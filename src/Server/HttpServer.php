<?php
namespace Src\Server;
use FastRoute\Dispatcher;
use Src\Core\BeanFactory;
use Src\Init\HotReloadProcess;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Http\Server;


class HttpServer
{
    /**
     * @var Server
     */
    private $server;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * HttpServer constructor.
     * @param $server
     * @throws \Exception
     */
    public function __construct(string $addr='0.0.0.0', int $port = 9501)
    {
        $this->server = new Server($addr, $port);
        $this->server->set([
            'worker_num' => 1,
            'daemonize' => false,
        ]);
        $this->server->on("WorkerStart", [$this, "onWorkerStart"]);
        $this->server->on("ManagerStart", [$this, "onManagerStart"]);
        $this->server->on('Request', [$this, "onRequest"]);
        $this->server->on("Start", [$this, "osStart"]);
        $this->server->on("ShutDown", [$this, "onShutDown"]);
    }

    /**
     * @param Request $request
     * @param Response $response
     */
    public function onRequest(Request $request, Response $response) {
        $myrequest = \Src\Http\Request::init($request);
        $myresponse = \Src\Http\Response::init($response);
        $routeInfo = $this->dispatcher->dispatch($myrequest->getMethod(), $myrequest->getUri());
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                // ... 404 Not Found
                $response->status(404);
                $response->end();
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                // ... 405 Method Not Allowed
                $response->status(405);
                $response->end();
                break;
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                $extVars = [$myrequest, $myresponse];
                $myresponse->setBody($handler($vars, $extVars));
                // ... call $handler with $vars
                $myresponse->end();
                break;
        }
    }

    /**
     * @param Server $server
     */
    public function osStart(Server $server){
        cli_set_process_title("hiswoole master");
        $masterPid = $server->master_pid;
        file_put_contents(__ROOT__.'/runtime/hiswoole.pid',$masterPid);
    }

    /**
     * @param Server $server
     */
    public function onShutDown(Server $server){
        unlink(__ROOT__.'/runtime/hiswoole.pid');
        echo "关闭了" . PHP_EOL;
    }

    /**
     * @param Server $server
     * @param int $workerId
     * @throws \Exception
     */
    public function onWorkerStart(Server $server, int $workerId) {
        cli_set_process_title("hiswoole worker");
        BeanFactory::init();
        $this->dispatcher = BeanFactory::getBean("RouterCollector")->getDispatcher();
    }

    /**
     * @param Server $server
     */
    public function onManagerStart(Server $server) {
        cli_set_process_title("hiswoole manager");
    }

    public function run(){
        if (!file_exists(__RUNTIME__)){
            mkdir(__RUNTIME__);
        }
        echo "启动了" . PHP_EOL;
        $p = new HotReloadProcess();
        $this->server->addProcess($p->run());
        $this->server->start();
    }
}