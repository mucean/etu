<?php

namespace Etu\ORM\Type;

class Number extends Common
{
    public function store($value)
    {
        return $value * 1;
    }

    public function restore($value)
    {
        return $value * 1;
    }
}