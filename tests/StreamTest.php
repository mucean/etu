<?php
namespace Tests\Http;

use Etu\Stream;

class StreamTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Stream
     */
    protected $stream;
    protected $str = 'Hello, world!';

    /**
     * @before
     */
    public function createStream()
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $this->str);
        rewind($stream);
        $this->stream = new Stream($stream);
    }

    /**
     * @after
     */
    public function destroyStream()
    {
        $this->stream->close();
    }

    public function testThrowExceptionConstruct()
    {
        $this->expectExceptionMessage('argument passed to Stream class must be a resource');
        new Stream('test');
    }

    public function testConstruct()
    {
        $stream = fopen('php://temp', 'w');
        fwrite($stream, $this->str);
        $stream = new Stream($stream);
        $this->assertInstanceOf('\Etu\Stream', $stream);
    }

    public function testToString()
    {
        $this->assertEquals((string) $this->stream, $this->str);
    }

    public function testGetSize()
    {
        $this->assertEquals($this->stream->getSize(), strlen($this->str));
        $this->stream->close();
        $this->assertEquals($this->stream->getSize(), null);
    }

    public function testTell()
    {
        $this->stream->seek(strlen($this->str));
        $this->assertEquals($this->stream->tell(), strlen($this->str));
        $this->stream->rewind();
        $this->assertEquals($this->stream->tell(), 0);
        $this->stream->close();
        $this->expectExceptionMessage('source has been detached');
        $this->stream->tell();
    }

    public function testEof()
    {
        $this->assertFalse($this->stream->eof());
        $this->stream->getContents();
        $this->assertTrue($this->stream->eof());
        $this->stream->close();
        $this->assertTrue($this->stream->eof());
    }

    public function testIsSeekable()
    {
        $this->assertTrue($this->stream->isSeekable());
        $this->stream->close();
        $this->assertFalse($this->stream->isSeekable());
    }

    public function testSeek()
    {
        $this->stream->seek(strlen($this->str) - 5);
        $this->assertEquals($this->stream->tell(), strlen($this->str) - 5);
        $this->expectExceptionMessage('Unable to seek to the position of source');
        $this->stream->seek(strlen($this->str) + 1);
        $this->stream->close();
        $this->expectExceptionMessage('source can not be seeked');
        $this->stream->seek(0);
    }

    public function testIsWritable()
    {
        $this->assertTrue($this->stream->isWritable());
        $this->stream->close();
        $this->assertFalse($this->stream->isWritable());
    }

    public function testWrite()
    {
        $content = 'good job';
        $this->assertEquals($this->stream->write($content), strlen($content));
        $this->stream->close();
        $this->expectExceptionMessage('source is not writeable');
        $this->stream->write($content);
    }

    public function testIsReadable()
    {
        $this->assertTrue($this->stream->isReadable());
        $this->stream->close();
        $this->assertFalse($this->stream->isReadable());
    }

    public function testRead()
    {
        $this->assertEquals($this->stream->read(5), substr($this->str, 0, 5));
        $this->stream->close();
        $this->expectExceptionMessage('source is not readable');
        $this->stream->read(5);
    }

    public function testGetContent()
    {
        $this->assertEquals($this->stream->getContents(), $this->str);
        $this->stream->close();
        $this->expectExceptionMessage('Could not get contents from stream');
        $this->stream->getContents();
    }

    public function testGetMetadata()
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $this->str);
        rewind($stream);
        $metadata = stream_get_meta_data($stream);
        $this->assertEquals($this->stream->getMetadata(), $metadata);
        $this->assertEquals($this->stream->getMetadata('uri'), $metadata['uri']);
        $this->stream->close();
        $this->assertEquals($this->stream->getMetadata(), []);
        $this->assertEquals($this->stream->getMetadata('uri'), null);
    }

    public function testClose()
    {
        $this->stream->close();
        $this->assertEquals(null, $this->stream->detach());
    }
}
