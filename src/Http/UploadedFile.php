<?php

namespace Etu\Http;

use Psr\Http\Message\UploadedFileInterface;
use Etu\Stream;

class UploadedFile implements UploadedFileInterface
{
    protected $file;
    protected $stream;
    protected $isMoved = false;
    protected $targetPath;
    public function __construct(array $file)
    {
        if (!isset($file['tmp_name'])) {
            throw new \InvalidArgumentException('UploadedFile class need an array within tmp_name of $_FILES variate');
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new \InvalidArgumentException(sprintf('%s is not uploaded file', $file['tmp_name']));
        }

        if (!is_file($file['tmp_name'])) {
            $this->isMoved = true;
        }

        $this->file = $file;
    }

    public function getStream()
    {
        if ($this->isMoved) {
            throw new \RuntimeException(sprintf(
                'uploaded file was moved to %s directory',
                $this->targetPath === null ? 'other' : $this->targetPath
            ));
        }

        if (!$this->stream) {
            $this->stream = new Stream(fopen($this->stream, 'r'));
        }

        return $this->stream;
    }

    public function moveTo($targetPath)
    {
        if ($this->isMoved) {
            throw new \RuntimeException(sprintf(
                'uploaded file was moved to %s directory',
                $this->targetPath === null ? 'other' : $this->targetPath
            ));
        }
        $targetDirName = dirname($targetPath);
        if (!is_dir($targetDirName)) {
            throw new \InvalidArgumentException(sprintf(
                '%s is not a directory, uploaded file can not move to',
                $targetDirName
            ));
        }

        if (!move_uploaded_file($this->file['tmp_name'], $targetPath)) {
            throw new \RuntimeException('move uploaded file failed');
        }
    }

    public function getSize()
    {
        return isset($this->file['size']) ? $this->file['size'] : null;
    }

    public function getError()
    {
        return $this->file['error'];
    }

    public function getClientFilename()
    {
        return isset($this->file['name']) ? $this->file['name'] : null;
    }

    public function getClientMediaType()
    {
        return isset($this->file['type']) ? $this->file['type'] : null;
    }

    public static function throwUploadFileException($code)
    {
        $message = '';

        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = "The uploaded file was only partially uploaded";
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = "No file was uploaded";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = "Missing a temporary folder";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = "Failed to write file to disk";
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = "File upload stopped by extension";
                break;

            default:
                $message = "Unknown upload error";
                break;
        }
        throw new \RuntimeException($message, $code);
    }
}
