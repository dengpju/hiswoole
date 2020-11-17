<?php


namespace Src\Annotations;


use DI\Container;

return [
    Bean::class => function($instance, Container $cotainer){
        $arrs = explode("\\", get_class($instance));
        $beanName = end($arrs);
        $cotainer->set($beanName, $instance);
    },
    Value::class => function($instance,Container $cotainer){

    },
];