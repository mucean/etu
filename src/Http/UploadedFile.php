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
        return static::parseFiles($_FILES);
    }

    public static function parseFiles($files)
    {
        $parsedFiles = [];
        foreach ($files as $name => $file) {
            $parsedFiles[$name] = [];
            if (!is_array($file['error'])) {
                $parsedFiles[$name] = new UploadedFile(
                    $file['tmp_name'],
                    isset($file['name']) ? $file['name'] : '',
                    isset($file['type']) ? $file['type'] : null,
                    isset($file['size']) ? $file['size'] : null,
                    isset($file['error']) ? $file['error'] : UPLOAD_ERR_OK,
                    true
                );
            } else {
                $nextFiles = [];
                $nextNames = array_keys($file['error']);
                foreach ($nextNames as $nextName) {
                    $nextFiles[$nextName]['tmp_name'] = $file['tmp_name'][$nextName];
                    $nextFiles[$nextName]['name'] = $file['name'][$nextName];
                    $nextFiles[$nextName]['type'] = $file['type'][$nextName];
                    $nextFiles[$nextName]['size'] = $file['size'][$nextName];
                    $nextFiles[$nextName]['error'] = $file['error'][$nextName];
                }
                $parsedFiles[$name] = self::parseFiles($nextFiles);
            }
        }

        return $parsedFiles;
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

        if ($this->stream === null) {
            $this->stream = new Stream(fopen($this->tmpName, 'r'));
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
            if (!move_uploaded_file($tmpName, $targetPath)) {
                throw new RuntimeException('Occur error when moving file');
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
