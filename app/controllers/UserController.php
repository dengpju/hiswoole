<?php


namespace App\controllers;


use Src\Annotations\Bean;
use Src\Annotations\Redis;
use Src\Annotations\RequestMapping;
use Src\Annotations\Value;
use Src\Http\Request;
use Src\Http\Response;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use function Swoole\Coroutine\run;

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
     * @Redis(key="#2", prefix="users",expire="60",type="hash",incr="count")
     * @RequestMapping(value="/test/{uid:\d+}")
     * @return string|array
     */
    public function test(Request $request, Response $response,int $uid){
        var_dump($request->getQueryParams());
//        return 'test11'.$uid;
        return ['uid'=>$uid,'name'=>'uuuttt'];
    }

    /**
     * @Redis(prefix="stock",key="prod_id",member="prod",score="prod_stock",type="sortedset",coroutine=true)
     * @RequestMapping(value="/tests")
     * @param Request $request
     * @param Response $response
     */
    public function tests(Request $request, Response $response){
        $chan = new Channel(200);
        $wg = new \Swoole\Coroutine\WaitGroup();
        for ($y = 0; $y < 5; $y++) {
            $wg->add();
            $prods = [
                ["prod_id"=>12 + $y ,"prod_stock"=>31 + $y],
                ["prod_id"=>13 + $y,"prod_stock"=>32 + $y],
            ];
            Coroutine::create(function () use ($wg, $y,$chan,$prods) {
                $chan->push($prods);
                $wg->done();
            });
        }
        $wg->wait();
        var_dump($chan->length());
//            Coroutine::create(function () use (&$chan) {
//                for ($i = 0; $i < 100; $i++){
//                    $prods = [
//                        ["prod_id"=>12 + $i,"prod_stock"=>32 + $i],
//                        ["prod_id"=>13 + $i,"prod_stock"=>31 + $i],
//                    ];
//                    $chan->push($prods);
//                }
//            });


//        $prods = [
//                    ["prod_id"=>12,"prod_stock"=>32],
//                    ["prod_id"=>13,"prod_stock"=>32],
//                ];
//        $chan->push($prods);
        return $chan;
    }
}