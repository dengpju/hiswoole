<?php
require 'vendor/autoload.php';
Swoole\Runtime::enableCoroutine();


use Doctrine\Common\Annotations\AnnotationRegistry;
use Src\Test\MyRedis;
use Swoole\Coroutine as Co;
use Swoole\Runtime;

Runtime::enableCoroutine();

define('__ROOT__', __DIR__);

//AnnotationRegistry::registerAutoloadNamespace('Src\Annotations');
//$myRedis = new MyRedis();
//var_dump($myRedis);
//
//$value = new \Src\Annotations\Value();
//var_dump($value);
//$value->do();

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

