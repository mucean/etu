<?php
namespace Etu\Traits;

/**
 * singleton mode
 *
 * @package singleton
 * @author mucean
 */
trait Singleton
{
    /**
     * static instance
     * @var static
     */
    protected static $instance;

    /**
     * return class instance, if $this->instance exist class instance,
     * direct return, otherwise new $className
     * store and return instance
     *
     * @return static
     */
    public static function getInstance()
    {
        if (static::$instance !== null) {
            return static::$instance;
        }

        return static::$instance = new static();
    }

    protected function __construct() {}
    protected function __clone() {}
}
