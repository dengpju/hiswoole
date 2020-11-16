<?php
require 'vendor/autoload.php';

use Doctrine\Common\Annotations\AnnotationRegistry;
use Src\Test\MyRedis;

define('__ROOT__', __DIR__);

AnnotationRegistry::registerAutoloadNamespace('Src\Annotations');
$myRedis = new MyRedis();
var_dump($myRedis);

$value = new \Src\Annotations\Value();
var_dump($value);
$value->do();