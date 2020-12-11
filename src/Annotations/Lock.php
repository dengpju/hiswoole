<?php


namespace Src\Annotations;

use Doctrine\Common\Annotations\Annotation\IgnoreAnnotation;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Lock
{
    public $prefix="";
    public $key="";
    public $retry=3;
    public $expire = 10;
}