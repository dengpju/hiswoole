<?php

use Src\Core\BeanFactory;

if ((!function_exists('_env'))) {
    /**
     * @param string $key
     * @param string $default
     * @return mixed|string
     */
    function _env(string $key, string $default="")
    {
        return BeanFactory::getEnv($key, $default);
    }
}

if ((!function_exists('config'))) {
    /**
     * @param string $key
     * @param string $default
     * @return mixed|string
     */
    function config(string $key, string $default="")
    {
        return BeanFactory::getConfig($key, $default);
    }
}