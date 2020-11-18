<?php


namespace Src\Core;


class RouterCollectr
{
    public static $routes = [];

    public static function addRoute($method, $uri, $handler){
        self::$routes[] = ['method'=>$method, 'uri'=>$uri, 'handler'=>$handler];

    }
}