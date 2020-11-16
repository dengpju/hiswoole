<?php

namespace Src\Annotations;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Value
{
    public $name;

    public function do(){
        $ini = parse_ini_file(__ROOT__.'/.env');
        if (isset($ini[$this->name])){
            return $ini[$this->name];
        }
        return '';
    }
}