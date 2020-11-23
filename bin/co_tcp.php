<?php

Swoole\Coroutine\run(function(){
    //每个进程都监听9501端口
    $server = new Swoole\Coroutine\Server('0.0.0.0', 9501 , false, true);

    //接收到新的连接请求 并自动创建一个协程
    $server->handle(function (Swoole\Coroutine\Server\Connection $conn) {
        while (true) {
            //接收数据
            $data = $conn->recv();
            if (empty($data)) {
                $conn->close();
                break;
            }else{
                echo $data.PHP_EOL;
            }

            //发送数据
            $conn->send('hello');

            Swoole\Coroutine::sleep(1);
        }
    });

    //开始监听端口
    $server->start();
});
