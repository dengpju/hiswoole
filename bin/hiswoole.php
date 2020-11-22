<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Src\Core\BeanFactory;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Process;

define('__ROOT__', __DIR__.'/../');

if ($argc=2){
    $cmd = $argv[1];
    if ($cmd=="start"){
        $loader = require_once __ROOT__.'/vendor/autoload.php';
        require_once __ROOT__.'/app/config/define.php';
        Swoole\Runtime::enableCoroutine();
        AnnotationRegistry::registerLoader([$loader,'loadClass']);
        BeanFactory::init();
        $dispatcher = BeanFactory::getBean("RouterCollector")->getDispatcher();

        $http = new Swoole\Http\Server("0.0.0.0", 81);
        $http->set([
           'worker_num' => 1,
           'daemonize' => false,
        ]);
        $http->on('request', function (Request $request, Response $response) use ($dispatcher) {
            $myrequest = \Src\Http\Request::init($request);
            $myresponse = \Src\Http\Response::init($response);
            $routeInfo = $dispatcher->dispatch($myrequest->getMethod(), $myrequest->getUri());
            switch ($routeInfo[0]) {
                case FastRoute\Dispatcher::NOT_FOUND:
                    // ... 404 Not Found
                    $response->status(404);
                    $response->end();
                    break;
                case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                    $allowedMethods = $routeInfo[1];
                    // ... 405 Method Not Allowed
                    $response->status(405);
                    $response->end();
                    break;
                case FastRoute\Dispatcher::FOUND:
                    $handler = $routeInfo[1];
                    $vars = $routeInfo[2];
                    $extVars = [$myrequest, $myresponse];
                    $myresponse->setBody($handler($vars, $extVars));
                    // ... call $handler with $vars
                    $myresponse->end();
                    break;
            }
        });
        $http->on("Start", function (\Swoole\Server $server){
            $masterPid = $server->master_pid;
            file_put_contents(__ROOT__.'/runtime/hiswoole.pid',$masterPid);
        });
        $http->start();
    }else if ($cmd == "stop"){
        $masterPid=(int)file_get_contents(__ROOT__.'/runtime/hiswoole.pid');
        if ($masterPid && trim($masterPid) != 0){
            Process::kill($masterPid);
        }

    }
}