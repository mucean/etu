<?php

namespace Etu;

use Etu\Traits\ArrayPropertyReadAccess;

/**
 * Class Service
 * @author mucean
 */
abstract class Service
{
    use ArrayPropertyReadAccess;

    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->registerPropertyAccess('config');
    }

    public function getConfig($name = null, $default = null)
    {
        if ($name === null) {
            return $this->config;
        }

        return $this->getProperty('config', $name, $default);
    }
}
