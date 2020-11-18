<?php


namespace Src\Annotations;


use DI\Container;

return [
    Bean::class => function($instance, Container $container,$self){
        $vars=get_object_vars($self);
        if(isset($vars["name"]) && $vars["name"]!=""){
            $beanName=$vars["name"];
        } else{
            $arrs=explode("\\",get_class($instance));
            $beanName=end($arrs);
        }
        $container->set($beanName,$instance);
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