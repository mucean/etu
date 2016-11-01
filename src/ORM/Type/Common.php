<?php

namespace Etu\ORM\Type;

class Common
{
    public function store($value)
    {
        return $value;
    }

    public function restore($value)
    {
        return $value;
    }
}