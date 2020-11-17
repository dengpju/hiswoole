<?php


namespace Src\Test;


use DI\Annotation\Inject;

class User
{
    private $redis;

    /**
     * @Inject()
     * @var MySql
     */
    public $mysql;

    /**
     * @Inject()
     * @param Redis $redis
     */
    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }


}