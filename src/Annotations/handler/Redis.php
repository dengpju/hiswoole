<?php


namespace Src\Annotations\handler;


use Src\Annotations\Redis;
use Src\Core\BeanFactory;
use Src\Init\DecoratorCollector;

return [
    Redis::class => function(\ReflectionMethod $method, $instance , $self){
        $dCollector = BeanFactory::getBean(DecoratorCollector::class);
        $key = get_class($instance).'::'.$method->getName();
        $dCollector->dSet[$key] = function ($func) {
            return function ($params) use ($func) {
                return call_user_func($func, $params);
            };
        };
    },
];