<?php


namespace Src\Annotations;


use DI\Container;

return [
    Bean::class => function($instance, Container $cotainer){
        $arrs = explode("\\", get_class($instance));
        $beanName = end($arrs);
        var_dump($beanName);
        $cotainer->set($beanName, $instance);
    },
    Value::class => function(\ReflectionProperty $property,$instance,$self){
        $env = parse_ini_file(ROOT_PAHT.'/.env');
        if (!isset($env[$self->name]) || $self->name==''){
            return $instance;
        }else{
            $property->setValue($instance, $env[$self->name]);
            return $instance;
        }
    },
];