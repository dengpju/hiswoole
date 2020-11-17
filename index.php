<?php
$loader = require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/app/config/define.php';
Swoole\Runtime::enableCoroutine();

use Doctrine\Common\Annotations\AnnotationRegistry;
use Src\Core\BeanFactory;
use Src\Test\Redis;
use Swoole\Coroutine as Co;
use Swoole\Http\Request;
use Swoole\Http\Response;

define('__ROOT__', __DIR__);

 AnnotationRegistry::registerLoader([$loader,'loadClass']);

//AnnotationRegistry::registerAutoloadNamespace("Src\Annotations");

//$rc=new ReflectionClass(MyRedis::class);
//$p=$rc->getProperty("conn_url");
//
//$reader = new AnnotationReader();
//$anno=$reader->getPropertyAnnotation($p,Value::class);
//echo $anno->name;

// BeanFactory::scanBeans(__DIR__.'/src/Test', 'Src\\Test');

//$myredis = BeanFactory::loadClass(MyRedis::class);
//var_dump($myredis);

//$redis = BeanFactory::getBean(Redis::class);
//var_dump($redis);

//$builder = new \DI\ContainerBuilder();
//$builder->useAnnotations(true);
//$container = $builder->build();
//$user = $container->get(\Src\Test\User::class);
//var_dump($user);

BeanFactory::init();
$user = BeanFactory::getBean("UserController");
var_dump($user);
die;

/**
 * 请用两个线程交替输出A1B2C3D4...，A线程输出字母，B线程输出数字，要求A线程首先执行，B线程其次执行！
 */
Co\run(function() {
    $cid = Co::getCid();
    $chan = new Swoole\Coroutine\Channel(1);
    Co::create(function () use ($chan){
        foreach (['A','B','C','D'] as $k => $v){
            echo $v;
            $chan->push($k + 1);
            Co::sleep(1.0);
        }
        $chan->push('finish');
    });
    Co::create(function () use ($chan) {
        while(1) {
            $data = $chan->pop();
            if ('finish' == $data){
                $chan->close();
                echo PHP_EOL;
                break;
            }
            echo $data;
        }
    });
});
/**
 * 有一个总任务A，分解为子任务A1 A2 A3 ...，任何一个子任务失败后要快速取消所有任务。
 */

Co\run(function (){

    for ($i = 0; $i < 3; $i++){
        $r = random_int(1, 10);
        Co::create(function () use ($r){
            echo '创建协程:'.Co::getCid().PHP_EOL;
            echo '协程:'.Co::getCid().','.$r.'秒后结束'.PHP_EOL;
            $n = 0;
            while (true){
                Co::sleep(1);
                if ($n > $r){
                    echo '协程:'.Co::getCid().'结束'.PHP_EOL;
                    break;
                }
                $n += 1;
            }
        });
    }
});


$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/test', function (){
        return 'my test';
    });
});

$http = new Swoole\Http\Server("0.0.0.0", 81);
$http->on('request', function (Request $request, Response$response) use ($dispatcher) {
    $myrequest = \Src\Core\Request::init($request);
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
            // ... call $handler with $vars
            $response->end($handler());
            break;
    }
});
$http->start();


