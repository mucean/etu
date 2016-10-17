<?php

namespace Etu\Service\Sql\Command;

use Etu\Service\Sql\Command;

class Insert extends Command
{
    /**
     * insert command columns
     * @var array | null
     */
    protected $columns = [];

    /**
     * insert command values
     * @var array
     */
    protected $values = [];

    const RESET_SCOPE_COLUMNS = 'columns';
    const RESET_SCOPE_VALUES = 'values';

    /**
     * columns of insert command need to set
     * @param $columns
     * @return $this
     * @example
     * $insert->setColumns(['aa', 'bb']);
     * $insert->serColumns([['aa', 'bb'], ['cc', 'dd']]);
     */
    public function setColumns($columns)
    {
        if (is_array($columns) === false) {
            $columns = func_get_args();
        }

        $this->columns = array_merge($this->columns, $columns);

        return $this;
    }

    /**
     * values of insert command need to insert
     * @param array $values
     * @return $this
     */
    public function setValues(array $values)
    {
        if (array_key_exists(0, $values) === false) {
            return $this;
        }

        if (is_array($values[0])) {
            $this->values = array_merge($this->values, $values);
        } else {
            $this->values[] = $values;
        }

        return $this;
    }

    /**
     * execute insert command
     *
     * @param $values array
     * @return int
     */
    public function execute(array $values = null)
    {
        return parent::execute($values)->rowCount();
    }

    public function getPrepareSql()
    {
        $sql = 'INSERT INTO %s%s%s';

        $columns = '';
        if ($this->columns !== []) {
            $columns = sprintf(' (%s)', implode(',', $this->columns));
        }

        $values = '';
        if ($this->values !== []) {
            $values = sprintf('(%s)', rtrim(str_repeat('?,', count($this->columns)), ','));

            $values = sprintf(' VALUES %s', rtrim(str_repeat($values . ',', count($this->values)), ','));
        }

        return sprintf($sql, $this->service->quoteIdentifier($this->table), $columns, $values);
    }

    public function getParams()
    {
        $values = [];
        if ($this->values !== []) {
            foreach ($this->values as $value) {
                $values = array_merge($values, $value);
            }
        }

        return $values;
    }

    public function reset($scope = self::RESET_SCOPE_ALL)
    {
        parent::reset($scope);

        switch ($scope) {
            case self::RESET_SCOPE_COLUMNS:
                $this->columns = [];
                break;
            case self::RESET_SCOPE_VALUES:
                $this->values = [];
                break;
            case self::RESET_SCOPE_ALL:
                $this->columns = [];
                $this->values = [];
                break;
        }
    }
}