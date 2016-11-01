<?php

namespace Etu\ORM;

abstract class Data
{
    protected static $mapperName = '\Etu\ORM\Mapper';

    protected static $mapperOptions = [
    ];

    protected static $mapper;

    protected static $attributes = [];

    /**
     *
     * @var array
     */
    protected $values;

    public function pack(array $values)
    {
        $this->values = $values;
        return $this;
    }

    /**
     * @param mixed $primaryId
     *
     * @return static
     */
    public static function find($primaryId)
    {
        $data = static::getMapper()->find($primaryId);
        return (new static())->pack($data);
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
