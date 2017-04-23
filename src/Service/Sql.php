<?php

namespace Etu\Service;

use Etu\Service;
use Etu\Service\Exception as ServiceException;
use InvalidArgumentException;
use Exception;
use PDOStatement;
use PDO;

/**
 * Class Sql
 * @author mucean
 */
class Sql extends Service
{
    /**
     * PDO instance
     * @var PDO
     */
    protected $handler;

    /**
     * callable error handler
     *
     * @var callable
     */
    protected $errorHandler;

    /**
     * identifier quote string
     *
     * @var string
     */
    protected $quoteSymbol = '`';

    protected $defaultOption = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];

    public function __construct(array $config)
    {
        if (!array_key_exists('dsn', $config)) {
            throw new InvalidArgumentException('relational database config need `dsn` key');
        }

        parent::__construct($config);
    }

    public function __call($method, $arguments)
    {
        $handler = $this->connect();
        return $arguments === []
            ? $handler->$method()
            : call_user_func_array([$handler, $method], $arguments);
    }

    /**
     * @return PDO
     */
    public function getPDO()
    {
        return $this->connect();
    }

    public function select($table)
    {
        return new Sql\Command\Select($this, $table);
    }

    /**
     * update database
     *
     * @param string $table
     * @return Sql\Command\Update
     */
    public function update($table)
    {
        return new Sql\Command\Update($this, $table);
    }

    /**
     * insert command
     * @param $table
     * @return Sql\Command\Insert
     */
    public function insert($table)
    {
        return new Sql\Command\Insert($this, $table);
    }

    /**
     * delete command
     * @param $table
     * @return Sql\Command\Delete
     */
    public function delete($table)
    {
        return new Sql\Command\Delete($this, $table);
    }

    /**
     * execute a sql
     *
     * @param string | PDOStatement $sql
     * @param $parameters array
     * @throws Exception
     * @return PDOStatement
     */
    public function execute($sql, array $parameters = [])
    {
        $statement = $sql;
        if (!$statement instanceof PDOStatement) {
            $statement = $this->connect()->prepare($sql);
        }

        if (!$statement->execute($parameters)) {
            throw new Exception('execute sql failed');
        }

        return $statement;
    }

    /**
     * quote sql identifier
     *
     * @param string | array $identifier
     * @return string
     */
    public function quoteIdentifier($identifier)
    {
        if (is_array($identifier)) {
            return array_map([$this, 'quoteIdentifier'], $identifier);
        }

        $identifier = str_replace(['\'', '"', ';'], '', $identifier);

        $items = explode('.', $identifier);

        $quoteSymbol = $this->quoteSymbol;

        array_walk($items, function (&$item) use ($quoteSymbol) {
            $item = sprintf('%s%s%s', $quoteSymbol, $item, $quoteSymbol);
        });

        return implode('.', $items);
    }

    /**
     * connect service
     *
     * @return PDO
     * @throws ServiceException
     */
    public function connect()
    {
        if ($this->isConnected()) {
            return $this->handler;
        }

        $dsn = $this->getConfig('dsn');
        $user = $this->getConfig('user');
        $password = $this->getConfig('password');
        $options = $this->defaultOption;
        foreach ($this->getConfig('options', []) as $key => $value) {
            $options[$key] = $value;
        }

        try {
            $db = new PDO($dsn, $user, $password, $options);
        } catch (Exception $e) {
            $this->handleError($e);
            throw new ServiceException($e->getMessage());
        }

        return $this->handler = $db;
    }

    /**
     * pdo is connected
     *
     * @return bool
     */
    public function isConnected()
    {
        return $this->handler instanceof PDO;
    }

    /**
     * set handle pdo connect failed exception
     *
     * @param callable $handler
     */
    public function setErrorHandler(callable $handler)
    {
        $this->errorHandler = $handler;
    }

    /**
     * handle pdo connect failed exception
     *
     * @param Exception $e
     */
    protected function handleError(Exception $e)
    {
        if (is_callable($this->errorHandler)) {
            call_user_func($this->errorHandler, $e);
        }
    }
}
