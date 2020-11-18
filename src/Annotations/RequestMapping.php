<?php


namespace Src\Annotations;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class RequestMapping
{
    public $value="";//路径
    public $method=[];
}