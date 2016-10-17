<?php

namespace Etu\Service\Sql\Command;

use Etu\Service\Sql\Command;

class Delete extends Command
{
    use Where;

    const RESET_SCOPE_WHERE = 'where';

    public function getPrepareSql()
    {
        if ($this->needPrepare === false) {
            return $this->sqlForPrepare;
        }

        $sql = 'DELETE FROM %s%s';

        return $this->sqlForPrepare = sprintf(
            $sql,
            $this->service->quoteIdentifier($this->table),
            $this->normalizeWhereColumns()
        );
    }

    /**
     * execute delete command
     *
     * @param $values array
     * @return int
     */
    public function execute(array $values = null)
    {
        return parent::execute($values)->rowCount();
    }

    public function getParams()
    {
        return $this->whereValues;
    }

    public function nextPrepare()
    {
        $this->needPrepare = false;
    }

    public function reset($scope = self::RESET_SCOPE_ALL)
    {
        parent::reset($scope);

        switch ($scope) {
            case self::RESET_SCOPE_ALL:
            case self::RESET_SCOPE_WHERE:
                $this->resetWhere();
                break;
        }
    }
}