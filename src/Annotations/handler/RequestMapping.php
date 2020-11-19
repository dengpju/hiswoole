<?php
namespace Src\Annotations\handler;

use Src\Annotations\RequestMapping;
use Src\Core\BeanFactory;

return [
    RequestMapping::class => function(\ReflectionMethod $method, $instance, $self){
        $routerCollectr = BeanFactory::getBean('RouterCollector');
        $routerCollectr->addRoute(count($self->method)>0?$self->method:["GET"],
            $self->value, function () use ($instance, $method){
                return $method->invoke($instance);
            });
        return $instance;
    },
];