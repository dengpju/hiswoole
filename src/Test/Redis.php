<?php
namespace Src\Test;


use DI\Annotation\Inject;
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