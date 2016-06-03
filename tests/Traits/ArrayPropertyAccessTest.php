<?php
namespace tests\Traits;

class ArrayPropertyAccessTest extends \PHPUnit_Framework_TestCase
{
    use \Etu\Traits\ArrayPropertyAllAccess;

    protected $readArray = ['abc' => ['acb' => ['aaa', 'bbb'], 'dfg' => 'cdd']];
    protected $writeArray = ['abc' => ['acb' => ['aaa', 'bbb'], 'dfg' => 'cdd']];
    protected $notArray = 'hi';

    public function __construct()
    {
        $this->registerPropertyAccess('readArray');
        $this->registerPropertyAccess('writeArray', true);
    }

    public function testRegisterPropertyAccess()
    {
        $this->assertEquals($this->accessProperties['readArray'], false);
        $this->assertEquals($this->accessProperties['writeArray'], true);
        $this->setExpectedException('InvalidArgumentException', 'the property must be existed and an array type');
        $this->registerPropertyAccess('error');
    }

    public function testRegisterPropertyAccessException()
    {
        $this->setExpectedException('InvalidArgumentException', 'the property must be existed and an array type');
        $this->registerPropertyAccess('notArray');
    }

    public function testGet()
    {
        $this->assertEquals($this->get('readArray', []), $this->readArray);
        $this->assertEquals($this->get('readArray', ['abc', 'dfg']), 'cdd');
        $this->assertEquals($this->get('readArray', ['abc', 'acb']), ['aaa', 'bbb']);
        $this->assertEquals($this->get('readArray', ['abc', 'acb', 1]), 'bbb');
        $this->assertEquals($this->get('readArray', ['abc', 'acb', 2]), null);
        $this->assertEquals($this->get('readArray', ['abc', 'acb', 2], 'hi'), 'hi');
        $this->assertEquals($this->get('abc', ['abc', 'acb', 2]), null);
        $this->assertEquals($this->get('readArray', []), $this->writeArray);
    }

    public function testHas()
    {
        $this->assertTrue($this->has('readArray', []));
        $this->assertTrue($this->has('readArray', ['abc', 'dfg']));
        $this->assertTrue($this->has('readArray', ['abc', 'acb', 1]));
        $this->assertFalse($this->has('readArray', ['abc', 'acb', 2]));
    }

    public function testSet()
    {
        $accessPath = ['aaa', 'ccc'];
        $this->set('writeArray', $accessPath, 'bbb');
        $this->assertEquals($this->get('writeArray', $accessPath), 'bbb');
        $this->set('writeArray', $accessPath, 'ddd');
        $this->assertEquals($this->get('writeArray', $accessPath), 'ddd');
        $this->setExpectedException('InvalidArgumentException', 'hello is not register to access');
        $this->set('hello', ['aaa'], 'abc');
    }

    public function testUnset()
    {
        $writeArray = $this->writeArray;
        $this->unset('writeArray', ['abc', 'dfg']);
        unset($writeArray['abc']['dfg']);
        $this->assertEquals($this->get('writeArray', []), $writeArray);
        $this->setExpectedException('RuntimeException', 'readArray property is not allow to modify');
        $this->unset('readArray', ['aaa'], 'abc');
    }
}
