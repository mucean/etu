<?php

namespace Etu;

use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface
{
    protected $stream;
    protected $size;
    protected $seekable;
    protected $readable;
    protected $writeable;
    protected $customMetadata;

    private static $readWriteHash = [
        'read' => [
            'r' => true, 'w+' => true, 'r+' => true, 'x+' => true, 'c+' => true,
            'rb' => true, 'w+b' => true, 'r+b' => true, 'x+b' => true,
            'c+b' => true, 'rt' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a+' => true
        ],
        'write' => [
            'w' => true, 'w+' => true, 'rw' => true, 'r+' => true, 'x+' => true,
            'c+' => true, 'wb' => true, 'w+b' => true, 'r+b' => true,
            'x+b' => true, 'c+b' => true, 'w+t' => true, 'r+t' => true,
            'x+t' => true, 'c+t' => true, 'a' => true, 'a+' => true
        ]
    ];

    public function __construct($stream, $option = [])
    {
        if (!is_resource($stream)) {
            throw new \InvalidArgumentException('argument passed to Stream class must be a resource');
        }
        $this->stream = $stream;
        if (isset($option['size'])) {
            $this->size = $option['size'];
        }

        if (isset($option['metadata'])) {
            $this->customMetadata = $option['metadata'];
        }

        $meta = stream_get_meta_data($this->stream);
        $this->seekable = $meta['seekable'];
        $this->readable = isset(self::$readWriteHash['read'][$meta['mode']]);
        $this->writeable = isset(self::$readWriteHash['write'][$meta['mode']]);
    }

    public function __toString()
    {
        if (!isset($this->stream)) {
            return '';
        }
        $this->seek(0);
        return (string) stream_get_contents($this->stream);
    }

    public function close()
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
        $this->detach();
    }

    public function detach()
    {
        if (!isset($this->stream)) {
            return null;
        }

        $stream = $this->stream;
        $this->stream = null;
        $this->size = null;

        $this->seekable = $this->readable = $this->writeable = false;

        return $stream;
    }

    public function getSize()
    {
        if ($this->size !== null) {
            return $this->size;
        }

        if (!isset($this->stream)) {
            return null;
        }

        $stats = fstat($this->stream);

        if (isset($stats['size'])) {
            $this->size = $stats['size'];
            return $this->size;
        }

        return null;
    }

    public function tell()
    {
        if (!isset($this->stream)) {
            throw $this->getDetachException();
        }

        $position = ftell($this->stream);

        if ($position === false) {
            throw new \RuntimeException('source can not tell you the position');
        }

        return $position;
    }

    public function eof()
    {
        return !$this->stream && feof($this->stream);
    }

    public function isSeekable()
    {
        return $this->seekable;
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        if (!isset($this->stream) || !$this->seekable) {
            throw $this->getDetachException();
        }

        if (fseek($this->stream, $offset, $whence) === -1) {
            throw new \RuntimeException('Unable to seek to the position of source');
        }
    }

    public function rewind()
    {
        $this->seek(0);
    }

    public function isWritable()
    {
        return $this->writeable;
    }

    public function write($string)
    {
        if (!$this->writeable) {
            throw new \RuntimeException('source is not writeable');
        }
        $res = fwrite($this->stream, (string) $string);

        if ($res === false) {
            throw new \RuntimeException('write to source failed');
        }

        return $res;
    }

    public function isReadable()
    {
        return $this->readable;
    }

    public function read($length)
    {
        if (!$this->stream) {
            throw $this->getDetachException();
        }

        if (!$this->readable) {
            throw new \RuntimeException('source is not readable');
        }
        return (string) fread($this->stream, $length);
    }

    public function getContents()
    {
        if (!$this->stream) {
            throw $this->getDetachException();
        }

        return (string) stream_get_contents($this->stream);
    }

    public function getMetadata($key = null)
    {
        if (!$this->stream) {
            return $key ? null : [];
        }

        $meta = $this->customMetadata + stream_get_meta_data($this->stream);

        if (!$key) {
            return $meta;
        }

        return isset($meta[$key]) ? $meta[$key] : null;
    }

    protected function getDetachException()
    {
        return new \RuntimeException('source has been detached');
    }
}
