<?php

namespace Etu\Service;

use Etu\Container as BaseContainer;
use Etu\Traits\Singleton;

class Container extends BaseContainer
{
    use Singleton;
    public function __construct()
    {
        parent::__construct();
    }
}