<?php
/**
 * Created by PhpStorm.
 * User: zgzheng
 * Date: 2018/11/8
 * Time: 上午10:58
 */
require_once 'AutoPacketTool.php';

$mount_path= "/Volumes/packages";
$live_path = $mount_path . "/iOS迭代安装包/直播";
$forum_path = $mount_path . "/iOS迭代安装包/社区";
$mall_path = $mount_path . "/iOS迭代安装包/商城";
$total_path = $mount_path . "/iOS迭代安装包/融合包";
$test_path = $mount_path . "/iOS迭代安装包/内测包";

$date = date('Ymd');

$cur_branch = $_POST['cur_branch'];
$target = $_POST['target'];
$ext = $_POST['ext'];
$group = $_POST['group'];
$dir = $_POST['dir'];
$cd_script_c ="cd /Users/guess/AP_Test";
$cd_git_c = "cd /Users/guess/MyTest";

switch ($group) {
    case "1":
        $base_path =  $live_path . DIRECTORY_SEPARATOR . $dir;
        $ipa_name = $target . "live_${date}_" . $ext;
        break;
    case "2":
        $base_path = $forum_path . DIRECTORY_SEPARATOR . $dir;
        $ipa_name = $target . "forum_${date}_" . $ext;
        break;
    case "3":
        $base_path = $mall_path . DIRECTORY_SEPARATOR . $dir;
        $ipa_name = $target . "mall_${date}_" . $ext;
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

$git_checkout_c = "git checkout $cur_branch";
$git_shell = gd_shell_array([$cd_git_c, $git_checkout_c]);
exec($git_shell, $git_result, $git_status);
if ($git_status) {
    print_r($git_result);
    echo 'checkout failed';
    exit(1);
}

$xb_c = "./QDXbPHP.sh $cur_branch $target $ipa_name $base_path";
$unlock_c = "security -v unlock-keychain -p \"123456\" ~/Library/Keychains/login.keychain-db";
$xb_shell = gd_shell_array([$cd_script_c, $unlock_c, $xb_c]);
echo $xb_shell . PHP_EOL;
exec($xb_shell, $xb_result, $xb_status);

if ($xb_status) {
    $xb_result_str = implode("\n", $xb_result);
    echo $xb_result_str;
    echo 'xb failed';
    exit(1);
} else {
    echo end($xb_result);
}
