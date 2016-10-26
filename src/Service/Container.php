<?php

namespace Etu\Service;

use Etu\Container as BaseContainer;
use Etu\Traits\Singleton;

class Container extends BaseContainer
{
    use Singleton;

    protected function __construct(array $items)
    {
        parent::__construct($items);
    }
}