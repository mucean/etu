<?php

namespace Etu;

class Application
{
    public function start()
    {
        //todo start
    }

    public function registerNamespace($dir, $namespace, callable $func = null)
    {
        $dir = rtrim(strval($dir), DIRECTORY_SEPARATOR);
        if (DEBUG) {
            if (!is_dir($dir)) {
                throw new \Exception(sprintf('invalid directory was given: %s', $dir));
            }
        }

        if ($func === null) {
            $pre_namespace = ltrim($namespace, '\\');
            $pre_namespace_len = strlen($pre_namespace);
            $func = function ($class) use ($pre_namespace, $dir, $pre_namespace_len) {
                if ($pre_namespace === '') {
                    $part_dir = str_replace('\\', '/', ltrim($class, '\\'));
                } else {
                    $class = ltrim($class, '\\');
                    $validate_result = strpos($class, $pre_namespace);
                    if ($validate_result === false || $validate_result > 0) {
                        return null;
                    }
                    $part_dir = str_replace('\\', '/', substr($class, $pre_namespace_len + 1));
                }
                $file = $dir . DIRECTORY_SEPARATOR . $part_dir . '.php';
                if (is_file($file)) {
                    require_once($file);
                }
            };
        }

        spl_autoload_register($func);
    }
}
