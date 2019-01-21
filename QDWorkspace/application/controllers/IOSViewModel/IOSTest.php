<?php
/**
 * Created by PhpStorm.
 * User: zgzheng
 * Date: 2018/12/27
 * Time: 8:59 PM
 */


$path = '/Volumes/packages/iOS迭代安装包/直播/';
$path_arr = explode(DIRECTORY_SEPARATOR, $path);
unset($path_arr[0]);
array_unshift($path_arr, 'smb://10.d');
$path = implode(DIRECTORY_SEPARATOR, $path_arr);
echo $path;
