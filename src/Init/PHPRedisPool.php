<?php
namespace Src\Init;
use Core\lib\RedisPool;
use Src\Annotations\Bean;

/**
 * Class PDOPool
 * @Bean()
 */
class PHPRedisPool extends RedisPool{

    public function __construct(int $min = 5, int $max = 10)
    {
        global $GLOBAL_CONFIGS;
        $poolconfig=$GLOBAL_CONFIGS["redispool"]["default"];
        parent::__construct($poolconfig['min'], $poolconfig['max'],$poolconfig['idleTime']);
    }
    protected function newRedis()
    {
        global $GLOBAL_CONFIGS;
        $default=$GLOBAL_CONFIGS["redis"]["default"];


        $redis=new \Redis();
        $redis->connect($default["host"],$default["port"]);
        if($default["auth"]!="")
            $redis->auth($default["auth"]);
        return $redis;
    }
}