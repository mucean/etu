<?php

namespace Etu\Service\Sql\Command;

/**
 * Trait Where
 * @author mucean
 */
trait Where
{
    /**
     * where columns
     *
     * @var array
     */
    protected $whereColumns = [];

    /**
     * where values
     *
     * @var array
     */
    protected $whereValues = [];

    /**
     * columns that want to where
     *
     * @return $this
     */
    public function where($column, $values)
    {
        $this->whereColumns[] = (string) $column;

        if (!is_array($values)) {
            $values = array_slice(func_get_args(), 1);
        }

        $this->whereValues = array_merge($this->whereValues, $values);

        return $this;
    }

    /**
     * sql where in
     *
     * @return $this
     */
    public function whereIn($column, $values)
    {
        return $this;
    }
    

    /**
     * normalize where columns for PDO prepare
     *
     * @return string
     */
    protected function normalizeWhereColumns(array $columns)
    {
        if ($columns === []) {
            return '';
        }

        return sprintf('(%s)', implode(') AND (', $columns));
    }
}
