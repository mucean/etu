<?php

namespace Tests\Http;

use Etu\Http\Headers;

/**
 * Class HeadersTest
 */
class HeadersTest extends \PHPUnit_Framework_TestCase
{
    protected $headers;

    protected $context;

    /**
     * @before
     */
    public function buildHeader()
    {
        $context = BuildContext::getContext();
        $this->headers = Headers::buildFromContext($context);
        $this->context = $context;
    }

    public function testConstruct()
    {
        $data = ['hello' => 'hello, world!'];
        $headers = new Headers($data);
        $this->assertInstanceOf('Etu\\Http\\Headers', $headers);
        $this->assertEquals([$data['hello']], $headers->get('hello'));
    }

    public function testGetHeaderName()
    {
        $name = 'HTTP_HOST';
        $this->assertEquals('host', $this->headers->getHeaderName($name));
    }

    public function testBuildFromContext()
    {
        $this->assertInstanceOf('Etu\\Http\\Headers', $this->headers);
    }

    public function testAll()
    {
        $data = [];
        foreach ($this->context as $name => $value) {
            if (strpos($name, 'HTTP_') === 0) {
                $data[$name] = [$value];
            }
        }

        $this->assertEquals($data, $this->headers->all());
    }

    public function testGet()
    {
        $name = 'HTTP_HOST';
        $this->assertEquals(
            [$this->context->get($name)],
            $this->headers->get($name)
        );
        $this->assertEquals([], $this->headers->get('hi'));
    }

    public function testHas()
    {
        $name = 'HTTP_HOST';
        $this->assertTrue($this->headers->has($name));
        $this->assertFalse($this->headers->has('hello'));
    }

    public function testSet()
    {
        $name = 'hello';
        $value = 'hello, world!';
        $this->assertFalse($this->headers->has('hello'));
        $this->headers->set($name, $value);
        $this->assertTrue($this->headers->has($name));
        $this->assertEquals([$value], $this->headers->get($name));
    }

    public function testUnset()
    {
        $name = 'HTTP_HOST';
        $this->assertTrue($this->headers->has($name));
        $this->headers->unset($name);
        $this->assertFalse($this->headers->has($name));
    }

    public function testGetIterator()
    {
        $data = [];
        foreach ($this->context as $name => $value) {
            if (strpos($name, 'HTTP_') === 0) {
                $data[$name] = [$value];
            }
        }

        foreach ($this->headers as $name => $value) {
            $this->assertEquals($data[$name], $value);
        }
    }
}
