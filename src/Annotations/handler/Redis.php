<?php


namespace Src\Annotations\handler;


use Src\Annotations\Redis;
use Src\Core\BeanFactory;
use Src\Init\DecoratorCollector;

function getKey(string $key, array $params){
    $pattern = "/^#(\d+)/i";
    if (preg_match($pattern, $key, $matches)){
        return $params[$matches[1]];
    }
    return $key;
}

function redisByString(Redis $self, array $params, $func){
    $fullKey = $self->prefix.getKey($self->key, $params);
    $config = config('redis.default');
    $redis = new \Redis();
    $redis->connect($config['host'], $config['port']);
    $redis->auth($config['auth']);
    $fromRedisGet = $redis->get($fullKey);
    if ($fromRedisGet){
        return json_decode($fromRedisGet,true);
    }else{
        $result = call_user_func($func, ...$params);
        if ($self->expire > 0){
            $redis->setex($fullKey,$self->expire,json_encode($result,JSON_UNESCAPED_UNICODE));
        }else{
            $redis->set($fullKey,json_encode($result,JSON_UNESCAPED_UNICODE));
        }
        return $result;
    }
}

function redisByHash(Redis $self, array $params, $func){
    $fullKey = $self->prefix.getKey($self->key, $params);
    $config = config('redis.default');
    $redis = new \Redis();
    $redis->connect($config['host'], $config['port']);
    $redis->auth($config['auth']);
    $fromRedisGet = $redis->hGetAll($fullKey);
    if ($fromRedisGet){
        return $fromRedisGet;
    }else{
        $result = call_user_func($func, ...$params);
        if (is_object($result)){
            $result = json_decode(json_encode($result), true);
        }
        if ($self->expire > 0){
            $redis->hMSet($fullKey, $result);
        }else{
            $redis->hMSet($fullKey, $result);
        }
        return $result;
    }
}

return [
    Redis::class => function(\ReflectionMethod $method, object $instance , Redis $self){
        $dCollector = BeanFactory::getBean(DecoratorCollector::class);
        $key = get_class($instance).'::'.$method->getName();
        $dCollector->dSet[$key] = function ($func) use ($self) {
            return function (array $params) use ($func,$self) {
                if ("" != $self->key){
                    switch ($self->type) {
                        case "string":
                            return redisByString($self, $params, $func);
                        case "hash":
                            return redisByString($self, $params, $func);
                        default:
                            return call_user_func($func, ...$params);
                    }
                }
                return call_user_func($func, ...$params);
            };
        };
        return $instance;
    },
];