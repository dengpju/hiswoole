<?php

$workerNum = 1;
$pool = new Swoole\Process\Pool($workerNum);

$pool->on("WorkerStart", function ($pool, $workerId) {
    cli_set_process_title("producer");
    echo "Worker#{$workerId} is started\n";
    $redis = new Redis();
    $redis->pconnect('192.168.42.131', 6379);
    $redis->auth("1qaz2wsx");
    $key = "key1";
    while (true) {
        $msg = time();
        $redis->lPush($key, $msg);
        var_dump($msg);
        sleep(3.0);
    }
});

$pool->on("WorkerStop", function ($pool, $workerId) {
    echo "Worker#{$workerId} is stopped\n";
});

$pool->start();