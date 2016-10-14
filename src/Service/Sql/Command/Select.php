<?php

namespace Etu\Service\Sql\Command;

use Etu\Service\Sql\CommandInterface;

class Select implements CommandInterface
{
    use Where;

    public function getPrepareSql()
    {
        // TODO: Implement getPrepareSql() method.
    }

    public function getParams()
    {
        // TODO: Implement getParams() method.
    }
}