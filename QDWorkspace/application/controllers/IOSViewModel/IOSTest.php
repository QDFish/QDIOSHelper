<?php
/**
 * Created by PhpStorm.
 * User: zgzheng
 * Date: 2018/12/27
 * Time: 8:59 PM
 */

$archive_result =['fff', 'sdafasdf', 'asdfasdfasdfasdf'];
$archive_result = array_splice($archive_result, count($archive_result) - 1, 1);
$archive_result_str = implode("\n", $archive_result);
echo $archive_result_str;
