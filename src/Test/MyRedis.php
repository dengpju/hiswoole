<?php
namespace Src\Test;


use Src\Annotations\Bean;
use Src\Annotations\Value;

/**
 * @Bean()
 */
class MyRedis
{
    /**
     * @Value(name="uri")
     */
    public $uri;
}