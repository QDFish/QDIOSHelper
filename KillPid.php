<?php
/**
 * Created by PhpStorm.
 * User: zgzheng
 * Date: 2018/11/22
 * Time: 下午4:41
 */

require_once "AutoPacketTool.php";

$pid = $_GET['pid'];

$kill_pid_shell = "kill pid $pid";
//$kill_pid_shell = "who is me";
//echo $kill_pid_shell;
exec($kill_pid_shell, $kill_pid_result, $kill_pid_status);
if($kill_pid_status){
    echo "kill failed reason:" . var_dump($kill_pid_result);
}

if (!$kill_pid_status) {
    $rm_shell = "rm -rf /tmp/lock.file";
    exec($rm_shell, $rm_result, $rm_status);
    if($rm_status){
        echo "rm failed" . var_dump($rm_result);
    } else {
//        header('Location:AutoPacket.php?target_type=TaQu');
    }
}


