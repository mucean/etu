<?php

namespace Etu\Service\Sql;

use Etu\Service\Sql;

abstract class Command
{
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
     * is need to prepare sql
     *
     * @var bool
     */
    protected $needPrepare = true;

    protected $sqlForPrepare;

    const RESET_SCOPE_ALL = 'all';

    public function __construct(Sql $service, $table)
    {
        $this->service = $service;
        $this->table = (string) $table;
    }

    /**
     * PDO prepared statement
     *
     * @var \PDOStatement
     */
    protected $statement;

    /**
     * execute sql command
     *
     * @param $values array
     * @return \PDOStatement
     */
    public function execute(array $values = null)
    {
        if ($this->needPrepare === true) {
            $this->prepare();
            $this->nextPrepare();
        }

        if ($values === null) {
            $values = $this->getParams();
        }

        $this->statement->execute($values);

        return $this->statement;
    }

    /**
     * prepare sql command
     * @return \PDOStatement
     */
    public function prepare()
    {
        $sql = $this->getPrepareSql();
        $this->statement = $this->service->connect()->prepare($sql);

        return $this->statement;
    }

    /**
     * get prepare statement
     *
     * @return \PDOStatement
     */
    public function getStatement()
    {
        if ($this->statement === null) {
            $this->prepare();
        }

        return $this->statement;
    }

    /**
     * set the trigger of need prepare is true
     */
    public function needToPrepare()
    {
        if ($this->needPrepare === false) {
            $this->needPrepare = true;
        }
    }

    /**
     * execute this after execute prepare
     */
    protected function nextPrepare()
    {
    }

    /**
     * reset params
     * @param string $scope
     */
    protected function reset($scope = self::RESET_SCOPE_ALL)
    {
        if (is_array($scope)) {
            array_map([$this, 'reset'], $scope);
        }
        $this->needToPrepare();
    }

    /**
     * get sql command for prepare
     * @return string
     */
    abstract public function getPrepareSql();

    /**
     * get pdo statement executed params
     * @return array
     */
    abstract public function getParams();
}