<?php


namespace App\controllers;


use Src\Annotations\Bean;
use Src\Annotations\RequestMapping;
use Src\Annotations\Value;

/**
 * @Bean(name="user")
 */
class UserController
{
    /**
     * @Value(name="uri")
     */
    public $v='1.0';

    /**
     * @RequestMapping(value="/test")
     * @return string
     */
    public function test(){
        return 'test';
    }
}