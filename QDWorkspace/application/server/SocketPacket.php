<?php
/**
 * Created by PhpStorm.
 * User: zgzheng
 * Date: 2019/1/16
 * Time: 4:11 PM
 */

require_once __DIR__ . "/../../../vendor/autoload.php";
use Workerman\Worker;
use PHPSocketIO\SocketIO;

$io = new SocketIO(3133);
// 当有客户端连接时打印一行文字
$io->on('workerStart', function()use($io) {
    echo 'work success';
    $inner_http_worker = new Worker('http://0.0.0.0:6166');
    $inner_http_worker->onMessage = function($http_connection, $data)use($io){
        echo 'recevie' . $_POST['event'] . $_POST['msg'] . PHP_EOL;
        if(!isset($_POST['event'])) {
            return $http_connection->send("fail, no event");
        }   
        
        $io->emit($_POST['event'], $_POST['msg']);
        $http_connection->send('ok');
    };
    $inner_http_worker->listen();
});

$io->on('connection', function($socket)use($io){
    
    $socket->on('chat message', function($msg)use($io){
        
//        $io->emit('chat message from server', $msg);
    });
});

Worker::runAll();
