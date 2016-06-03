<?php
namespace Etu;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class Stream implements StreamInterface
{
    protected $stream;
    protected $size;
    protected $seekable;
    protected $readable;
    protected $writeable;
    protected $metadata;
    protected $customMetadata;

    protected $isAttached = false;

    private static $readWriteHash = [
        'read' => [
            'r' => true, 'w+' => true, 'r+' => true, 'x+' => true, 'c+' => true,
            'rb' => true, 'w+b' => true, 'r+b' => true, 'x+b' => true,
            'c+b' => true, 'rt' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a+' => true,
        ],
        'write' => [
            'w' => true, 'w+' => true, 'rw' => true, 'r+' => true, 'x+' => true,
            'c+' => true, 'wb' => true, 'w+b' => true, 'r+b' => true,
            'x+b' => true, 'c+b' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a' => true, 'a+' => true,
        ],
    ];

    public function __construct($stream, $option = [])
    {
        $this->attach($stream, $option);
    }

    public function __toString()
    {
        if (!$this->isAttached()) {
            return '';
        }

        try {
            $this->seek(0);

            return $this->getContents();
        } catch (RuntimeException $e) {
            return '';
        }
    }

    public function close()
    {
        if ($this->isAttached()) {
            fclose($this->stream);
        }

        $this->detach();
    }

    public function attach($stream, $option = [])
    {
        if (!is_resource($stream)) {
            throw new InvalidArgumentException('argument passed to Stream class must be a resource');
        }

        $this->stream = $stream;
        $this->isAttached = true;

        if (isset($option['size'])) {
            $this->size = $option['size'];
        }

        if (isset($option['customMetadata'])) {
            $this->customMetadata = $option['customMetadata'];
        }
    }

    protected function isAttached()
    {
        return (bool) $this->isAttached;
    }

    public function detach()
    {
        if (!$this->isAttached()) {
            return;
        }

        $stream = $this->stream;
        $this->stream = $this->size = $this->metadata = $this->customMetadata = null;
        $this->isAttached = $this->seekable = $this->readable = $this->writeable = false;

        return $stream;
    }

    public function getSize()
    {
        if (null !== $this->size) {
            return $this->size;
        }

        if (!$this->isAttached()) {
            return;
        }

        $stats = fstat($this->stream);

        if (isset($stats['size'])) {
            $this->size = $stats['size'];
        }

        return $this->size;
    }

    public function tell()
    {
        if (!$this->isAttached()) {
            throw new RuntimeException('source has been detached');
        }

        $position = ftell($this->stream);

        if (false === $position) {
            throw new RuntimeException('source can not tell you the position');
        }

        return $position;
    }

    public function eof()
    {
        return $this->isAttached() ? feof($this->stream) : true;
    }

    public function isSeekable()
    {
        if (null !== $this->seekable) {
            return $this->seekable;
        }

        $this->seekable = false;

        if ($this->isAttached()) {
            $this->seekable = $this->getMetadata('seekable');
        }

        return $this->seekable;
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        if (!$this->isAttached() || !$this->isSeekable()) {
            throw new RuntimeException('source can not be seeked');
        }

        if (fseek($this->stream, $offset, $whence) === -1) {
            throw new RuntimeException('Unable to seek to the position of source');
        }
    }

    public function rewind()
    {
        $this->seek(0);
    }

    public function isWritable()
    {
        if (null !== $this->writeable) {
            return $this->writeable;
        }

        $this->writeable = false;

        if ($this->isAttached()) {
            $metadata = $this->getMetadata();
            $this->writeable = isset(self::$readWriteHash['write'][$metadata['mode']]);
        }

        return $this->writeable;
    }

    public function write($string)
    {
        if (!$this->isWritable()) {
            throw new RuntimeException('source is not writeable');
        }

        $res = fwrite($this->stream, $string);

        if (false === $res) {
            throw new RuntimeException('write to source failed');
        }

        return $res;
    }

    public function isReadable()
    {
        if (null !== $this->readable) {
            return $this->readable;
        }

        $this->readable = false;

        if ($this->isAttached()) {
            $metadata = $this->getMetadata();
            $this->readable = isset(self::$readWriteHash['read'][$metadata['mode']]);
        }

        return $this->readable;
    }

    public function read($length)
    {
        if (!$this->isReadable()) {
            throw new RuntimeException('source is not readable');
        }

        $res = fread($this->stream, $length);

        if (false === $res) {
            throw new RuntimeException('read from source failed');
        }

        return $res;
    }

    public function getContents()
    {
        if (!$this->isReadable() || ($data = stream_get_contents($this->stream)) === false) {
            throw new RuntimeException('Could not get contents from stream');
        }

        return $data;
    }

    public function getMetadata($key = null)
    {
        if (!$this->isAttached()) {
            return $key ? null : [];
        }

        if (null === $this->metadata) {
            $this->metadata = stream_get_meta_data($this->stream);

            if (null !== $this->customMetadata) {
                $this->metadata += $this->customMetadata;
            }
        }

        if (!$key) {
            return $this->metadata;
        }

        return isset($this->metadata[$key]) ? $this->metadata[$key] : null;
    }
}
