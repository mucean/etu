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

    protected $scope = [
    ];

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
    public function update(array $values = null)
    {
        if ($values === null) {
            $values = $this->values;
            $whereValues = $this->whereValues;
        } else {
            $whereValues = [];
        }

        $sets = [
            'set' => $this->sets,
            'values' => $values
        ];

        $where = [
            'where' => $this->whereColumns,
            'values' => $whereValues
        ];

        return $this->service->update($this->table, $sets, $where);
    }

    /**
     * reset parameters
     *
     * @return void
     */
    public function reset($scope = null)
    {
    }
}
