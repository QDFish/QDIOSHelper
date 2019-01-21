<?php
/**
 * Created by PhpStorm.
 * User: zgzheng
 * Date: 2019/1/16
 * Time: 8:13 PM
 */

require_once __DIR__ . "/vendor/autoload.php";
use Workerman\Worker;
use PHPSocketIO\SocketIO;

$io = new SocketIO(8810);
// 当有客户端连接时打印一行文字
$io->on('connection', function($socket)use($io){
    echo "new connection coming\n";
});

Worker::runAll();