<?php

$worker = new Swoole\Process(function () {
    /**
     * @var Swoole\Table $table
     */
    $table = new Swoole\Table(1024);
    var_export($table->get("1"));
}, false, false);
$worker->start();

Swoole\Process::wait(true);