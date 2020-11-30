<?php
namespace Src\Annotations\handler;

use Src\Annotations\Value;

return [
    Value::class => function(\ReflectionProperty $property, object $instance,$self){
        $env = parse_ini_file(ROOT_PAHT.'/.env');
        if (!isset($env[$self->name]) || $self->name==''){
            return $instance;
        }else{
            $property->setValue($instance, $env[$self->name]);
            return $instance;
        }
    },
];

