<?php

namespace Etu\Service\Sql\Command;

use Etu\Service\Sql\Command;

class Select extends Command
{
    use Where;

    /**
     * sql table columns
     * @var array
     */
    protected $columns = [];

    /**
     * order by command content
     * @var array
     */
    protected $orderByPart;

    /**
     * group by command content
     * @var array
     */
    protected $groupByPart;

    /**
     * select sql offset number
     * @var integer
     */
    protected $offsetNumber;

    /**
     * select sql limit number
     * @var integer
     */
    protected $limitNumber;

    /**
     * set select columns
     * @param $columns mixed
     * @return $this
     */
    public function setColumns($columns)
    {
        $this->needToPrepare();

        if (is_array($columns) === false) {
            $columns = array_slice(func_get_args(), 1);
        }

        $this->columns = array_merge($this->columns, $columns);

        return $this;
    }

    public function get($limit = null, array $values = null)
    {
        if ($limit !== null) {
            $this->limit($limit);
        }

        return $this->execute($values);
    }

    /**
     *
     * @param $columns
     * @param null $having
     * @param null $havingParams
     * @return $this
     */
    public function groupBy($columns, $having = null, $havingParams = null)
    {
        $this->groupByPart = [$columns];

        if ($having !== null) {
            $this->groupByPart[] = $having;

            if (is_array($havingParams) === false) {
                $havingParams = array_slice(func_get_args(), 2);
            }

            $this->groupByPart[] = $havingParams;
        }

        return $this;
    }

    /**
     * set order by command content
     *
     * @example
     * $select->orderBy('id desc', 'item asc', 'time');
     * $select->orderBy(['id desc', 'item asc', 'time']);
     * $select->orderBy(['id' => 'desc', 'item' => 'asc', 'time']);
     * @param $content
     * @return $this
     */
    public function orderBy($content)
    {
        if (is_array($content) === false) {
            $content = func_get_args();
        }

        $this->orderByPart = [];

        foreach ($content as $key => $value) {
            if (is_numeric($key)) {
                $this->orderByPart[] = $value;
            } else {
                if (strtolower($value) === 'desc') {
                    $value = sprintf('%s %s', $this->service->quoteIdentifier($key), $value);
                }

                $this->orderByPart[] = $value;
            }
        }

        return $this;
    }

    /**
     * set select command offset number
     * @param $offset
     */
    public function offset($offset)
    {
        $this->offsetNumber = $offset;
    }

    /**
     * set sql limit number
     * @param $limitNumber integer
     */
    public function limit($limitNumber)
    {
        $this->limitNumber = (int) $limitNumber;
    }

    public function getPrepareSql()
    {
        if ($this->needPrepare === false) {
            return $this->sqlForPrepare;
        }

        $sql = 'SELECT %s FROM %s';

        $sql = sprintf(
            $sql,
            $this->columns === [] ? '*' : implode(' ', $this->service->quoteIdentifier($this->columns)),
            $this->service->quoteIdentifier($this->table)
        );

        $where = $this->normalizeWhereColumns();

        if ($where) {
            $sql = $sql . $where;
        }

        if ($this->groupByPart !== null) {
            $groupByPart = sprintf(' GROUP BY %s', $this->service->quoteIdentifier($this->groupByPart[0]));

            if (array_key_exists(1, $this->groupByPart)) {
                $groupByPart .= ' ' . $this->groupByPart[1];
            }

            $sql .= ' ' . $groupByPart;
        }

        if ($this->orderByPart !== null) {
            $sql .= sprintf(' ORDER BY %s', implode(' ', $this->orderByPart));
        }

        if ($this->offsetNumber !== null) {
            $sql .= sprintf(' OFFSET %d', $this->offsetNumber);
        }

        if ($this->limitNumber !== null) {
            $sql .= sprintf(' LIMIT %d', $this->limitNumber);
        }

        return $this->sqlForPrepare = $sql;
    }

    public function getParams()
    {
        $values = $this->whereValues;

        if ($this->groupByPart !== null && array_key_exists(2, $this->groupByPart)) {
            $values = array_merge($values, $this->groupByPart[2]);
        }

        return $values;
    }

    public function reset()
    {
        // TODO code this
    }
}
