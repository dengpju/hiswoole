#!/usr/bin/env php
<?php
ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');

error_reporting(E_ALL);

! defined('__ROOT__') && define('__ROOT__', dirname(__DIR__, 1));
! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL);

$loader = require __ROOT__ . '/vendor/autoload.php';
require __ROOT__.'/src/Server/HttpServer.php';

use Doctrine\Common\Annotations\AnnotationRegistry;
use Src\Server\HttpServer;
use Swoole\Process;
use Swoole\Runtime;
Runtime::enableCoroutine();

AnnotationRegistry::registerLoader([$loader, 'loadClass']);

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