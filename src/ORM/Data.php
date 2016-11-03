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
     * last values of modified attributes
     * @var array
     */
    protected $lastModifiedAttributes = [];

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
        $value = $this->getValue(static::$attributes, $name);

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
        $this->modifiedAttributes[] = $name;

        return $this;
    }

    /**
     * save date
     * @return bool
     */
    public function save()
    {
        return static::$mapper->save($this);
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
     * entity is new created or not
     * @return bool
     */
    public function isNew()
    {
        return $this->isNew;
    }

    /**
     * entity is modified or not
     * @return bool
     */
    public function isModified()
    {
        return !empty($this->modifiedAttributes);
    }

    /**
     * get modified attributes
     * @param string $name
     * @return array|mixed
     */
    public function getModifiedAttributes($name = null)
    {
        if ($name === null) {
            return $this->modifiedAttributes;
        }

        return $this->getValue($this->modifiedAttributes, $name);
    }

    /**
     * get last modified attributes
     * @param null $name
     * @return array|mixed
     */
    public function getLastModifiedAttributes($name = null)
    {
        if ($name === null) {
            return $this->lastModifiedAttributes;
        }

        return $this->getValue($this->lastModifiedAttributes, $name);
    }

    /**
     * get value in the array via key name
     * @param array $attributes
     * @param $name
     * @return mixed
     */
    protected function getValue(array &$attributes, $name)
    {
        if (array_key_exists($name, $attributes) === false) {
            throw new \InvalidArgumentException(sprintf('%s attribute does not exist', $name));
        }

        return $attributes[$name];
    }

    /**
     * the method is for Mapper class to use
     * @internal
     * @param array $values
     * @param bool $update
     * @return $this
     */
    public function __pack(array $values, $update = false)
    {
        if ($update) {
            $values = array_merge($this->values, $values);
        }

        $this->values = $values;
        $this->isNew = false;
        $this->modifiedAttributes = [];
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