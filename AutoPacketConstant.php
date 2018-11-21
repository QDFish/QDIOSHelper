<?php
/**
 * Created by PhpStorm.
 * User: zgzheng
 * Date: 2018/11/20
 * Time: 下午3:49
 */

$pro_path = "/Users/guess/TaQu";
$cd_script_c ="cd /Users/guess/AP_TaQu";

//$pro_path = "/Users/zgzheng/TouchiOS_new";
//$cd_script_c ="cd /Project/AutoPacket/AP_TaQu";
$cd_git_c = "cd " . $pro_path;

$version_key = "CFBundleShortVersionString";
$build_key = "CFBundleVersion";
$gray_key = "HBIsGrayLevel";

$test_target_key = "TaQuTest";
$build_target_key = "TaQuBuild";
$main_target_key = "TaQu";

$plist_paths = [
    $test_target_key => $pro_path . "/TaQu/TaQuTest-Info.plist",
    $build_target_key => $pro_path . "/TaQu/TaQuBuild-Info.plist",
    $main_target_key => $pro_path . "/TaQu/Info.plist"
];

