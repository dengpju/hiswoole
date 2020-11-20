<?php


namespace App\controllers;


use Src\Annotations\Bean;
use Src\Annotations\RequestMapping;
use Src\Annotations\Value;
use Src\Http\Request;

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
     * @RequestMapping(value="/test/{uid:\d+}")
     * @return string
     */
    public function test(Request $request, int $uid){
        var_dump($request->getQueryParams());
        return 'test11'.$uid;
    }
}