<?php

namespace Etu\ORM;

abstract class Data
{
    protected static $mapperName = '\Etu\ORM\Mapper';

    protected static $mapperOptions = [
    ];

    /**
     * @var \Etu\ORM\Mapper | \Etu\ORM\Sql\Mapper
     */
    protected static $mapper;

    protected static $attributes = [];

    /**
     * values of modified attributes
     * @var array
     */
    protected $modifiedAttributes = [];

    /**
     * entity values
     * @var array
     */
    protected $values;

    /**
     * indicate this entity is new or not
     * @var bool
     */
    protected $isNew = true;

    /**
     * fetch the value of the entity
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        if (array_key_exists($name, static::$attributes) === false) {
            throw new \InvalidArgumentException(sprintf('%s attribute does not exist', $name));
        }

        $value = $this->values[$name];
        if (array_key_exists('get', static::$attributes)) {
            $value = call_user_func(static::$attributes['get'], $value);
        }

        return $value;
    }

    /**
     * set the value of the entity defined
     * @param $name
     * @param $value
     * @return $this
     */
    public function set($name, $value)
    {
        if (array_key_exists($name, static::$attributes) === false) {
            throw new \InvalidArgumentException(sprintf('%s attribute does not exist', $name));
        }

        if (array_key_exists('set', static::$attributes)) {
            $value = call_user_func(static::$attributes['set'], $value);
        }

        $this->values[$name] = $value;

        return $this;
    }

    /**
     * @param mixed $primaryId
     * @return static | null
     */
    public static function find($primaryId)
    {
        return static::getMapper()->find($primaryId);
    }

    /**
     * the method is for Mapper class to use
     * @internal
     * @param array $values
     * @return $this
     */
    public function pack(array $values)
    {
        $this->values = $values;
        $this->isNew = false;
        return $this;
    }

    /**
     * get entity mapper options and attribute
     * @return array
     */
    public static function getOptions()
    {
        $mapperOptions = static::$mapperOptions;
        $attributes = static::$attributes;

        $calledClass = get_called_class();
        if ($calledClass === __CLASS__) {
            return [$mapperOptions, $attributes];
        }

        /** @var $parentClass static*/
        $parentClass = get_parent_class($calledClass);
        list($parentMapperOptions, $parentAttributes) = $parentClass::getOptions();

        return [
            array_merge($parentMapperOptions, $mapperOptions),
            array_merge($parentAttributes, $attributes)
        ];
    }

    /**
     * get data mapper
     * @return \Etu\ORM\Mapper | \Etu\ORM\Sql\Mapper
     */
    public static function getMapper()
    {
        if (static::$mapper !== null) {
            return static::$mapper;
        }

        return static::$mapper = new static::$mapperName(static::class);
    }
}