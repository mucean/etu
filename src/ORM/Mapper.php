<?php

namespace Etu\ORM;

/**
 * Class Mapper
 * @author mucean
 */
abstract class Mapper
{
    abstract public function find($primaryId);

    abstract public function getService();
}