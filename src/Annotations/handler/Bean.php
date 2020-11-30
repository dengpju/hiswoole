<?php
namespace Src\Annotations\handler;

use DI\Container;
use Src\Annotations\Bean;

return [
    Bean::class => function(object $instance, Container $container,$self){
        $vars=get_object_vars($self);
        if(isset($vars["name"]) && $vars["name"]!=""){
            $beanName=$vars["name"];
        } else{
            $arrs=explode("\\",get_class($instance));
            $beanName=end($arrs);
        }
        $container->set($beanName,$instance);
    },
];

