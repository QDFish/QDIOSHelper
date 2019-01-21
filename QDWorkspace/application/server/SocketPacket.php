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
$io->totalsockets = [];
// 当有客户端连接时打印一行文字
$io->on('workerStart', function()use($io) {
    echo 'work success';
    $inner_http_worker = new Worker('http://0.0.0.0:6166');
    $inner_http_worker->onMessage = function($http_connection, $data)use($io){
        echo 'recevie' . $_POST['event'] . $_POST['msg'] . PHP_EOL;
        if(!isset($_POST['event'])) {
            return $http_connection->send("fail, no event");
        }
        
        if (isset($_POST['remote'])) {
            foreach ($io->totalsockets as $socket) {
//                $io->emit($_POST['event'], $_POST['msg'] . $socket->conn->remoteAddress . ' ' . $_POST['remote']);
                if (strpos($socket->conn->remoteAddress, $_POST['remote']) !== false) {
                    $socket->emit($_POST['event'], $_POST['msg']);
//                    break;
                }
            }
        } else {
            $io->emit($_POST['event'], $_POST['msg']);    
        }
        
        $http_connection->send('ok');
    };
    $inner_http_worker->listen();
});

$io->on('connection', function($socket)use($io){
    if ($socket->conn->remoteAddress) {
        $io->totalsockets[] = $socket;
    }
});

$io->on('begin loading', function ($socket)use($io) {

});

Worker::runAll();
