<?php

namespace Tests;

use Tests\Instance\ServiceInstance;

class ServiceTest extends \PHPUnit_Framework_TestCase
{
    protected $item;

    protected $config = [
        'hi' => 'hello, world!'
    ];

    public function __construct()
    {
        $this->item = new ServiceInstance($this->config);
    }

    public function testGetConfig()
    {
        $this->assertEquals($this->item->getConfig(), $this->config);
        $this->assertEquals($this->item->getConfig('hi'), $this->config['hi']);
        $this->assertEquals($this->item->getConfig('big'), null);
        $this->assertEquals($this->item->getConfig('big', 'aa'), 'aa');
    }
}