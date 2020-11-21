<?php


namespace Src\Http;


class Response
{
    /**
     * @var \Swoole\Http\Response
     */
    protected $swooleReponse;
    protected $body;

    /**
     * Response constructor.
     * @param $swooleReponse
     */
    public function __construct($swooleReponse)
    {
        $this->swooleReponse = $swooleReponse;
        $this->setHeader("Content-Type", "text/plain;charset=utf8");
    }

    public static function init(\Swoole\Http\Response $response){
        return new self($response);
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body): void
    {
        $this->body = $body;
    }

    /**
     * @param string $key
     * @param $value
     */
    public function setHeader(string $key, $value){
        $this->swooleReponse->header($key, $value);
    }

    /**
     * @param string $content
     */
    public function write(string $content){
        $this->swooleReponse->write($content);
    }

    /**
     * @param int $code
     */
    public function writeHttpStatus(int $code){
        $this->swooleReponse->status($code);
    }

    /**
     * @param string $url
     * @param int $code
     */
    public function redirect(string $url, int $code=301){
        $this->writeHttpStatus($code);
        $this->setHeader("Location", $url);
    }

    public function end(){
        $ret = $this->getBody();
        gettype($ret);
        if (in_array(gettype($ret),['array'])){
            $this->swooleReponse->header("Content-Type","application/json;charset=utf-8");
            $this->swooleReponse->write(json_encode($ret));
        }else{
            $this->swooleReponse->write($this->getBody());
        }

        $this->swooleReponse->end();
    }
}