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
}