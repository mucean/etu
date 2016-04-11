<?php

namespace Tests;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function testAa()
    {
        $aa = new \Etu\Application;
        $this->assertEquals(1, $aa->start());
    }
}
