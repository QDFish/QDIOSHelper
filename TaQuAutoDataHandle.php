<?php
/**
 * Created by PhpStorm.
 * User: zgzheng
 * Date: 2018/11/8
 * Time: 上午11:55
 */
require_once 'AutoPacketTool.php';

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
$cd_script_c ="cd /Users/guess/AP_TaQu";
$cd_git_c = "cd /Users/guess/TaQu";

switch ($group) {
    case "1":
        $base_path =  $live_path . DIRECTORY_SEPARATOR . $dir;
        $ipa_name = $target . "_live_${date}_" . $ext;
        break;
    case "2":
        $base_path = $forum_path . DIRECTORY_SEPARATOR . $dir;
        $ipa_name = $target . "_forum_${date}_" . $ext;
        break;
    case "3":
        $base_path = $mall_path . DIRECTORY_SEPARATOR . $dir;
        $ipa_name = $target . "_mall_${date}_" . $ext;
        break;
    case "4":
        $base_path = $total_path . DIRECTORY_SEPARATOR . $dir;
        $ipa_name = $target . "_${date}_" . $ext;
        break;
    case "5":
        $base_path = $test_path . DIRECTORY_SEPARATOR . $dir;
        $ipa_name = $target . "_${date}_" . $ext;
        break;
    default:
}

$cur_branch= "git symbolic-ref --short -q HEAD";

if ($cur_branch != $select_branch) {
    $git_checkout_c = "git checkout $select_branch";
    $git_checkout_shell = gd_shell_array([$cd_git_c, $git_checkout_c]);
   # echo $git_checkout_shell;
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

$unlock_c = "security -v unlock-keychain -p \"123456\" ~/Library/Keychains/login.keychain-db";
$xb_c = "./QDXbPHP.sh $select_branch $target $ipa_name $base_path";
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
