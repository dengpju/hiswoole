<?php


namespace Src\Init;


use Swoole\Process;

class HotReloadProcess
{
    public function run(){
        return new Process(function (){
           while (true){
               echo "hot reload process".PHP_EOL;
               sleep(3);
               $path = __ROOT__."/app/*.php";
               $files = glob($path);
           }
        });
    }
}