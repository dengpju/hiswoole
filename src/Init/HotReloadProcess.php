<?php


namespace Src\Init;


use Swoole\Process;

class HotReloadProcess
{
    /**
     * @var string
     */
    private $md5;

    /**
     * @return Process
     */
    public function run(){
        return new Process(function (Process $process){
            cli_set_process_title("hiswoole reload");
           while (true){
               echo "hot reload process".PHP_EOL;
               sleep(3);
               $md5 = $this->getFileMd5(__ROOT__.'/app/*');
               if (!$this->md5){
                   $this->md5 = $md5;
                   continue;
               }
               if ($this->md5 != $md5){
                   $this->md5 = $md5;
                   $masterPid = file_get_contents(__ROOT__.'/runtime/hiswoole.pid');
                   Process::kill($masterPid, SIGUSR1);
               }
           }
        });
    }

    /**
     * @param string $dir
     * @param string $ignore
     * @return string
     */
    public function getFileMd5(string $dir){
        $files=glob($dir);
        $ret=[];
        foreach($files as $file){
            if(is_dir($file))
                $ret[]=$this->getFileMd5($file."/*");
            else if(isset(pathinfo($file)["extension"]) && pathinfo($file)["extension"]=="php"){
                $ret[]=md5_file($file);
            }
        }
        return md5(implode("",$ret));
    }
}