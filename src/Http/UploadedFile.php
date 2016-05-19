<?php

namespace Etu\Http;

use Psr\Http\Message\UploadedFileInterface;
use Etu\Stream;
use InvalidArgumentException;
use RuntimeException;

class UploadedFile implements UploadedFileInterface
{
    protected $name;
    protected $type;
    protected $size;
    protected $tmpName;
    protected $error;
    protected $sapi = false;

    protected $stream;
    protected $isMoved = false;

    public static function buildFromContext()
    {
        $files = [];
        foreach ($_FILES as $name => $file) {
            if (is_array($file['tmp_name'])) {
                $files[$name] = [];
            } else {
                $files[$name] = new static(
                    $file['tmp_name'],
                    isset($file['name']) ? $file['name'] : null,
                    isset($file['type']) ? $file['type'] : null,
                    isset($file['size']) ? $file['size'] : null,
                    isset($file['error']) ? $file['error'] : UPLOAD_ERR_OK,
                    true
                );
            }
        }
    }

    public function __construct(
        $tmpName,
        $name = null,
        $type = null,
        $size = null,
        $error = UPLOAD_ERR_OK,
        $sapi = false
    ) {
        $this->tmpName = $tmpName;
        $this->name = $name;
        $this->type = $type;
        $this->size = $size;
        $this->error = $error;
        $this->sapi = $sapi;
    }

    public function getStream()
    {
        if ($this->isMoved) {
            throw new RuntimeException(sprintf('uploaded file %s has been moved', $this->name));
        }

        if ($this->stream !== null) {
            $this->stream = new Stream(fopen($this->stream, 'r'));
        }

        return $this->stream;
    }

    public function moveTo($targetPath)
    {
        if ($this->isMoved) {
            throw new RuntimeException(sprintf('uploaded file %s has been moved', $this->name));
        }

        $targetDirName = dirname($targetPath);
        if (!is_writable($targetDirName)) {
            throw new InvalidArgumentException(sprintf(
                '%s is not a writable path, uploaded file can not move to',
                $targetDirName
            ));
        }

        if (strpos($targetPath, '://') !== false) {
            if (!copy($this->tmpName, $targetPath)) {
                throw new RuntimeException('Occur error when moving file');
            }
            if (!unlink($this->tmpName)) {
                throw new RuntimeException('Occur error when moving file');
            }
        } elseif ($this->sapi) {
            if (!is_uploaded_file($tmpName)) {
                throw new RuntimeException(sprintf('%s is not uploaded file', $tmpName));
            }
        } else {
            if (!rename($this->tmpName, $targetPath)) {
                throw new RuntimeException('Occur error when moving file');
            }
        }

        $this->isMoved = true;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getClientFilename()
    {
        return $this->name;
    }

    public function getClientMediaType()
    {
        return $this->type;
    }
}
