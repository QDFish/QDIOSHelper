<?php
/**
 * Created by PhpStorm.
 * User: zgzheng
 * Date: 2018/11/20
 * Time: 下午8:23
 */

require_once 'AutoPacketConstant.php';
require 'vendor/autoload.php';

$test_plist_content = file_get_contents($plist_paths[$test_target_key]);
$build_plist_content = file_get_contents($plist_paths[$build_target_key]);
//$main_plist_content = file_get_contents($plist_paths[$main_target_key]);

$test_plist = new \CFPropertyList\CFPropertyList();
$test_plist->parse($test_plist_content);

$build_plist = new \CFPropertyList\CFPropertyList();
$build_plist->parse($build_plist_content);

//$main_plist = new \CFPropertyList\CFPropertyList();
//$main_plist->parse($main_plist_content);

$version_dic = [];
$build_dic = [];

$test_plist_arr = $test_plist->getValue(true);
$build_plist_arr = $build_plist->getValue(true);
//$main_plist_arr = $main_plist->getValue(true);

$version_dic[$test_target_key] = $test_plist_arr->get($version_key)->getValue();
$version_dic[$build_target_key] = $build_plist_arr->get($version_key)->getValue();

$build_dic[$test_target_key] = $test_plist_arr->get($build_key)->getValue();
$build_dic[$build_target_key] = $build_plist_arr->get($build_key)->getValue();

$gray_value = $build_plist_arr->get($gray_key);
if ($gray_value) {
    $is_gray = $gray_value->getValue();
}

