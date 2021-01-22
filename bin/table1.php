<?php
cli_set_process_title("process main");
$table = new Swoole\Table(1024);
$table->column('id', Swoole\Table::TYPE_INT, 4); //1,2,4,8
$table->column('name', Swoole\Table::TYPE_STRING, 64);
$table->column('age', Swoole\Table::TYPE_FLOAT);
$table->create();

$table->set('1', ['id' => 1, 'name' => 'test1', 'age' => 20]);
$table->set('2', ['id' => 2, 'name' => 'test2', 'age' => 21]);
$table->set('3', ['id' => 3, 'name' => 'test3', 'age' => 19]);

$worker = new Swoole\Process(function (Swoole\Process $process) use($table) {
    cli_set_process_title("process worker");
    while (1){
        echo "进程id:{$process->pid} ".json_encode($table->get("1")).PHP_EOL;
        sleep(5);
    }
}, false, false);
$worker->start();

Swoole\Process::wait(true);

