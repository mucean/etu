<?php
namespace tests\Traits;

use Etu\Traits\ArrayPropertyAllAccess;

class ArrayPropertyAccessTest extends \PHPUnit\Framework\TestCase
{
    use ArrayPropertyAllAccess;

    protected $readArray = ['abc' => ['acb' => ['aaa', 'bbb'], 'dfg' => 'cdd']];
    protected $writeArray = ['abc' => ['acb' => ['aaa', 'bbb'], 'dfg' => 'cdd']];
    protected $notArray = 'hi';

    public function __construct()
    {
        $this->registerPropertyAccess('readArray');
        $this->registerPropertyAccess('writeArray', true);
        parent::__construct();
    }

    public function testRegisterPropertyAccess()
    {
        $this->assertEquals($this->accessProperties['readArray'], false);
        $this->assertEquals($this->accessProperties['writeArray'], true);
        $this->expectExceptionMessage('the property must be existed and an array type');
        $this->registerPropertyAccess('error');
    }

    public function testRegisterPropertyAccessException()
    {
        $this->expectExceptionMessage('the property must be existed and an array type');
        $this->registerPropertyAccess('notArray');
    }

    public function testGet()
    {
        $this->assertEquals($this->getProperty('readArray', []), $this->readArray);
        $this->assertEquals($this->getProperty('readArray', ['abc', 'dfg']), 'cdd');
        $this->assertEquals($this->getProperty('readArray', ['abc', 'acb']), ['aaa', 'bbb']);
        $this->assertEquals($this->getProperty('readArray', ['abc', 'acb', 1]), 'bbb');
        $this->assertEquals($this->getProperty('readArray', ['abc', 'acb', 2]), null);
        $this->assertEquals($this->getProperty('readArray', ['abc', 'acb', 2], 'hi'), 'hi');
        $this->assertEquals($this->getProperty('abc', ['abc', 'acb', 2]), null);
        $this->assertEquals($this->getProperty('readArray', []), $this->writeArray);
    }

    public function testHas()
    {
        $this->assertTrue($this->hasProperty('readArray', []));
        $this->assertTrue($this->hasProperty('readArray', ['abc', 'dfg']));
        $this->assertTrue($this->hasProperty('readArray', ['abc', 'acb', 1]));
        $this->assertFalse($this->hasProperty('readArray', ['abc', 'acb', 2]));
    }

    public function testSet()
    {
        $accessPath = ['aaa', 'ccc'];
        $this->setProperty('writeArray', $accessPath, 'bbb');
        $this->assertEquals($this->getProperty('writeArray', $accessPath), 'bbb');
        $this->setProperty('writeArray', $accessPath, 'ddd');
        $this->assertEquals($this->getProperty('writeArray', $accessPath), 'ddd');
        $this->expectExceptionMessage('hello is not register to access');
        $this->setProperty('hello', ['aaa'], 'abc');
    }

    public function testUnset()
    {
        $writeArray = $this->writeArray;
        $this->unsetProperty('writeArray', ['abc', 'dfg']);
        unset($writeArray['abc']['dfg']);
        $this->assertEquals($this->getProperty('writeArray', []), $writeArray);
        $this->expectExceptionMessage('readArray property is not allow to modify');
        $this->unsetProperty('readArray', ['aaa']);
    }
}
