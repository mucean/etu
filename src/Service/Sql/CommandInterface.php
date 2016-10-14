<?php

namespace Etu\Service\Sql;

interface CommandInterface
{
    /**
     * get sql command for prepare
     * @return string
     */
    public function getPrepareSql();

    /**
     * get pdo statement executed params
     * @return array
     */
    public function getParams();
}