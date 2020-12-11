<?php

use Src\Annotations\Lock;
use Src\Core\BeanFactory;
use Src\Init\DecoratorCollector;
use function Src\Annotations\handler\getKey;

function getlock(Lock $self, array $params){
    $script = <<<LUA
            local key = KEYS[1]
            local expire = ARGV[1]
            if redis.call('setnx', key, 1) == 1 then
                return redis.call('expire', key, expire)
            end
            return 0
LUA;
    $config = config('redis.default');
    $redis = new \Redis();
    $redis->connect($config['host'], $config['port']);
    $redis->auth($config['auth']);
    return $redis->eval($script, [$self->prefix, getKey($self->key, $params), $self->expire], 1);
}

function unlock(Lock $self, array $params){
    $key = $self->prefix. getKey($self->key, $params);
    $script = <<<LUA
        local key = KEYS[1]
        return redis.call('del', key);
LUA;
    $config = config('redis.default');
    $redis = new \Redis();
    $redis->connect($config['host'], $config['port']);
    $redis->auth($config['auth']);
    return $redis->eval($script, [$self->prefix, $key], 1);
}

function lock(Lock $self, array $params){
    $retry = $self->retry;
    while ($retry-- > 0) {
        if (getlock($self, $params)) {
            return true;
        }
        usleep( 1000 * 100 * 1);
    }
    return false;
}

function exec(Lock $self, array $params, $func){
    try {
        if (lock($self, $params)) {
            $ret = call_user_func($func, ...$params);
            unlock($self, $params);
            return $ret;
        }
        return false;
    } catch (Exception $exception) {
        unlock($self, $params);
        return false;
    }
}

return [
    Lock::class => function(\ReflectionMethod $method, object $instance , Lock $self){
        $dCollector = BeanFactory::getBean(DecoratorCollector::class);
        $key = get_class($instance).'::'.$method->getName();
        $dCollector->dSet[$key] = function ($func) use ($self) {
            return function (array $params) use ($func,$self) {
                if ($self->key != ""){
                    $ret = exec($self, $params, $func);
                    if ($ret === false) {
                        return ['data locked'];
                    }else{
                        return $ret;
                    }
                }
                return call_user_func($func, ...$params);
            };
        };
        return $instance;
    }
];