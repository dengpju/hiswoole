<?php


namespace Src\Core;


use Doctrine\Common\Annotations\AnnotationReader;
use Src\Annotations\Bean;

class BeanFactory
{
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
        foreach ($classes as $class){
            if (strstr($class, $namespace)){
                $refClass = new \ReflectionClass($class);
                $annos = $reader->getClassAnnotations($refClass);
                foreach ($annos as $anno) {
                    if ($anno instanceof Bean){
                        self::$beans[$refClass->getName()] = self::loadClass($refClass->getName(),$refClass->newInstance());
                    }
                }
            }
        }
        var_dump(self::$beans);
        die;
    }

    /**
     * @param string $beanName
     * @return mixed|null
     */
    public static function getBean(string $beanName){
        if (isset(self::$beans[$beanName])){
            return self::$beans[$beanName];
        }
        return null;
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
        return $object;
    }


}