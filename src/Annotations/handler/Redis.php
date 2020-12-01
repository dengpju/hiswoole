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
                    $fromRedisGet = "fromRedisGet";
                    if ($fromRedisGet){
                        return $fromRedisGet;
                    }else{
                        $result = call_user_func($func, ...$params);
                        //TODO:: set to redis
                        return $result;
                    }
                }
                return call_user_func($func, ...$params);
            };
        };
        return $instance;
    },
];