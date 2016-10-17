<?php

namespace Etu\Service\Sql\Command;

use Etu\Service\Sql\Command;

class Insert extends Command
{
    protected $columns = [];

    protected $values = [];

    public function prepare()
    {
    }

    public function getPrepareSql()
    {
        // TODO: Implement getPrepareSql() method.
    }

    public function getParams()
    {
        // TODO: Implement getParams() method.
    }
}