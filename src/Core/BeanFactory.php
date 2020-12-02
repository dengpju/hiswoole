<?php


namespace Src\Core;


use DI\Container;
use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use Doctrine\Common\Annotations\AnnotationReader;

class BeanFactory
{
    /**
     * @var array
     */
    private static $env=[];
    /**
     * @var array
     */
    private static $config=[];
    /**
     * @var Container
     */
    private static $cotainer;
    /**
     * @var array
     */
    private static $handler=[];

    /**
     * @throws \ReflectionException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \Exception
     */
    public static function init(){
        $builder = new ContainerBuilder();
        $builder->useAnnotations(true);
        self::$cotainer = $builder->build();
        $handlers = glob(__ROOT__.'/src/Annotations/handler/*.php');
        foreach ($handlers as $handler) {
            self::$handler = array_merge(self::$handler, require_once ($handler));
        }
        $scanDirs = [
            __ROOT__.'/src/Init'=>"\Init",
            __ROOT__.'/'.self::getEnv("scan_dir")=>self::getEnv("scan_root_namespace"),
        ];
        foreach ($scanDirs as $dir => $namespace) {
            self::scanBeans($dir, $namespace);
        }
    }

    /**
     * 加载env
     */
    public static function loadEnv(){
        self::$env = self::parseEnv(__ROOT__."/.env");
    }

    /**
     * @param string $key
     * @param string $default
     * @return mixed|string
     */
    public static function getEnv(string $key, string $default=""){
        if (isset(self::$env[$key])){
            return self::$env[$key];
        }
        return $default;
    }

    /**
     * @param string $path
     * @return array|bool
     */
    private static function parseEnv(string $path){
        $envs = parse_ini_file($path);
        foreach ($envs as &$env){
            if (strpos($env, '#') !== false){
                $env = trim(current(explode('#', $env)));
            }
            if (strpos($env, ';') !== false){
                $env = trim(current(explode(';', $env)));
            }
        }
        return $envs;
    }

    /**
     * 加载config
     */
    public static function loadConfig(){
        if (file_exists(__ROOT__."/config")){
            $phpfiles = self::getAllFile(__ROOT__."/config");
            foreach ($phpfiles as $php){
                $value = require_once ($php);
                self::$config[str_replace('.php','',basename($php))] = $value;
            }
        }
    }

    /**
     * @param string $key
     * @param string $default
     * @return mixed|string
     */
    public static function getConfig(string $key, string $default=""){
        $keys = array_filter(explode('.', $key));
        $config = self::$config;
        $value = $default;
        $item = count($keys);
        foreach ($keys as $i => $k) {
            if (isset($config[$k])){
                if ($i == ($item - 1)){
                    $value = $config[$k];
                    break;
                }
                if (is_array($config[$k])){
                    $config = $config[$k];
                }else{
                    $value = $config[$k];
                }
            }else{
                break;
            }
        }
        return $value;
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
     * @param string $path
     * @param string $namespace
     * @throws \ReflectionException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \Exception
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
                        // 处理属性
                        self::handlerProperty($instance,$refClass,$reader);
                        // 处理方法
                        self::handlerMethod($instance,$refClass,$reader);
                        // 执行
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