<?php

namespace Etu\Service;

use Etu\Service;
use PDO;
use InvalidArgumentException;
use Exception;

/**
 * Class Sql
 * @author mucean
 */
class Sql extends Service
{
    protected $handler;

    protected $errorHandler;

    public function __construct(array $config)
    {
        if (!array_key_exists('dsn', $config)) {
            throw new InvalidArgumentException('relational database config need `dsn` key');
        }

        parent::__construct($config);
    }

    public function setErrorHandler(callable $handler)
    {
        ;
    }

    public function connect()
    {
        if ($this->handler instanceof PDO) {
            return $this->handler;
        }

        $dsn = $this->getConfig('dsn');
        $user = $this->getConfig('user');
        $password = $this->getConfig('password');
        $options = $this->getConfig('options', []);

        try {
            $db = new PDO($dsn, $user, $password, $options);
        } catch (Exception $e) {
            $this->errorHandler($e);
            throw new Exception('connect to database failed');
        }

        return $this->handler = $db;
    }
}
