<?php

namespace Etu\ORM\Type;

class Integer extends Common
{
    public function restore($value)
    {
        return intval($value);
    }

    public function store($value)
    {
        return intval($value);
    }
}