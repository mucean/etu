<?php

namespace Etu\Service\Sql;

use Etu\Service\Sql;
use PDO;

/**
 * Class Mysql
 * @author mucean
 */
class Mysql extends Sql
{
    public function useBufferQuery()
    {
        return $this->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    }

    public function nonuseBufferQuery()
    {
        return $this->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
    }
}
