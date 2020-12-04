<?php
namespace  Src\Lib;

use Src\Core\BeanFactory;
use Src\Init\PHPRedisPool;

/**
 * Class RedisHelper
 * @method  static string get(string $key)
 */
class RedisHelper{


    public static function __callStatic($name, $arguments)
    {
        /** @var  $pool PHPRedisPool */
        $pool=BeanFactory::getBean(PHPRedisPool::class);
        $redis_obj=$pool->getConnection();
        try{
            if(!$redis_obj) return false;
            $redis=$redis_obj->redis;
            return $redis->$name(...$arguments);
        }catch (\Exception $exception){
            var_dump($exception->getMessage());
            return false;
        }finally{
            if($redis_obj)
                $pool->close($redis_obj);
        }
    }
}