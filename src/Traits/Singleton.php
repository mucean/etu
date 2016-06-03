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
     *     'classname1' => $instance1,
     *     'classname2' => $instance2,
     *     ...
     * ]
     *
     * @var array
     */
    protected static $instances = [];

    /**
     * return class instance, if $this->instances exist correspond
     * class instance, direct return, otherwise new $classname
     * store and return instance
     *
     * @param  array  $params
     * @return object
     */
    public static function getInstance($params = [])
    {
        $classname = get_called_class();

        if (isset(static::$instances[$classname])) {
            return static::$instances[$classname];
        }

        static::$instances[$classname] = new static($params);

        return static::$instances[$classname];
    }

    protected function __construct() {}
    protected function __clone() {}
}
