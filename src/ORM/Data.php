<?php

namespace Etu\ORM;

abstract class Data
{
    protected static $mapper = '\Etu\ORM\Mapper';

    protected static $mapperConfig = [
        'service' => ''
    ];

    public static function getConfig()
    {
        ;
    }

    /**
     * @param mixed $primaryId
     *
     * @return static
     */
    public static function find($primaryId)
    {
        return new static();
    }
}