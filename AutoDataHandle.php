<?php
/**
 * Created by PhpStorm.
 * User: zgzheng
 * Date: 2018/11/8
 * Time: 上午11:55
 */

$target_type = $_POST['target_type'];

require_once 'AutoPacketTool.php';
require_once 'AutoPacketConstant.php';

if (file_exists("/tmp/lock.file")) {
    echo "有进程正在打包中,请稍候再试";
    exit(1);
}

$mount_path= "/Volumes/packages";
$live_path = $mount_path . "/iOS迭代安装包/直播";
$forum_path = $mount_path . "/iOS迭代安装包/社区";
$mall_path = $mount_path . "/iOS迭代安装包/商城";
$total_path = $mount_path . "/iOS迭代安装包/融合包";
$test_path = $mount_path . "/iOS迭代安装包/内测包";

$date = date('Ymd');

$select_branch = $_POST['select_branch'];
$target = $_POST['target'];
$ext = $_POST['ext'];
$group = $_POST['group'];
$dir = $_POST['dir'];

$version = $_POST['version'];
$build = $_POST['build'];
$gray = $_POST['is_gray'];


$base_path = $mount_path . $group_path[$group] . DIRECTORY_SEPARATOR . $dir;
$ipa_name = $target . "_${group}_$date" . $ext;

//switch ($group) {
//    case "1":
//        $base_path =  $live_path . DIRECTORY_SEPARATOR . $dir;
//        $ipa_name = $target . "_live_${date}_" . $ext;
//        break;
//    case "2":
//        $base_path = $forum_path . DIRECTORY_SEPARATOR . $dir;
//        $ipa_name = $target . "_forum_${date}_" . $ext;
//        break;
//    case "3":
//        $base_path = $mall_path . DIRECTORY_SEPARATOR . $dir;
//        $ipa_name = $target . "_mall_${date}_" . $ext;
//        break;
//    case "4":
//        $base_path = $total_path . DIRECTORY_SEPARATOR . $dir;
//        $ipa_name = $target . "_${date}_" . $ext;
//        break;
//    case "5":
//        $base_path = $test_path . DIRECTORY_SEPARATOR . $dir;
//        $ipa_name = $target . "_${date}_" . $ext;
//        break;
//    default:
//}


$git_reset_c = "git reset --hard";
$git_reset_shell = gd_shell_array([$cd_git_c, $git_reset_c]);
exec($git_reset_shell, $git_reset_result, $git_reset_status);
if ($git_reset_status) {
    print_r($git_reset_result);
    echo 'git reset failed';
    exit(1);
}

$cur_branch_c = "git symbolic-ref --short -q HEAD";
$cur_branch_shell = gd_shell_array([$cd_git_c, $cur_branch_c]);
exec($cur_branch_shell, $git_curbranch_result, $git_curbranch_status);
if ($git_curbranch_status) {
    print_r($git_curbranch_result);
    echo 'get current branch failed';
    exit(1);
}

$cur_branch = array_pop($git_curbranch_result);

if ($cur_branch != $select_branch) {
    $git_checkout_c = "git checkout $select_branch";
    $git_checkout_shell = gd_shell_array([$cd_git_c, $git_checkout_c]);
//    echo $git_checkout_shell;
    exec($git_checkout_shell, $git_checkout_result, $git_checkout_status);
    if ($git_checkout_status) {
        print_r($git_checkout_result);
        echo 'checkout failed';
        exit(1);
    }
}

$git_pull_c = "git pull origin $select_branch";
$git_pull_shell = gd_shell_array([$cd_git_c, $git_pull_c]);
exec($git_pull_shell, $git_pull_result, $git_pull_status);
if ($git_pull_status) {
    print_r($git_pull_result);
    echo 'pull failed';
    exit(1);
}

require_once 'iOSPlistInit.php';


if ($target == $test_target_key) {
    $verson_value = $test_plist_arr->get($version_key);
    $build_value = $test_plist_arr->get($build_key);
    $verson_value->setValue($version);
    $build_value->setValue($build);
    $test_plist->save($plist_paths[$test_target_key], \CFPropertyList\CFPropertyList::FORMAT_XML);
} else if ($target == $build_target_key) {
    $verson_value = $build_plist_arr->get($version_key);
    $build_value = $build_plist_arr->get($build_key);
    $verson_value->setValue($version);
    $build_value->setValue($build);
    
}


if ($gray_value != null) {
    $gray_value->setValue($gray);
    $build_plist->save($plist_paths[$build_target_key], \CFPropertyList\CFPropertyList::FORMAT_XML);
}


//$git_add_c = "git add .";
//$git_commit_c = "git commit -m 'refresh plist'";
//$git_push_c = "git push origin " . $cur_branch;
//$git_push_shell = gd_shell_array([$cd_git_c, "git config --global user.name", $git_add_c, $git_commit_c, $git_push_c]);
//echo $git_push_shell;
//exec($git_push_shell, $git_push_result, $git_push_status);
//if ($git_push_status) {
//    print_r($git_push_result);
//    echo 'push failed';
//    exit(1);
//}

//echo 'test-----';


//exit(1);

$unlock_c = "security -v unlock-keychain -p \"123456\" ~/Library/Keychains/login.keychain-db";
$xb_c = "./QDXbPHP.sh $select_branch $target $ipa_name $base_path";
//$xb_c = "./QDLongTask.sh";
$xb_shell = gd_shell_array([$cd_script_c, $unlock_c, $xb_c]);
#echo $xb_shell . PHP_EOL;
exec($xb_shell, $xb_result, $xb_status);

if ($xb_status) {
    print_r($xb_result);
    echo 'xb failed';
    exit(1);
} else {
    echo end($xb_result);
}
