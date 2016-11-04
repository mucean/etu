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
        return $this->handler->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    }

    public function nonUseBufferQuery()
    {
        return $this->handler->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
    }

    public function lastInsertId()
    {
        $statement = $this->connect()->prepare('SELECT last_insert_id()');
        $statement->execute();
        return $statement->fetchColumn();
    }
}
