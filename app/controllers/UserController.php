<?php


namespace App\controllers;


use Src\Annotations\Bean;
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
}