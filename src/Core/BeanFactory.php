<?php


namespace Src\Core;


use DI\Container;
use DI\ContainerBuilder;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Src\Annotations\Bean;

class BeanFactory
{
    private static $env=[];
    /**
     * @var Container
     */
    private static $cotainer;

    private static $register=[];

    /**
     * @throws \ReflectionException
     */
    public static function init(){
        self::$env = parse_ini_file(ROOT_PAHT."/.env");
        $builder = new ContainerBuilder();
        $builder->useAnnotations(true);
        self::$cotainer = $builder->build();
        self::$register = require_once (ROOT_PAHT."/src/Annotations/Register.php");
        self::scanBeans(self::getEnv("scan_dir"), self::getEnv("scan_root_namespace"));
    }

    /**
     * @param string $key
     * @param string $default
     * @return mixed|string
     */
    private static function getEnv(string $key, string $default=""){
        if (isset(self::$env[$key])){
            return self::$env[$key];
        }
        return $default;
    }


    private static $beans=[];

    /**
     * @param string $path
     * @param string $namespace
     * @throws \ReflectionException
     */
    public static function scanBeans(string $path, string $namespace){

        $phpfiles = glob($path.'/*.php');
        foreach ($phpfiles as $php){
            require_once ($php);
        }
        $classes = get_declared_classes();
        $reader = new AnnotationReader();
//        AnnotationRegistry::registerAutoloadNamespace("Src\Annotations");
        foreach ($classes as $class){
            if (strstr($class, $namespace)){
                $refClass = new \ReflectionClass($class);
                $annos = $reader->getClassAnnotations($refClass);
                foreach ($annos as $anno) {
                    if (isset(self::$register[get_class($anno)])){
                        $handler = self::$register[get_class($anno)];
                        $instance = self::$cotainer->get($refClass->getName());
                        self::handlerPropAnnot($instance,$refClass,$reader);
                        $handler($instance, self::$cotainer);
                    }
                }
            }
        }
    }

    private static function handlerPropAnnot(&$instance, \ReflectionClass $refClass, AnnotationReader $reader){
        $properties = $refClass->getProperties();
        foreach ($properties as $property) {
            $annos = $reader->getPropertyAnnotations($property);
            foreach ($annos as $anno){
                $handler = self::$register[get_class($anno)];
                $instance = $handler($property, $instance, $anno);
            }
        }
    }

    /**
     * @param string $beanName
     * @return mixed|null
     */
    public static function getBean(string $beanName){
        return self::$cotainer->get($beanName);
    }

    /**
     * @param string $classname
     * @throws \ReflectionException'
     */
    public static function loadClass(string $classname, $object = false){
        $refClass = new \ReflectionClass($classname);
        $properties = $refClass->getProperties();
        $reader = new AnnotationReader();
        foreach ($properties as $property) {
            $annos = $reader->getPropertyAnnotations($property);
            foreach ($annos as $anno){
                $value = $anno->do();
                $retObj = $object ? $object:$refClass->newInstance();
                $property->setValue($retObj,$value);
                return $retObj;
            }
        }
        return $object ? $object:$refClass->newInstance();
    }


}