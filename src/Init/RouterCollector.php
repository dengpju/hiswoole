<?php


namespace Src\Init;

use Src\Annotations\Bean;

/**
 * @Bean()
 */
class RouterCollector
{
    public $routes = [];

    public function addRoute($method, $uri, $handler){
        $this->routes[] = ['method'=>$method, 'uri'=>$uri, 'handler'=>$handler];

    }

    public function getDispatcher() {
        return \FastRoute\simpleDispatcher(function(\FastRoute\RouteCollector $r) {
            foreach ($this->routes as $route) {
                $r->addRoute($route['method'], $route['uri'], $route['handler']);
            }
        });
    }
}