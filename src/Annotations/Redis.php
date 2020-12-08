<?php


namespace Src\Annotations;


/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Redis
{
    public $source="default";
    public $key = "";
    public $prefix="";
    public $type = "string";
    public $expire=0;
    public $incr="";
}