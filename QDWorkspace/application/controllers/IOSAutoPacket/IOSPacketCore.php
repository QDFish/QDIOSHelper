<?php
/**
 * Created by PhpStorm.
 * User: zgzheng
 * Date: 2019/1/17
 * Time: 3:08 PM
 */


require_once __DIR__ . "/../../../../vendor/autoload.php";
require_once __DIR__ . "/../../server/RedisKeyConstant.php";
use Workerman\Worker;
use PHPSocketIO\SocketIO;

define("HB_TaQu", "TaQu");
define("HB_PeiPei", "PeiPei");
define("HB_Test", "Test");
define("HB_TQLive", "TQLive");


class IOSPacketCore
{
    public static $base_path = 'IOSAutoPacket/';
    public static $version_key = "CFBundleShortVersionString";
    public static $build_key = "CFBundleVersion";
    public static $gray_key = "HBIsGrayLevel";
    public static $mount_path= "/Volumes/packages";
    public static $smb_path= "//guest:@10.10.5.249/packages";

    //init
    public $pro_path;
    public $save_path;
    public $workspace_name;
    public $test_target_key;
    public $build_target_key;
    public $main_target_key;
    public $plist_paths = [];
    public $group_value = [];
    public $group_path = [];
    public $test_plist;
    public $build_plist;
    public $version_dic;
    public $build_dic;
    public $test_plist_arr;
    public $build_plist_arr;
    public $gray_value;
    public $is_gray;

    //cur
    public $cur_branch;
    public $branch_list_result;

    public function __construct($project = '') {
        if ($project != '') {
            $this->init_config($project);
            $this->init_plist();
            $this->get_branches();
            $this->get_cur_branch();
        }
    }

    public function kill_task($task_id) {

        $need_packet = false;
        $this->lock_context(LOCK_EX, function () use($task_id, &$need_packet){
            $redis = $this->redis();
            $task = $this->get_task($redis, 0);
            if ($task['task_id'] == $task_id && $task['status'] == PacketTask_Status_Loading) {
                $this->stop_progress();
                $this->stop_task($task);
            }
        
            $redis->lRem(PacketTaskId_List, $task_id, 0);
            $redis->hDel(PacketTask_Hash, $task_id);
            $this->send_msg('del_task', $task_id);
            $need_packet = $this->check_task_list($redis);
            $redis->close();

            if ($task['task_id'] == $task_id && ($task['status'] == PacketTask_Status_Success || $task['status'] == PacketTask_Status_Failed)) {
                $task_json = json_encode($task);
                $this->send_msg('add_his', $task_json);
            }
        });

        $this->send_msg('end_loading', 'ok', true);

        if ($need_packet) {
            exec('php ' . __DIR__ . '/IOSPacketSh.php', $msg, $status);
        } else {
            $this->send_msg('message', 'not load');
        }
    }

    public function add_task() {

        $task_id = uniqid();
        $json = [];

        $this->lock_context(LOCK_SH, function () use($task_id, &$json) {

            $date = date('Ymd');
            $json['date'] = $date;
            $json['task_id'] = $task_id;
            $json['status'] = PacketTask_Status_Waiting;
            $json['reason'] = '';
            $json['pid'] = '';

            $project = $_POST['project'];
            $json['project'] = $project;
            $this->init_config($project);


            $this->get_cur_branch();

            $group = $_POST['group'];
            $dir = $_POST['dir'];
            $target = $_POST['target'];
            $ext = $_POST['ext'];
            $base_path = self::$mount_path . $this->group_path[$group] . DIRECTORY_SEPARATOR . $dir;
            $ipa_name = $target . "_${group}_$date" . $ext;
            $json['base_path'] = $base_path;
            $json['ipa_name'] = $ipa_name;
            $json['target'] = $target;
            $json['version'] = $_POST['version'];
            $json['build'] = $_POST['build'];
            $json['is_gray'] = $_POST['is_gray'];
            $json['select_branch'] = $_POST['select_branch'];
        });

        $need_packet = false;
        $this->lock_context(LOCK_EX, function () use($json, $task_id, &$need_packet) {
            $json_str = json_encode($json);
            $redis = $this->redis();
            $redis->rPush(PacketTaskId_List, $task_id);
            $redis->hSetNx(PacketTask_Hash, $task_id, $json_str);
            $need_packet = $this->check_task_list($redis);

            if ($redis->lLen(PacketTaskId_List) == 1) {
                $task =  $this->get_task($redis, 0);
                $json_str = json_encode($task);
                
            }
            $this->send_msg('add_task', $json_str);
            $redis->close();
        });

        $this->send_msg('end_loading', 'ok', true);

        if ($need_packet) {
            exec('php ' . __DIR__ . '/IOSPacketSh.php', $msg, $status);
        } else {
            $this->send_msg('message', 'not load');
        }
    }

    public function send_msg($event, $msg, $remote = false) {
        $data = ['event' => $event, 'msg' => $msg];
        if ($remote) {
            $data['remote'] = $_SERVER['REMOTE_ADDR'];
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://localhost");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_PORT, 6166);
        curl_exec($ch);
        curl_close($ch);
    }

    public function redis() {
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
        return $redis;
    }

    public function get_task($redis, $index) {
        $task_id = $redis->lGet(PacketTaskId_List, $index);
        $task_json = $redis->hGet(PacketTask_Hash, $task_id);
        $task = json_decode($task_json, true);
        return $task;
    }

    public function get_history($redis, $index) {
        $task_id = $redis->lGet(PacketHistoryId_List, $index);
        $task_json = $redis->hGet(PacketHistory_Hash, $task_id);
        $task = json_decode($task_json, true);
        return $task;
    }

    public function save_task($redis, $task) {
        $task_id = $task['task_id'];
        if ($task_id != '') {
            $task_json = json_encode($task);
            $redis->hSet(PacketTask_Hash, $task_id, $task_json);
        }
    }

    public function get_all_task($redis) {
        $tasks = [];
        $this->lock_context(LOCK_SH, function () use($redis, &$tasks){
            $num = $redis->lLen(PacketTaskId_List);

            for ($i = 0; $i < $num; $i++) {
                $task = $this->get_task($redis, $i);
                $tasks[] = $task;
            }
        });

        return $tasks;
    }

    public function get_all_history($redis) {
        $tasks = [];
        $this->lock_context(LOCK_SH, function () use($redis, &$tasks){
            $num = $redis->lLen(PacketHistoryId_List);
            $num = $num > 10 ? 10 : $num;

            for ($i = 0; $i < $num; $i++) {
                $task = $this->get_history($redis, $i);
                $tasks[] = $task;
            }
        });

        return $tasks;
    }

    private function check_task_list($redis) {
        $task = $this->get_task($redis, 0);
        if ($task['status'] == PacketTask_Status_Waiting) {
            $task['status'] = PacketTask_Status_ready;
            $this->save_task($redis, $task);
            $task_json = json_encode($task);
            $this->send_msg('update_task', $task_json);
            return true;
        }

        return false;
    }

    public function progress($second) {
        $unit = 2;
        $total = 80;
        $count = (int)($second / $unit);
        $step  = ($total / $count);
        
        for ($i = 0; $i < $count; $i++) {
            $progress = ($i + 1) * $step;
            $this->send_msg('progress', "$progress");
            sleep($unit);
        }

        while (1) {
            $this->send_msg('progress', "80");
            sleep($unit);
        }
    }

    public function stop_progress() {
        $redis = $this->redis();
        $pid = $redis->get(PacketProgress_Pid);
        if ($pid) {
            exec('kill ' . $pid);
            $redis->set(PacketProgress_Pid, '');
        }
    }
    
    public function stop_task($task) {
        $pid = $task['pid'];
        if ($pid) {
            $this->send_msg('stop task', $pid);
            exec('kill ' . $pid);
        }
    }

    public function finish($success) {
        $redis = $this->redis();
        $task = $this->get_task($redis, 0);
        if ($success) {
            $task['status'] = PacketTask_Status_Success;
            $this->dingding($task);
            $this->send_msg('progress', '100');
        } else {
            $task['status'] = PacketTask_Status_Failed;    
        }
        
        sleep(1);
        
        $task_json = json_encode($task);
        
        $task_id = $task['task_id'];
        $redis->lPush(PacketHistoryId_List, $task_id);
        $redis->hSetNx(PacketHistory_Hash, $task_id, $task_json);
        $redis->hSet(PacketTask_Hash, $task_id, $task_json);
        $this->kill_task($task_id);
        $redis->close();
    }

    public function dingding($task) {
        $webhook = "https://oapi.dingtalk.com/robot/send?access_token=2847708772bf841db880f708330ec5875078162f0c55a5333edd55c96672568c";

        $bath_arr = explode(DIRECTORY_SEPARATOR, $task['base_path']);
        unset($bath_arr[0]);
        unset($bath_arr[1]);
        array_unshift($bath_arr, "smb://10.10.5.249");
        $base_path = implode(DIRECTORY_SEPARATOR, $bath_arr);

        $message_arr = [
            "###### 当前分支:" . $task['select_branch'],
            "###### 文件名:" . $task['ipa_name'],
            "###### 文件路径:" . $base_path,
            "###### version:" . $task['version'],
            "###### build:" . $task['build'],
        ];


        $message = implode("\n", $message_arr);
        $data = array ('msgtype' => 'actionCard','actionCard' => array ('text' => $message,'hideAvatar' => '0', 'btnOrientation' => '0', 'btns' => array(array('title' => '跳转到包目录', 'actionURL' => $base_path ))));
        $data_string = json_encode($data);
        $this->request_by_curl($webhook, $data_string);
    }

    public function packet() {
//        $this->send_msg('begin_task', 'begin');
//        $redis = $this->redis();
//        $task = $this->get_task($redis, 0);
//        $task['status'] = PacketTask_Status_Loading;
//        $task['pid'] = getmypid();
//        $this->save_task($redis, $task);
//        $task_json = json_encode($task);
//        $this->send_msg('update_task', $task_json);
//        $redis->close();
//
//        sleep(10);
//        return 'success';
        $this->send_msg('begin_task', 'begin');
        $redis = $this->redis();
        $task = $this->get_task($redis, 0);
        try {
            $task['status'] = PacketTask_Status_Loading;
            $task['pid'] = getmypid();
            $this->save_task($redis, $task);
            $task_json = json_encode($task);
            $this->send_msg('update_task', $task_json);

            $project = $task['project'];
            $this->init_config($project);

            $this->init_plist();



            if (!$this->get_cur_branch(false)) {
                $task['reason'] = 'get current branch faild';
                $this->save_task($redis, $task);
                $redis->close();
                return 'failed';
            }



//        $date = $task['date'];
            $select_branch = $task['select_branch'];
            $target = $task['target'];

//        $ext = $task['ext'];
//        $group = $task['group'];
//        $dir = $task['dir'];
            $version = $task['version'];
            $build = $task['build'];
            $gray = $task['is_gray'];

            $base_path = $task['base_path'];

//            = self::$mount_path . $this->group_path[$group] . DIRECTORY_SEPARATOR . $dir;
            $ipa_name = $task['ipa_name'];
//             $target . "_${group}_$date" . $ext;

            if (!$this->reset_hard(false)) {
                $task['reason'] = 'get current branch faild';
                $this->save_task($redis, $task);
                $redis->close();
                return 'failed';
            };




            if ($this->cur_branch != $select_branch) {
                if (!$this->checkout_branch($select_branch, false)) {
                    $task['reason'] = 'get current branch faild';
                    $this->save_task($redis, $task);
                    $redis->close();
                    return 'failed';
                }
            }


            if (!$this->pull_branch($select_branch, false)) {
                $task['reason'] = 'get current branch faild';
                $this->save_task($redis, $task);
                $redis->close();
                return 'failed';
            }

            $this->save_plist($target, $version, $build, $gray);

            $result = $this->archive_ipa($target, $ipa_name, $base_path, $task);
            $this->save_task($redis, $task);
            $redis->close();
        } catch (Exception $e) {
            $task['reason'] = 'exception ' . $e->getMessage();
            $this->save_task($redis, $task);
            $redis->close();
            return 'failed';
        }

        return $result;
    }


    private function lock_context($operation, $block) {
        $real_path = realpath(__DIR__ . '/../../..');;
        $fp = fopen($real_path . '/lock/packet_task_lock.txt', "a+");
        if (flock($fp, $operation)) {
            $block();
            flock($fp, LOCK_UN);
        }

        fclose($fp);
    }

    private function archive_ipa($target, $ipa_name, $base_path, &$task) {
        if (strlen($target) == 0) {
            $task['reason'] = 'target name can\'t be empty';
            return 'failed';
        }

        if (strlen($ipa_name) == 0) {
            $task['reason'] = 'ipa name can\'t be empty"';
            return 'failed';
        }

        $archive_path = $this->save_path . DIRECTORY_SEPARATOR . 'archive';
        $ipa_path = $this->save_path . DIRECTORY_SEPARATOR . 'ipa';

        if (!is_writeable($archive_path)) {
            $task['reason'] = 'archive path is not writable';
            return 'failed';
        }

        if (!is_writeable($ipa_path)) {
            $task['reason'] = 'ipa path is not writable';
            return 'failed';
        }

        if (file_exists($archive_filename = ($archive_path . DIRECTORY_SEPARATOR . $ipa_name))) {
            unlink($archive_filename);
        }

        if (file_exists($ipa_filename = ($ipa_path . DIRECTORY_SEPARATOR . $ipa_name))) {
            $this->delete_file_path($ipa_filename, false);
        }

        $archive_shell =
            "xcodebuild archive \
            -workspace $this->pro_path/$this->workspace_name.xcworkspace \
            -scheme $target \
            -configuration Release \
            -archivePath $archive_filename";

        exec($archive_shell, $archive_result, $archive_status);
        if ($archive_status) {
            $log_count = 2;
            $log_count = $log_count > count($archive_result) ? count($archive_result) : $log_count;
            
            $archive_result = array_splice($archive_result, count($archive_result) - $log_count, $log_count);
            $archive_result_str = implode("\n", $archive_result);

            $task['reason'] = 'archive failed' . $archive_result_str;
            return 'failed';
        }

//        $this->send_msg('message', 'archive' . $archive_shell);



        $ipa_shell =
            "xcodebuild \
            -exportArchive \
            -archivePath $archive_filename.xcarchive \
            -exportPath $ipa_filename \
            -exportOptionsPlist $this->save_path/exprotOptionsPlist.plist \
            -allowProvisioningUpdates";


//        $this->send_msg('message', 'ipa' . $ipa_shell);

        exec($ipa_shell, $ipa_result, $ipa_status);
        if ($ipa_status) {
            $log_count = 2;
            $log_count = $log_count > count($archive_result) ? count($archive_result) : $log_count;

            $ipa_result = array_splice($ipa_result, count($ipa_result) - $log_count, $log_count);
            $ipa_result_str = implode(" ", $ipa_result);

            $task['reason'] = 'ipa failed' . $ipa_result_str;
            return 'failed';
        }


        exec("rm -rf $archive_path/*", $rm_result, $rm_status);

        $mount_path = self::$mount_path;

        if (!file_exists($mount_path)) {
            exec("sudo mkdir $mount_path", $mount_result, $mount_status);
            if ($mount_status) {

                $task['reason'] = "mkdir $mount_path failed, maybe you should set sudo no password";
                return 'failed';
            }
        }


        $smb_path = self::$smb_path;
        exec("sudo mount_smbfs $smb_path $mount_path", $smb_result, $smb_status);
//        if ($smb_status) {
//            echo 'connect smb failed';
//            exit(1);
//        }

//        $this->send_msg('message', 'mount_smbfs' . "sudo mount_smbfs $smb_path $mount_path");

        if (!file_exists($base_path)) {
            exec("sudo mkdir $base_path", $mount_result, $mount_status);
            if ($mount_status) {
                $task['reason'] = "mkdir $base_path failed failed, maybe you should set sudo no password";
                return 'failed';
            }
        }

        exec("mv $ipa_filename/$target.ipa $base_path/$ipa_name.ipa", $mv_result, $mv_status);
        if ($mv_status) {
            $task['reason'] = "mv $ipa_filename.ipa to $base_path failed";
            return 'failed';
        }

        exec("rm -rf $ipa_path/*", $rm_result, $rm_status);

        return 'success';
    }

    private function save_plist($target, $version, $build, $gray) {

        if ($target == $this->test_target_key) {
            $verson_value = $this->test_plist_arr->get(self::$version_key);
            $build_value = $this->test_plist_arr->get(self::$build_key);
            $verson_value->setValue($version);
            $build_value->setValue($build);
            $this->test_plist->save($this->plist_paths[$this->test_target_key], \CFPropertyList\CFPropertyList::FORMAT_XML);
        } else if ($target == $this->build_target_key) {
            $verson_value = $this->build_plist_arr->get(self::$version_key);
            $build_value = $this->build_plist_arr->get(self::$build_key);
            $verson_value->setValue($version);
            $build_value->setValue($build);
        }

        if ($this->gray_value != null) {
            $this->gray_value->setValue($gray);
        }

        $this->build_plist->save($this->plist_paths[$this->build_target_key], \CFPropertyList\CFPropertyList::FORMAT_XML);
    }

    private function get_branches()
    {
        $fetch_c = "git fetch origin";
        $branch_list_c = "git branch -r";
        $cd_git_c = "cd " . $this->pro_path;
        $branch_list_shell = $this->gd_shell_array([$cd_git_c, $fetch_c, $branch_list_c]);
        exec($branch_list_shell, $this->branch_list_result, $branch_list_status);
        if ($branch_list_status) {
            echo "获取分支列表失败";
            exit(1);
        } else {
            foreach ($this->branch_list_result as &$branch) {
                $branch_arr = explode(DIRECTORY_SEPARATOR, $branch);
                $branch = end($branch_arr);
            }
        }
    }

    private function reset_hard($exit = true) {
        $cd_git_c = "cd " . $this->pro_path;
        $git_reset_c = "git reset --hard";
        $git_reset_shell = $this->gd_shell_array([$cd_git_c, $git_reset_c]);
        exec($git_reset_shell, $git_reset_result, $git_reset_status);
        if ($git_reset_status) {
            $result = implode("\n", $git_reset_result);
            echo 'git reset failed reason:' . PHP_EOL . $result;
            if ($exit) {
                exit(1);
            } else {
                return false;
            }
        }

        return true;
    }

    private function checkout_branch($select_branch, $exit = true) {
        $cd_git_c = "cd " . $this->pro_path;
        $git_checkout_c = "git checkout $select_branch";
        $git_checkout_shell = $this->gd_shell_array([$cd_git_c, $git_checkout_c]);
        exec($git_checkout_shell, $git_checkout_result, $git_checkout_status);
        if ($git_checkout_status) {
            $result = implode("\n", $git_checkout_result);
            echo 'checkout failed reason:' . PHP_EOL . $result;
            if ($exit) {
                exit(1);
            } else {
                return false;
            }
        }

        return true;
    }

    private function pull_branch($select_branch, $exit = true) {
        $cd_git_c = "cd " . $this->pro_path;
        $git_pull_c = "git pull origin $select_branch";
        $git_pull_shell = $this->gd_shell_array([$cd_git_c, $git_pull_c]);
        exec($git_pull_shell, $git_pull_result, $git_pull_status);
        if ($git_pull_status) {
            $result = implode("\n", $git_pull_result);
            echo $result;
            if ($exit) {
                exit(1);
            } else {
                return false;
            }
        }
        return true;
    }


    private function get_cur_branch($exit = true) {
        $cd_git_c = "cd " . $this->pro_path;
        $cur_branch_c = "git symbolic-ref --short -q HEAD";
        $cur_branch_shell = $this->gd_shell_array([$cd_git_c, $cur_branch_c]);
        exec($cur_branch_shell, $cur_branch_result, $cur_branch_status);
        if ($cur_branch_status) {

            echo "get current branch failed";
            if ($exit) {
                exit(1);
            }
            return false;
        }

        $this->cur_branch = end($cur_branch_result);
        return $this->cur_branch;
    }



    private function init_plist()
    {
        $test_plist_content = file_get_contents($this->plist_paths[$this->test_target_key]);
        $build_plist_content = file_get_contents($this->plist_paths[$this->build_target_key]);

        $this->test_plist = new \CFPropertyList\CFPropertyList();
        $this->test_plist->parse($test_plist_content);

        $this->build_plist = new \CFPropertyList\CFPropertyList();
        $this->build_plist->parse($build_plist_content);

        $this->version_dic = [];
        $this->build_dic = [];

        $this->test_plist_arr = $this->test_plist->getValue(true);
        $this->build_plist_arr = $this->build_plist->getValue(true);


        $this->version_dic[$this->test_target_key] = $this->test_plist_arr->get(self::$version_key)->getValue();
        $this->version_dic[$this->build_target_key] = $this->build_plist_arr->get(self::$version_key)->getValue();

        $this->build_dic[$this->test_target_key] = $this->test_plist_arr->get(self::$build_key)->getValue();
        $this->build_dic[$this->build_target_key] = $this->build_plist_arr->get(self::$build_key)->getValue();

        $this->gray_value = $this->build_plist_arr->get(self::$gray_key);
        if ($this->gray_value) {
            $this->is_gray = $this->gray_value->getValue();
        }
    }

    private function init_config($project)
    {
        if ($project == HB_TaQu) {
            $this->pro_path = "/Users/guess/TaQu";
//            $this->pro_path = "/Users/zgzheng/TouchiOS_new";
            $this->save_path = "/Users/guess/AP_TaQu";
            $this->workspace_name = 'TaQu';
            $this->test_target_key = "TaQuTest";
            $this->build_target_key = "TaQuBuild";
            $this->main_target_key = "TaQu";

            $this->plist_paths = [
                $this->test_target_key => $this->pro_path . "/TaQu/TaQuTest-Info.plist",
                $this->build_target_key => $this->pro_path . "/TaQu/TaQuBuild-Info.plist",
                $this->main_target_key => $this->pro_path . "/TaQu/Info.plist"
            ];

            $this->group_value = [
                '直播' => 'live',
                '社区' => 'forum',
                '商城' => 'mall',
                '融合包' => 'merge',
                '内测' => 'beta'
            ];

            $this->group_path = [
                'live' => '/iOS迭代安装包/直播',
                'forum' => '/iOS迭代安装包/社区',
                'mall' => '/iOS迭代安装包/商城',
                'merge' => '/iOS迭代安装包/融合包',
                'beta' => '/iOS迭代安装包/内测包'
            ];

        } else if ($project == HB_PeiPei) {

            $this->pro_path = "/Users/guess/PeiPei";
            $this->save_path = "/Users/guess/AP_PeiPei";
            $this->workspace_name = 'HBPeiPei';
//            $this->pro_path = "/Users/zgzheng/peipei";
//            $this->save_path = "/Project/AutoPacket/AP_PeiPei";

            $this->test_target_key = "HBPeiPei-Test";
            $this->build_target_key = "HBPeiPei-Build";
            $this->main_target_key = "HBPeiPei";

            $this->plist_paths = [
                $this->test_target_key => $this->pro_path . "/HBPeiPei-CL-Info.plist",
                $this->build_target_key => $this->pro_path . "/HBPeiPei-Build-Info.plist",
                $this->main_target_key => $this->pro_path . "/HBPeiPei/Info.plist"
            ];

            $this->group_value = [
                '测试' => 'test',
                '灰度' => 'gray',
                '内测' => 'beta',
                '融合包' => 'merge',
            ];

            $this->group_path = [
                'test' => '/iOS迭代安装包/配配/测试',
                'gray' => '/iOS迭代安装包/配配/灰度',
                'beta' => '/iOS迭代安装包/配配/内测',
                'merge' => '/iOS迭代安装包/配配/融合包',
            ];

        } else if ($project == HB_TQLive) {

            $this->pro_path = "/Users/guess/TQLive";
            $this->save_path = "/Users/guess/AP_TQLive";
            $this->workspace_name = 'TQLive';
//            $this->pro_path = "/Users/zgzheng/peipei";
//            $this->save_path = "/Project/AutoPacket/AP_PeiPei";

            $this->test_target_key = "TQLive";
            $this->build_target_key = "TQLive_Test";
            $this->main_target_key = "TQLive";

            $this->plist_paths = [
                $this->test_target_key => $this->pro_path . "/TQLive/Info.plist",
                $this->build_target_key => $this->pro_path . "/TQLive/Info_Test.plist",
                $this->main_target_key => $this->pro_path . "/TQLive/Info.plist"
            ];

            $this->group_value = [
                '哆闪直播' => 'tqlive',
                '直播' => 'live',
                '社区' => 'forum',
                '商城' => 'mall',
                '融合包' => 'merge',
                '内测' => 'beta'
            ];

            $this->group_path = [
                'tqlive' => '/iOS迭代安装包/哆闪直播',
                'live' => '/iOS迭代安装包/直播',
                'forum' => '/iOS迭代安装包/社区',
                'mall' => '/iOS迭代安装包/商城',
                'merge' => '/iOS迭代安装包/融合包',
                'beta' => '/iOS迭代安装包/内测包'
            ];

        } else if ($project == HB_Test) {
//            $this->send_msg('message', 'hhhh');
            $this->pro_path = "/Users/guess/MyTest";
            $this->save_path = "/Users/guess/AP_Test";
            $this->workspace_name = 'MyTest';
            $this->test_target_key = "MyTest";
            $this->build_target_key = "MyTest_build";
            $this->main_target_key = "d";

            $this->plist_paths = [
                $this->test_target_key => $this->pro_path . "/MyTest/Info.plist",
                $this->build_target_key => $this->pro_path . "/MyTest/Info.plist",
                $this->main_target_key => $this->pro_path . "/MyTest/Info.plist"
            ];

            $this->group_value = [
                '直播' => 'live',
                '社区' => 'forum',
                '商城' => 'mall',
                '融合包' => 'merge',
                '内测' => 'beta'
            ];

            $this->group_path = [
                'live' => '/iOS迭代安装包/直播',
                'forum' => '/iOS迭代安装包/社区',
                'mall' => '/iOS迭代安装包/商城',
                'merge' => '/iOS迭代安装包/融合包',
                'beta' => '/iOS迭代安装包/内测包'
            ];
        }
    }

    private function delete_file_path($path, $del_dir)
    {
        $delete_paths = [];
        if (substr($path, strlen($path) - 1, 1) != DIRECTORY_SEPARATOR) {
            $path .= DIRECTORY_SEPARATOR;
        }
        $files = scandir($path);
        if ($files != -1) {
            foreach ($files as $key => $file_name) {
                if ($file_name == '.' || $file_name == '..') {
                    continue;
                }

                $file_name = $path . $file_name;
                if (is_file($file_name)) {
                    unlink($file_name);
                } else if (is_dir($file_name)) {
                    $this->delete_file_path($file_name, true);
                }
            }

            if ($del_dir) {
                rmdir($path);
            }
        }

    }


    private function gd_shell_array(array $shells) {
        return implode($shells, ';');
    }

    function request_by_curl($remote_server, $post_string) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remote_server);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array ('Content-Type: application/json;charset=utf-8'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }
}






