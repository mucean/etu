<?php

namespace Etu\ORM;

abstract class Data
{
    protected static $mapperName = '\Etu\ORM\Mapper';

    protected static $mapperOptions = [
        'service' => '',
        'connection' => ''
    ];

    /**
     * @var \Etu\ORM\Mapper | \Etu\ORM\Sql\Mapper
     */
    protected static $mapper;

    /**
     * @example
     * [
     *     'id' => [
     *         'type' => 'integer',             // enum: integer, string, number, json, time
     *         'primaryKey' => true,
     *         'default' => 0,
     *         'autoIncrement' => true,
     *         'allowNull' => true,
     *         'set' => function ($value) {return $value},
     *         'get' => function ($value) {return $value}
     *     ],
     *     ...
     * ]
     * @var array
     */
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
     * @throws \InvalidArgumentException
     */
    public function get($name)
    {
        if (array_key_exists($name, $this->values)) {
            $value = $this->values[$name];
        } else {
            if (array_key_exists($name, static::$attributes) === false) {
                throw new \InvalidArgumentException(sprintf('%s attribute does not exist', $name));
            }

            if (array_key_exists('default', static::$attributes[$name]) === false) {
                throw new \InvalidArgumentException(sprintf('%s attribute does not default value'));
            }

            $value = static::$attributes[$name]['default'];
        }

        if (isset(static::$attributes[$name]['get'])) {
            $value = call_user_func(static::$attributes[$name]['get'], $value);
        }

        return $value;
    }

    /**
     * set the value of the entity defined
     * @param $name
     * @param $value
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function set($name, $value)
    {
        if (array_key_exists($name, static::$attributes) === false) {
            throw new \InvalidArgumentException(sprintf('%s attribute does not exist', $name));
        }

        if (isset(static::$attributes[$name]['set'])) {
            $value = call_user_func(static::$attributes[$name]['set'], $value);
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
        $result = true;

        if ($this->isNew()) {
            $this->__beforeSave();
            $this->__beforeInsert();
            $result = static::getMapper()->insert($this);
            $this->__afterInsert();
            $this->__afterSave();
        } elseif ($this->isModified()) {
            $this->__beforeSave();
            $this->__beforeUpdate();
            $result = static::getMapper()->update($this);
            $this->__afterUpdate();
            $this->__afterSave();
        }

        return $result;
    }

    /**
     * delete entity from service
     * @return bool
     */
    public function delete()
    {
        $this->__beforeDelete();
        $result = static::getMapper()->delete($this);
        $this->__afterDelete();

        return $result;
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
     * @param string $name
     * @return bool
     */
    public function isModified($name = null)
    {
        if ($name === null) {
            return !empty($this->modifiedAttributes);
        }

        return in_array($name, $this->modifiedAttributes);
    }

    /**
     * entity last is modified or not
     * @param string $name
     * @return bool
     */
    public function isLastModified($name = null)
    {
        if ($name === null) {
            return !empty($this->lastModifiedAttributes);
        }

        return in_array($name, $this->lastModifiedAttributes);
    }

    /**
     * get modified attributes
     * @return array
     */
    public function getModifiedAttributes()
    {
        return $this->modifiedAttributes;
    }

    /**
     * get last modified attributes
     * @return array
     */
    public function getLastModifiedAttributes()
    {
        return $this->lastModifiedAttributes;
    }

    protected function __beforeUpdate() {}
    protected function __afterUpdate() {}
    protected function __beforeInsert() {}
    protected function __afterInsert() {}
    protected function __beforeDelete() {}
    protected function __afterDelete() {}
    protected function __beforeSave() {}
    protected function __afterSave() {}

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
        $this->lastModifiedAttributes = $this->modifiedAttributes;
        $this->modifiedAttributes = [];
        return $this;
    }

    public function pick(array $attributes = [])
    {
        $values = [];
        if (count($attributes) > 0) {
            foreach ($attributes as $name) {
                if (array_key_exists($name, static::$attributes) === false || array_key_exists($name, $this->values) === false) {
                    continue;
                }
                $values[$name] = Type::factory(static::$mapper->getAttributeType($name))
                    ->store($this->values[$name]);
            }
        } else {
            foreach ($this->values as $key => $value) {
                if (array_key_exists($key, static::$attributes)) {
                    $values[$key] = Type::factory(static::$mapper->getAttributeType($key))->store($value);
                }
            }
        }

        return $values;
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