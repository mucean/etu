<?php

namespace Etu\Service\Sql\Command;

use Etu\Service;

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
    protected $whereConditions = [];

    /**
     * where values
     *
     * @var array
     */
    protected $whereValues = [];

    /**
     * columns that want to where
     *
     * @param $condition string | object
     * @param $values mixed
     * @return $this
     */
    public function where($condition, $values)
    {
        $this->whereConditions[] = (string) $condition;

        if (!is_array($values)) {
            $values = array_slice(func_get_args(), 1);
        }

        $this->whereValues = array_merge($this->whereValues, $values);

        return $this;
    }

    /**
     * sql where in
     *
     * @example
     * $example->whereIn('id', [1, 2, 3]);
     *
     * $select = $db->select('aa');
     * $update = $db->update('bb');
     * $update->whereIn('id', $select->setColumns('a_id')->where('fruit', 'apple'));
     *
     * @param $condition string
     * @param $values array | Select
     * @return $this
     */
    public function whereIn($condition, $values)
    {
        return $this->whereInOrNotIn($condition, $values, 'in');
    }

    /**
     * like $this->whereIn function
     *
     * @param $condition string
     * @param $values array | Select
     * @return $this
     */
    public function whereNotIn($condition, $values)
    {
        return $this->whereInOrNotIn($condition, $values, 'not in');
    }

    protected function whereInOrNotIn($condition, $values, $either = 'in')
    {
        if ($values instanceof Select) {
            $condition = sprintf('%s %s (%s)', $condition, $either, $values->getPrepareSql());
            $values = $values->getParams();
        } else {
            $condition = sprintf('%s %s (%s)', $condition, $either, rtrim(str_repeat('?,', count($values)), ','));
        }

        $this->where($condition, $values);

        return $this;
    }

    /**
     * normalize where columns for PDO prepare
     *
     * @param $conditions string | array
     * @return string
     */
    protected function normalizeWhereColumns($conditions = null)
    {
        if ($conditions === null) {
            $conditions = $this->whereConditions;
        }

        if (!is_array($conditions)) {
            $conditions = [$conditions];
        }

        if ($conditions === []) {
            return '';
        }

        return sprintf('(%s)', implode(') AND (', $conditions));
    }

    /**
     * reset where conditions and values
     *
     * @return void
     */
    protected function resetWhere()
    {
        $this->whereConditions = [];
        $this->whereValues = [];
    }
}
