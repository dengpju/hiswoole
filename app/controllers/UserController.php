<?php


namespace App\controllers;


use Src\Annotations\Bean;
use Src\Annotations\Redis;
use Src\Annotations\RequestMapping;
use Src\Annotations\Value;
use Src\Http\Request;
use Src\Http\Response;

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
     * @Redis(key="#2", prefix="users",expire="60")
     * @RequestMapping(value="/test/{uid:\d+}")
     * @return string|array
     */
    public function test(Request $request, Response $response,int $uid){
        var_dump($request->getQueryParams());
//        return 'test11'.$uid;
        return ['uid'=>$uid,'name'=>'uuuttt'];
    }
}