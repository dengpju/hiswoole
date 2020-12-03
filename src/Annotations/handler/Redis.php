<?php


namespace Src\Annotations\handler;


use Src\Annotations\Redis;
use Src\Core\BeanFactory;
use Src\Init\DecoratorCollector;

return [
    Redis::class => function(\ReflectionMethod $method, object $instance , Redis $self){
        $dCollector = BeanFactory::getBean(DecoratorCollector::class);
        $key = get_class($instance).'::'.$method->getName();
        $dCollector->dSet[$key] = function ($func) use ($self) {
            return function (array $params) use ($func,$self) {
                if ($self->key){
                    echo $self->key,PHP_EOL;
                    $fullKey = $self->prefix.$self->key;
                    $config = config('redis.default');
                    $redis = new \Redis();
                    $redis->connect($config['host'], $config['port']);
                    $redis->auth($config['auth']);
                    $fromRedisGet = $redis->get($fullKey);
                    if ($fromRedisGet){
                        return json_decode($fromRedisGet,true);
                    }else{
                        $result = call_user_func($func, ...$params);
                        $redis->set($fullKey,json_encode($result,JSON_UNESCAPED_UNICODE));
                        return $result;
                    }
                }
                return call_user_func($func, ...$params);
            };
        };
        return $instance;
    },
];