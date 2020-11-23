<?php

Swoole\Coroutine\run(function(){
    $client = new Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);
    if (!$client->connect('192.168.42.131', 8085, 0.5))
    {
        echo "connect failed. Error: {$client->errCode}\n";
    }
    $client->send("hello world\n");
    echo $client->recv().PHP_EOL;
    $client->close();
});