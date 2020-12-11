<?php


namespace Src\Annotations\handler;


use Src\Annotations\Redis;
use Src\Core\BeanFactory;
use Src\Init\DecoratorCollector;
use Swoole\Coroutine\Channel;

function getKey(string $key, array $params){
    $pattern = "/^#(\d+)/i";
    if (preg_match($pattern, $key, $matches)){
        return $params[$matches[1]];
    }
    return $key;
}

function getKeyFromData($key, array $arr){
    $pattern = "/^#(\w+)/i";
    if (preg_match($pattern, $key, $matches)){
        if (isset($arr[$matches[1]])){
            return $arr[$matches[1]];
        }
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
        if ($self->incr != ""){
            $redis->hIncrBy($fullKey, $self->incr, 1);
        }
        return $fromRedisGet;
    }else{
        $result = call_user_func($func, ...$params);
        if (is_object($result)){
            $result = json_decode(json_encode($result), true);
        }
        $dataKeys = implode("", array_keys($result));
        if (preg_match("/^\d+$/", $dataKeys)){
            foreach ($result as $data) {
                $redis->hMSet($self->prefix . getKeyFromData($self->key, $data), $data);
            }
        } else {
            $redis->hMSet($fullKey, $result);
        }
        return $result;
    }
}

function redisBySortedSet(Redis $self, array $params, $func){
    $config = config('redis.default');
    $redis = new \Redis();
    $redis->connect($config['host'], $config['port']);
    $redis->auth($config['auth']);

    if ($self->coroutine){
        /**
         * @var Channel $chan
         */
        $chan = call_user_func($func, ...$params);
        $length = $chan->length();
        $result= [];
        while ($chan->length()){
            $result = array_merge($result, $chan->pop(5));
        }
        if (!$length){
            return ["result"=>"error"];
        }
    }else{
        $result = call_user_func($func, ...$params);
    }
    if (is_object($result)){
        $result = json_decode(json_encode($result), true);
    }
    foreach ($result as $data) {
        $redis->zAdd($self->prefix, $data[$self->score],$self->member.$data[$self->key]);
    }
    return ["result"=>"success"];
}

function redisByLua(Redis $self, array $params, $func){
    $config = config('redis.default');
    $redis = new \Redis();
    $redis->connect($config['host'], $config['port']);
    $redis->auth($config['auth']);
    return $redis->eval($self->script);
}

return [
    Redis::class => function(\ReflectionMethod $method, object $instance , Redis $self){
        $dCollector = BeanFactory::getBean(DecoratorCollector::class);
        $key = get_class($instance).'::'.$method->getName();
        $dCollector->dSet[$key] = function ($func) use ($self) {
            return function (array $params) use ($func,$self) {
                if ($self->script != ""){
                    return redisByLua($self, $params, $func);
                }
                if ("" != $self->key){
                    switch ($self->type) {
                        case "string":
                            return redisByString($self, $params, $func);
                        case "hash":
                            return redisByHash($self, $params, $func);
                        case "sortedset":
                            return redisBySortedSet($self, $params, $func);
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