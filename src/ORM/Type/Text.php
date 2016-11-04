<?php

namespace Etu\ORM\Type;

class Text extends Common
{
    public function restore($value)
    {
        return strval($value);
    }

    public function store($value)
    {
        return strval($value);
    }
}