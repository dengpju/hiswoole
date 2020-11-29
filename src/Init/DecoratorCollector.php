<?php


namespace Src\Init;

use Src\Annotations\Bean;

/**
 * @Bean()
 */
class DecoratorCollector
{
    public $dSet = [];

    /**
     * @param \ReflectionMethod $method
     * @param $instance
     * @param $inputParams
     * @return mixed
     */
    public function exec(\ReflectionMethod $method, $instance, $inputParams){

        var_dump($instance);
        $key = get_class($instance).'::'.$method->getName();
        if (isset($this->dSet[$key])){
            $func = $this->dSet[$key];
            return $func($method->getClosure($instance))($inputParams);
        }
        return $method->invokeArgs($instance,$inputParams);
    }
}