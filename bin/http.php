<?php
define('__ROOT__', __DIR__.'/../');
require_once __ROOT__.'/src/Server/HttpServer.php';
use Src\Server\HttpServer;
use Swoole\Process;
use Swoole\Runtime;
Runtime::enableCoroutine();

if ($argc=2){
    $cmd = $argv[1];
    if ($cmd=="start"){
        $http = new HttpServer();
        $http->run();
    }else if ($cmd == "stop"){
        $masterPid=(int)file_get_contents(__ROOT__.'/runtime/hiswoole.pid');
        if ($masterPid && trim($masterPid) != 0){
            Process::kill($masterPid);
        }
    }
}