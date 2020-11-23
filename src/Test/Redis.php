<?php
namespace Src\Test;

use Src\Annotations\Bean;
use Src\Annotations\Value;

/**
 * @Bean()
 */
class Redis
{
    /**
     * @Value(name="uri")
     */
    public $uri;


}