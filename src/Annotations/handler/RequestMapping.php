<?php
namespace Src\Annotations\handler;

use Src\Annotations\RequestMapping;
use Src\Core\RouterCollectr;

return [
    RequestMapping::class => function(\ReflectionMethod $method, $instance, $self){
        var_dump($self->value);
        RouterCollectr::addRoute("ANY", $self->value, $method);
        return $instance;
    },
];