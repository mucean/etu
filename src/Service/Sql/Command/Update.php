<?php

namespace Etu\Service\Sql\Command;

use Etu\Service\Sql\Command;

/**
 * sql update command
 * @author mucean
 */
class Update extends Command
{
    use Where;

    /**
     * update set columns
     *
     * @var array
     */
    protected $sets = [];

    /**
     * set prepare values
     *
     * @var array
     */
    protected $values = [];

    const RESET_SCOPE_WHERE = 'where';
    const RESET_SCOPE_SET = 'set';

    /**
     * columns that want to update
     *
     * @param $column string
     * @param $values mixed
     * @return Update
     */
    public function set($column, $values)
    {
        $this->needToPrepare();

        $this->sets[] = (string) $column;

        if (!is_array($values)) {
            $values = array_slice(func_get_args(), 1);
        }

        $this->values = array_merge($this->values, $values);

        return $this;
    }

    /**
     * execute update command
     *
     * @param $values array
     * @return int
     */
    public function execute(array $values = null)
    {
        return parent::execute($values)->rowCount();
    }

    /**
     * reset parameters
     *
     * @param $scope string
     * @return void
     */
    public function reset($scope = self::RESET_SCOPE_ALL)
    {
        parent::reset($scope);

        switch ($scope) {
            case self::RESET_SCOPE_SET:
                $this->sets = [];
                $this->values = [];
                break;
            case self::RESET_SCOPE_WHERE:
                $this->resetWhere();
                break;
            case self::RESET_SCOPE_ALL:
                $this->resetWhere();
                $this->sets = [];
                $this->values = [];
                break;
        }
    }

    /**
     * get sql for prepared
     * @param null $sets
     * @param null $where
     * @param null $table
     * @return string
     */
    public function getPrepareSql($sets = null, $where = null, $table = null)
    {
        if ($this->needPrepare === false) {
            return $this->sqlForPrepare;
        }

        if ($table === null) {
            $table = $this->table;
        }

        if ($sets === null) {
            $sets = $this->sets;
        }

        if (is_array($sets)) {
            $sets = implode(',', $sets);
        }

        $where = $this->normalizeWhereColumns($where);

        $sql = sprintf(
            'UPDATE %s SET %s%s',
            $this->service->quoteIdentifier($table),
            $sets,
            (string) $where
        );

        return $this->sqlForPrepare = $sql;
    }

    /**
     * get params of PDOStatement's method execute
     * @return array
     */
    public function getParams()
    {
        return array_merge($this->values, $this->whereValues);
    }

    public function nextPrepare()
    {
        $this->needPrepare = false;
    }
}
