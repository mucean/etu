<?php

namespace Tests\Service;

use Etu\Service\Sql\Sql;

/**
 * Class SqlTest
 * @author mucean <mocean.liu@gmail.com>
 */
class SqlTest extends \PHPUnit_Framework_TestCase
{
    protected $db;

    public function __construct()
    {
        $this->db = new Sql([
            'dsn' => 'mysql:host=127.0.0.1;dbname=lottery',
            'user' => 'root',
            'password' => 'omymysql'
        ]);
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(Sql::class, $this->db);
    }

    public function testConnect()
    {
        $this->assertInstanceOf(\PDO::class, $this->db->connect());
    }
}
