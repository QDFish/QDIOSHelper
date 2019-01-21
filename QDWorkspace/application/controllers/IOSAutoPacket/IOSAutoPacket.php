<?php

/**
 * Created by PhpStorm.
 * User: zgzheng
 * Date: 2019/1/13
 * Time: 6:41 PM
 */

require_once __DIR__ . "/IOSPacketCore.php";

use Workerman\Worker;
use PHPSocketIO\SocketIO;



class IOSAutoPacket extends CI_Controller
{
    private $core;

    public function __construct() {
        parent::__construct();
//        $this->load->model('taskQueue');
    }


    public function index($project)
    {
        $this->core = new IOSPacketCore($project);
        $this->load->helper('url');
        $this->load->view('NormalHtmlBegin', ["title" => "IOS Auto Packet"]);
        $this->load->view(IOSPacketCore::$base_path . 'IOSAutoPacketHeader', ['project' => $project]);
        $this->load->view(IOSPacketCore::$base_path . 'IOSAutoPacketBody', [
            "cur_branch" => $this->core->cur_branch,
            "branch_list_result" => $this->core->branch_list_result,
            "test_target_key" => $this->core->test_target_key,
            "build_target_key" => $this->core->build_target_key,
            "version_dic" => $this->core->version_dic,
            "build_dic" => $this->core->build_dic,
            "group_value" => $this->core->group_value
        ]);
        $this->load->view('NormalHtmlEnd');
    }
    
    public function get_config($project) {
        $this->core = new IOSPacketCore($project);

        $json_para = [
            "test_target_key" => $this->core->test_target_key,
            "build_target_key" => $this->core->build_target_key,
            "version_dic" => $this->core->version_dic,
            "build_dic" => $this->core->build_dic,
        ];
        $json_para = json_encode($json_para);
        echo $json_para;
    }
    
    public function tasks() {
        ob_start();
        $this->core = new IOSPacketCore();
        $redis = $this->core->redis();
        $tasks = $this->core->get_all_task($redis);
        $tasks_json = json_encode($tasks);
        $redis->close();
        ob_clean();
        echo $tasks_json;
    }
    
    public function histories() {
        ob_start();
        $this->core = new IOSPacketCore();
        $redis = $this->core->redis();
        $tasks = $this->core->get_all_history($redis);
        $tasks_json = json_encode($tasks);
        $redis->close();
        ob_clean();
        echo $tasks_json;

    }
    
    public function kill_task($task_id) {
        $this->core = new IOSPacketCore();
        $this->core->kill_task($task_id);
    }

    public function add_task() {
        $this->core = new IOSPacketCore();
        $this->core->add_task();
    }

    public function help() {
        $help = <<<HELP
        <h2>Tips</h2>
0、增加了队列功能,打包完成后会出现在历史记录中,点击队列跟历史记录的视图可以查看相关信息

1、灰度仍然是无效的

2、队列中的任务都可以x掉,包括运行中的




HELP;
        echo $help;
    }

}




