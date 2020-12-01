<?php
namespace Src\Annotations\handler;

use Src\Annotations\Value;

return [
    Value::class => function(\ReflectionProperty $property, object $instance,Value $self){
        $env = parse_ini_file(__ROOT__.'/.env');
        if (!isset($env[$self->name]) || $self->name==''){
            return $instance;
        }else{
            $property->setValue($instance, $env[$self->name]);
            return $instance;
        }
    },
];

