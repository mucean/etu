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
     * instances of singleton mode class
     * @example $instances = [
     *     'className1' => $instance1,
     *     'className2' => $instance2,
     *     ...
     * ]
     *
     * @var array
     */
    protected static $instances = [];

    /**
     * return class instance, if $this->instances exist correspond
     * class instance, direct return, otherwise new $className
     * store and return instance
     *
     * @param  array  $params
     * @return object
     */
    public static function getInstance($params = [])
    {
        $className = get_called_class();

        if (isset(static::$instances[$className])) {
            return static::$instances[$className];
        }

        static::$instances[$className] = new static($params);

        return static::$instances[$className];
    }

    protected function __construct() {}
    protected function __clone() {}
}
