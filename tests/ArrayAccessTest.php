<?php
namespace Tests;

use Etu\ArrayAccess;

/**
 * Class ArrayAccessTest
 */
class ArrayAccessTest extends \PHPUnit\Framework\TestCase
{
    protected $test;

    protected $original = [
        'hi' => 'hello, world!',
        'animal' => [
            'mammals' => 'tiger'
        ]
    ];

    /**
     * @before
     */
    public function init()
    {
        $this->test = new ArrayAccess($this->original);
    }

    public function testAll()
    {
        $this->assertEquals($this->original, $this->test->all());
    }

    public function testGet()
    {
        $data = $this->test;
        $this->assertEquals($this->original['hi'], $data->get('hi'));
        $this->assertEquals($this->original['animal']['mammals'], $data->get(['animal', 'mammals']));
        $this->assertEquals('null', $data->get('hello', 'null'));
    }

    public function testOffsetGet()
    {
        $data = $this->test;
        $this->assertEquals($this->original['hi'], $data['hi']);
        $this->assertEquals($this->original['animal']['mammals'], $data['animal']['mammals']);
    }

    public function testHas()
    {
        $data = $this->test;
        $this->assertTrue($data->has('hi'));
        $this->assertTrue($data->has('animal'));
        $this->assertTrue($data->has(['animal', 'mammals']));
        $this->assertFalse($data->has('test'));
        $this->assertFalse($data->has(['animal', 'hi']));
    }

    public function testOffsetExists()
    {
        $data = $this->test;
        $this->assertTrue(isset($data['hi']));
        $this->assertTrue(isset($data['animal']));
        $this->assertTrue(isset($data['animal']['mammals']));
        $this->assertFalse(isset($data['test']));
        $this->assertFalse(isset($data['animal']['hi']));
    }

    public function testSet()
    {
        $data = $this->test;
        $tests = [
            'test' => 'no error',
            'animal,fishes' => 'turbot'
        ];

        foreach ($tests as $key => $value) {
            $key = explode(',', $key);
            $data->set($key, $value);
            $this->assertEquals($value, $data->get($key));
        }
    }

    public function testOffsetSet()
    {
        $data = $this->test;
        $data['test'] = 'no error';
        $this->assertEquals('no error', $data->get('test'));
        $this->assertEquals('no error', $data['test']);
        $data['animal']['fishes'] = 'turbot';
        $this->assertEquals('turbot', $data->get(['animal', 'fishes']));
        $this->assertEquals('turbot', $data['animal']['fishes']);
    }

    public function testUnset()
    {
        $data = $this->test;
        $data->unset('hi');
        $this->assertEquals(null, $data->get('hi'));
        $data->unset(['animal', 'mammals']);
        $this->assertEquals(null, $data->get(['animal', 'mammals']));
    }

    public function testOffsetUnset()
    {
        $data = $this->test;
        $this->assertEquals($data['hi'], $data->get('hi'));
        unset($data['hi']);
        $this->assertEquals(null, $data->get('hi'));
        $this->assertEquals(null, $data['hi']);
        $this->assertEquals($data['animal']['mammals'], $data->get(['animal', 'mammals']));
        unset($data['animal']['mammals']);
        $this->assertEquals(null, $data->get(['animal', 'mammals']));
        $this->assertEquals([], $data['animal']);
    }

    public function testCount()
    {
        $this->assertEquals(count($this->original), $this->test->count());
    }

    public function testIterator()
    {
        $iterator = $this->test->getIterator();
        $this->assertInstanceOf('ArrayIterator', $iterator);
        foreach ($iterator as $key => $value) {
            $this->assertEquals($this->original[$key], $value);
        }
    }
}
