<?php

namespace Etu\ORM\Sql;

use \Etu\ORM\Data as BaseData;

class Data extends BaseData
{
    protected static $mapperName = '\Etu\ORM\Sql\Mapper';

    public static function select()
    {
        return static::getMapper()->select();
    }
}