<?php
/**
 * Created by PhpStorm.
 * User: zgzheng
 * Date: 2018/11/20
 * Time: 下午3:49
 */

//$pro_path = "/Users/guess/TaQu";
//$cd_script_c ="cd /Users/guess/AP_TaQu";

define("HB_TaQu", "TaQu");
define("HB_PeiPei", "PeiPei");
define("HB_Test", "Test");

$version_key = "CFBundleShortVersionString";
$build_key = "CFBundleVersion";
$gray_key = "HBIsGrayLevel";

if ($target_type == HB_TaQu) {
//    $pro_path = "/Users/guess/TaQu";
//    $cd_script_c ="cd /Users/guess/AP_TaQu";
    $pro_path = "/Users/zgzheng/TouchiOS_new";
    $cd_script_c ="cd /Project/AutoPacket/AP_TaQu";
    $cd_git_c = "cd " . $pro_path;
    

    $test_target_key = "TaQuTest";
    $build_target_key = "TaQuBuild";
    $main_target_key = "TaQu";

    $plist_paths = [
        $test_target_key => $pro_path . "/TaQu/TaQuTest-Info.plist",
        $build_target_key => $pro_path . "/TaQu/TaQuBuild-Info.plist",
        $main_target_key => $pro_path . "/TaQu/Info.plist"
    ];

    $group_value = [
        '直播' => 'live',
        '社区' => 'forum',
        '商城' => 'mall',
        '融合包' => 'merge',
        '内测' => 'beta'
    ];

    $group_path = [
        'live' => '/iOS迭代安装包/直播',
        'forum' => '/iOS迭代安装包/社区',
        'mall' => '/iOS迭代安装包/商城',
        'merge' => '/iOS迭代安装包/融合包',
        'beta' => '/iOS迭代安装包/内测包'
    ];

} else if ($target_type == HB_PeiPei) {

    $pro_path = "/Users/guess/PeiPei";
    $cd_script_c ="cd /Users/guess/AP_PeiPei";
//    $pro_path = "/Users/zgzheng/TouchiOS_new";
//    $cd_script_c ="cd /Project/AutoPacket/AP_TaQu";
    $cd_git_c = "cd " . $pro_path;


    $test_target_key = "HBPeiPei-Test";
    $build_target_key = "HBPeiPei-Build";
    $main_target_key = "HBPeiPei";

    $plist_paths = [
        $test_target_key => $pro_path . "/HBPeiPei-CL-Info.plist",
        $build_target_key => $pro_path . "/HBPeiPei-Build-Info.plist",
        $main_target_key => $pro_path . "/HBPeiPei/Info.plist"
    ];

    $group_value = [
        '测试' => 'test',
        '灰度' => 'gray',
        '内测' => 'beta',
        '融合包' => 'merge',
    ];

    $group_path = [
        'test' => '/iOS迭代安装包/配配/测试',
        'gray' => '/iOS迭代安装包/配配/灰度',
        'beta' => '/iOS迭代安装包/配配/内测',
        'merge' => '/iOS迭代安装包/配配/融合包',
    ];

} else if ($target_type == HB_Test) {
//    $pro_path = "/Users/guess/MyTest";
//    $cd_script_c ="cd /Users/guess/AP_Test";
//    $pro_path = "/Users/zgzheng/TouchiOS_new";
//    $cd_script_c ="cd /Project/AutoPacket/AP_TaQu";
    $cd_git_c = "cd " . $pro_path;


    $test_target_key = "MyTest";
    $build_target_key = "MyTest";
    $main_target_key = "MyTest";

    $plist_paths = [
        $test_target_key => $pro_path . "/MyTest/info.plist",
        $build_target_key => $pro_path . "/MyTest/info.plist",
        $main_target_key => $pro_path . "/MyTest/info.plist"
    ];

    $group_value = [
        '直播' => 'live',
        '社区' => 'forum',
        '商城' => 'mall',
        '融合包' => 'merge',
        '内测' => 'beta'
    ];

    $group_path = [
        'live' => '/iOS迭代安装包/直播',
        'forum' => '/iOS迭代安装包/社区',
        'mall' => '/iOS迭代安装包/商城',
        'merge' => '/iOS迭代安装包/融合包',
        'beta' => '/iOS迭代安装包/内测包'
    ];

}



