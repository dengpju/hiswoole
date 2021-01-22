<?php

/**
$workerNum = 10;
$pool = new Swoole\Process\Pool($workerNum);

$pool->on("WorkerStart", function ($pool, $workerId) {
    echo "Worker#{$workerId} is started\n";
    $redis = new Redis();
    $redis->pconnect('192.168.42.131', 6379);
    $redis->auth("1qaz2wsx");
    $key = "key1";
    while (true) {
        sleep(5);
        $msg = $redis->brpop($key, 2);
        if ( $msg == null) continue;
        echo "Worker#{$workerId} processing msg ".json_encode($msg).PHP_EOL;
    }
});

$pool->on("WorkerStop", function ($pool, $workerId) {
    echo "Worker#{$workerId} is stopped\n";
});

$pool->start();
 */

$pool = new Swoole\Process\Pool(2, SWOOLE_IPC_UNIXSOCK, 0, true);

$pool->on('workerStart', function (Swoole\Process\Pool $pool, int $workerId) {
    $process = $pool->getProcess(0);
    /**
     * @var Swoole\Coroutine\Socket $socket
     */
    $socket = $process->exportSocket();
    if ($workerId == 0) {
        echo "proc0 recv: ".$socket->recv().PHP_EOL;
        $socket->send("hello everybody the {$workerId}\n");
    } else {
        $socket->send("hello proc0 the {$workerId} \n");
        echo "proc{$workerId} recv: ".$socket->recv().PHP_EOL;
//        echo "proc{$workerId} stop\n";
        $pool->shutdown();
    }
});

//$pool->on("WorkerStop", function ($pool, $workerId) {
//    echo "Worker#{$workerId} is stopped\n";
//});

$pool->start();