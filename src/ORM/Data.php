<?php

namespace Etu\ORM;

abstract class Data
{
    protected static $mapperName = '\Etu\ORM\Mapper';

    protected static $mapperOptions = [
    ];

    protected static $mapper;

    protected $attribute = [];

    /**
     *
     * @var array
     */
    protected $values;

    protected function pack(array $values)
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
     * @return \Etu\ORM\Mapper
     */
    public static function getMapper()
    {
        if (static::$mapper !== null) {
            return static::$mapper;
        }

        return static::$mapper = new static::$mapperName(static::$mapperOptions);
    }
}
