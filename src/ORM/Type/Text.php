<?php

namespace Etu\ORM\Type;

class Text extends Common
{
    public function restore($value)
    {
        return strval($value);
    }
}