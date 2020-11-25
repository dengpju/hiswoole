<?php


namespace Src\Core;


use DI\Container;
use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use Doctrine\Common\Annotations\AnnotationReader;

class BeanFactory
{
    private static $env=[];
    /**
     * @var Container
     */
    private static $cotainer;

    private static $handler=[];

    /**
     * @throws \ReflectionException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \Exception
     */
    public static function init(){
        self::$env = parse_ini_file(ROOT_PAHT."/.env");
        $builder = new ContainerBuilder();
        $builder->useAnnotations(true);
        self::$cotainer = $builder->build();
        $handlers = glob(ROOT_PAHT.'/src/Annotations/handler/*.php');
        foreach ($handlers as $handler) {
            self::$handler = array_merge(self::$handler, require_once ($handler));
        }
        $scanDirs = [
            ROOT_PAHT.'/src/Init'=>"\Init",
            ROOT_PAHT.'/'.self::getEnv("scan_dir")=>self::getEnv("scan_root_namespace"),
        ];

        foreach ($scanDirs as $dir => $namespace) {
            self::scanBeans($dir, $namespace);
        }
    }

    /**
     * @param string $dir
     * @return array
     */
    private static function getAllFile(string $dir){
        $dirs = glob($dir.'/*');
        $ret = [];
        foreach ($dirs as $dir){
            if (is_dir($dir)){
                $ret = array_merge($ret, self::getAllFile($dir));
            }elseif (is_file($dir) && pathinfo($dir)["extension"]=="php"){
                $ret[] = $dir;
            }
        }
        return $ret;
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

    /**
     * @param string $path
     * @param string $namespace
     * @throws \ReflectionException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public static function scanBeans(string $path, string $namespace){
        $phpfiles = self::getAllFile($path);
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
                    if (isset(self::$handler[get_class($anno)])){
                        $handler = self::$handler[get_class($anno)];
                        try {
                            $instance = self::$cotainer->get($refClass->getName());
                        } catch (DependencyException $e) {
                        } catch (NotFoundException $e) {
                        }
                        self::handlerProperty($instance,$refClass,$reader);
                        self::handlerMethod($instance,$refClass,$reader);
                        $handler($instance, self::$cotainer, $anno);
                    }
                }
            }
        }
    }

    /**
     * @param $instance
     * @param \ReflectionClass $refClass
     * @param AnnotationReader $reader
     */
    private static function handlerProperty(&$instance, \ReflectionClass $refClass, AnnotationReader $reader){
        $properties = $refClass->getProperties();
        foreach ($properties as $property) {
            $annos = $reader->getPropertyAnnotations($property);
            foreach ($annos as $anno){
                $handler = self::$handler[get_class($anno)];
                $instance = $handler($property, $instance, $anno);
            }
        }
    }

    /**
     * @param $instance
     * @param \ReflectionClass $refClass
     * @param AnnotationReader $reader
     */
    private static function handlerMethod(&$instance, \ReflectionClass $refClass, AnnotationReader $reader){
        $methods = $refClass->getMethods();
        foreach ($methods as $method) {
            $annos = $reader->getMethodAnnotations($method);
            foreach ($annos as $anno){
                $handler = self::$handler[get_class($anno)];
                $instance = $handler($method, $instance, $anno);
            }
        }
    }

    /**
     * @param string $beanName
     * @return mixed|null
     */
    public static function getBean(string $beanName){
        try {
            return self::$cotainer->get($beanName);
        } catch (DependencyException $e) {
        } catch (NotFoundException $e) {
        }
    }

}