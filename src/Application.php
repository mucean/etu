<?php

namespace Etu;

class Application
{
    public function start()
    {
        //todo start
    }

    public function registerNamespace($dir, callable $func = null)
    {
        $dir = rtrim(strval($dir), DIRECTORY_SEPARATOR);
        if (DEBUG) {
            if (!is_dir($dir)) {
                throw new \Exception(sprintf('invalid directory was given: %s', $dir));
            }
        }
    }
}
