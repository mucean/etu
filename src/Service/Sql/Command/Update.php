<?php

namespace Etu\Service\Sql\Command;

use Etu\Service\Sql\Sql;

/**
 * sql update command
 * @author mucean
 */
class Update
{
    use Where;

    /**
     * sql database service
     *
     * @var Sql
     */
    protected $service;

    /**
     * database table name
     *
     * @var string
     */
    protected $table;

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

    /**
     * is need to prepare sql
     *
     * @var bool
     */
    protected $needPrepare = true;

    /**
     * PDO prepared statement
     *
     * @var \PDOStatement
     */
    protected $statement;

    const RESET_SCOPE_ALL = 'all';
    const RESET_SCOPE_SET = 'set';
    const RESET_SCOPE_WHERE = 'where';

    public function __construct(Sql $service, $table)
    {
        $this->service = $service;
        $this->table = (string) $table;
    }

    /**
     * columns that want to update
     *
     * @return Update
     */
    public function set($column, $values)
    {
        if ($this->needPrepare === false) {
            $this->needPrepare = true;
        }

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
     * @return int
     */
    public function execute(array $values = null)
    {
        if ($this->needPrepare === true) {
            $this->prepare();
        }

        if ($values === null) {
            $values = array_merge($this->values, $this->whereValues);
        }

        //return $this->service->update($this->table, $sets, $where);
        if (!$this->statement->execute($values)) {
            return false;
        }
        return $this->statement->rowCount();
    }

    /**
     * prepare update sql
     *
     * @return PDOStatement
     */
    public function prepare($sets = null, $where = null, $table = null)
    {
        if ($table === null) {
            $table = $this->table;
        }

        if ($sets === null) {
            $sets = $this->sets;
        }

        if (is_array($sets)) {
            $sets = implode(',', $sets);
        }

        if ($where === null) {
            $where = $this->whereColumns;
        }

        if (is_array($where)) {
            $where = $this->normalizeWhereColumns($where);
        }

        if ($where) {
            $where = sprintf(' WHERE %s', $where);
        }

        $sql = sprintf('UPDATE %s SET %s%s', $table, $sets, (string) $where);

        $this->statement = $this->service->connect()->prepare($sql);

        return $this;
    }

    /**
     * get prepare statement
     *
     * @return \PDOStatement | null
     */
    public function getStatement()
    {
        return $this->statement;
    }

    /**
     * reset parameters
     *
     * @return void
     */
    public function reset($scope = self::RESET_SCOPE_ALL)
    {
        $this->needPrepare = true;
        switch ($scope) {
            case self::RESET_SCOPE_SET:
                $this->sets = [];
                $this->values = [];
                break;
            case self::RESET_SCOPE_WHERE:
                $this->whereColumns = [];
                $this->whereValues = [];
                break;
            default:
                $this->whereColumns = [];
                $this->whereValues = [];
                $this->sets = [];
                $this->values = [];
                break;
        }
    }
}
