<?php
namespace Tests\Http;

use Etu\Http\Context;

class ContextTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $context = BuildContext::getContext();
        $this->assertInstanceOf('Etu\Http\Context', $context);

        return $context;
    }

    /**
     * @depends testConstruct
     */
    public function testAll(Context $context)
    {
        $original = BuildContext::$context;
        unset($context['REQUEST_TIME']);
        $this->assertNotEquals($context->all(), $original);
        unset($original['REQUEST_TIME']);
        $this->assertEquals($context->all(), $original);
    }

    public function testKeys()
    {
        $context = BuildContext::getContext();
        $original = BuildContext::$context;
        unset($context['REQUEST_TIME']);
        $this->assertNotEquals($context->keys(), array_keys($original));
        unset($original['REQUEST_TIME']);
        $this->assertEquals($context->keys(), array_keys($original));
    }

    public function testCount()
    {
        $context = BuildContext::getContext();
        $original = BuildContext::$context;
        unset($context['REQUEST_TIME']);
        $this->assertNotEquals($context->count(), count($original));
        unset($original['REQUEST_TIME']);
        $this->assertEquals($context->count(), count($original));
    }

    /**
     * @depends testConstruct
     */
    public function testHas(Context $context)
    {
        $this->assertTrue($context->has('REQUEST_URI'));
        $this->assertFalse($context->has('URI'));
    }

    /**
     * @depends testConstruct
     */
    public function testUnset(Context $context)
    {
        $this->assertTrue($context->has('HTTP_CONNECTION'));
        $context->unset('HTTP_CONNECTION');
        $this->assertFalse($context->has('HTTP_CONNECTION'));
    }

    /**
     * @depends testConstruct
     */
    public function testMerge(Context $context)
    {
        $this->assertEquals($context->get('HTTP_CONTENT_LENGTH'), '0');
        $context->merge(['HTTP_CONTENT_LENGTH' => '256']);
        $this->assertEquals($context->get('HTTP_CONTENT_LENGTH'), '256');
    }

    /**
     * @depends testConstruct
     */
    public function testOffsetExists(Context $context)
    {
        $this->assertTrue(isset($context['REQUEST_URI']));
        $this->assertTrue(isset($context['test']['hi']));
        $this->assertFalse(isset($context['test']['hello']));
    }

    /**
     * @depends testConstruct
     */
    public function testOffsetGet(Context $context)
    {
        $this->assertEquals($context['REQUEST_URI'], '/app/test?aa=bb&cc[]=dd&cc[]=ee');
        $this->assertEquals($context['test']['hi'], 'hello');
    }

    /**
     * @depends testConstruct
     */
    public function testOffsetSet(Context $context)
    {
        $this->assertEquals($context['REQUEST_METHOD'], 'PUT');
        $context['REQUEST_METHOD'] = 'GET';
        $this->assertEquals($context['REQUEST_METHOD'], 'GET');
        $this->assertEquals($context['test']['hi'], 'hello');
        $context['test']['hi'] = 'world';
        $this->assertEquals($context['test']['hi'], 'world');
    }

    /**
     * @depends testConstruct
     */
    public function testOffsetUnset(Context $context)
    {
        $this->assertEquals($context['HTTP_DNT'], '1');
        unset($context['HTTP_DNT']);
        $this->assertArrayNotHasKey('HTTP_DNT', $context->all());
        $this->assertEquals($context['test']['hi'], 'world');
        unset($context['test']['hi']);
        $this->assertEquals($context['test'], []);
    }

    /**
     * @depends testConstruct
     */
    public function testGet(Context $context)
    {
        $this->assertEquals($context->get('REDIRECT_STATUS'), '200');
        $this->assertEquals($context->get('hello'), null);
        $this->assertEquals($context->get('hello', 'world'), 'world');
    }

    /**
     * @depends testConstruct
     */
    public function testSet(Context $context)
    {
        $context->set('REDIRECT_STATUS', 404);
        $this->assertEquals($context['REDIRECT_STATUS'], 404);
    }
}
