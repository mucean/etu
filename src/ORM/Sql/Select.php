<?php

namespace Etu\ORM\Sql;

use Etu\Service\Sql\Command\Select as BaseSelect;

class Select extends BaseSelect
{
    protected $wrapper;

    public function setWrapper(callable $wrapper)
    {
        $this->wrapper = $wrapper;
    }

    /**
     * if callable wrapper is set, the array result of wrapper of each values
     * will return, otherwise the array result of each values return
     * @param null $limit
     * @param array|null $values
     * @return array
     */
    public function get($limit = null, array $values = null)
    {
        $statement = parent::get($limit, $values);
        $data = [];
        if (is_callable($this->wrapper)) {
            while ($item = $statement->fetch()) {
                $data[] = call_user_func($this->wrapper, $item);
            }
        } else {
            while ($item = $statement->fetch()) {
                $data[] = $item;
            }
        }

        return $data;
    }
}