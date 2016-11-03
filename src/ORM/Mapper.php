<?php

namespace Etu\ORM;

/**
 * Class Mapper
 * @author mucean
 */
abstract class Mapper
{
    abstract public function getService();

    abstract protected function doFind($primaryValues);

    /**
     * @param $primaryValues
     * @return \Etu\ORM\Data | null
     */
    public function find($primaryValues)
    {
        return $this->doFind($primaryValues);
    }
}