<?php
/**
 * Created by PhpStorm.
 * User: zgzheng
 * Date: 2019/1/17
 * Time: 4:42 PM
 */

require_once __DIR__ . '/IOSPacketCore.php';

$core = new IOSPacketCore();

$child = [];
$status = 'exit';
for ($i = 0; $i < 2; $i++) {
    if ($i == 0) {
        $packet_pid = pcntl_fork();

        if ($packet_pid == -1) {
            echo 'packet pcntl error';
            exit(1);
        } else if ($packet_pid > 0) {
            $child[] = $packet_pid;
        } else {
            $status = $core->packet();
            $core->stop_progress();
            if ($status == 'success') {
                $core->finish(true);
            } else if ($status == 'failed'){
                $core->stop_progress();
                $core->finish(false);
            } else {
                $core->send_msg('message', 'been killed');
            }
            exit();
        }
    } else {
        $progress_pid = pcntl_fork();

        if ($progress_pid == -1) {
            echo 'progress pcntl error';
            exit(1);
        } else if ($progress_pid > 0) {
            $redis = $core->redis();
            $redis->set(PacketProgress_Pid, $progress_pid);
            $child[] = $progress_pid;
        } else {
            $core->progress(600);
            $core->stop_progress();
            exit();
        }
    }
}

while(count($child) > 0) {
    foreach($child as $key => $pid) {
        $res = pcntl_waitpid($pid, $status, WNOHANG);

        // If the process has already exited
        if($res == -1 || $res > 0)
            unset($child[$key]);
    }

    sleep(1);
}

$core->send_msg('message', 'be done');




