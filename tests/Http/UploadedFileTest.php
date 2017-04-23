<?php
namespace Tests\Http;

use Etu\Http\UploadedFile;

class UploadedFileTest extends \PHPUnit\Framework\TestCase
{
    protected static $file = './testfile';

    /**
     * @beforeClass
     */
    public static function createFileBeforeClass()
    {
        $fTest = fopen(self::$file, 'w');
        fwrite($fTest, 'Hello, world!');
        fclose($fTest);
    }

    /**
     * @afterClass
     */
    public static function destoryFileAfterClass()
    {
        if (file_exists(self::$file)) {
            unlink(self::$file);
        }
        // empty $_FILES after class
        $_FILES = [];
    }

    /**
     * @dataProvider contextProvider
     */
    public function testBuildFromContext($files, $expects)
    {
        $_FILES = $files;

        $this->assertEquals(UploadedFile::buildFromContext(), $expects);
    }

    public function testConstruct()
    {
        $file = [
            'tmp_name' => self::$file,
            'name' => 'testFile',
            'type' => 'text/plain',
            'size' => 100,
            'error' => UPLOAD_ERR_OK,
        ];

        $uploadedFile = new UploadedFile(
            $file['tmp_name'],
            $file['name'],
            $file['type'],
            $file['size'],
            $file['error'],
            false
        );

        $this->assertInstanceOf('\Etu\Http\UploadedFile', $uploadedFile);

        $this->assertEquals($uploadedFile->getClientFilename(), $file['name']);
        $this->assertEquals($uploadedFile->getClientMediaType(), $file['type']);
        $this->assertEquals($uploadedFile->getSize(), $file['size']);
        $this->assertEquals($uploadedFile->getError(), $file['error']);

        return $uploadedFile;
    }

    /**
     * @depends testConstruct
     */
    public function testGetStream(UploadedFile $uploadedFile)
    {
        $stream = $uploadedFile->getStream();
        $this->assertInstanceOf('\Etu\Stream', $stream);
        $stream->close();
    }

    /**
     * @depends testConstruct
     */
    public function testMoveTo(UploadedFile $uploadedFile)
    {
        $uploadedFile->moveTo('./abc.txt');

        $this->assertFileExists('./abc.txt');

        if (file_exists('./abc.txt')) {
            unlink('./abc.txt');
        }

        $this->expectExceptionMessage(sprintf(
            'uploaded file %s has been moved',
            $uploadedFile->getClientFilename()
        ));
        $uploadedFile->moveTo('./abc');
    }

    public function contextProvider()
    {
        return [
            [
                ['avatar' => [
                    'tmp_name' => self::$file,
                    'name' => 'avatar.jpg',
                    'type' => 'image/jpg',
                    'size' => 2676,
                    'error' => UPLOAD_ERR_OK,
                ]],
                ['avatar' => new UploadedFile(self::$file, 'avatar.jpg', 'image/jpg', 2676, UPLOAD_ERR_OK, true)],
            ],
            [
                [
                    'avatar' => [
                        'tmp_name' => [
                            0 => self::$file,
                            1 => self::$file,
                        ],
                        'name' => [
                            0 => 'avatar1.jpg',
                            1 => 'avatar2.jpg',
                        ],
                        'type' => [
                            0 => 'image/jpg',
                            1 => 'image/jpg',
                        ],
                        'size' => [
                            0 => 234,
                            1 => 1234,
                        ],
                        'error' => [
                            0 => UPLOAD_ERR_OK,
                            1 => UPLOAD_ERR_OK,
                        ],
                    ],
                ],
                [
                    'avatar' => [
                        0 => new UploadedFile(self::$file, 'avatar1.jpg', 'image/jpg', 234, UPLOAD_ERR_OK, true),
                        1 => new UploadedFile(self::$file, 'avatar2.jpg', 'image/jpg', 1234, UPLOAD_ERR_OK, true),
                    ],
                ],
            ],
            [
                [
                    'avatar' => [
                        'tmp_name' => [
                            'profile' => self::$file,
                        ],
                        'name' => [
                            'profile' => 'profile.jpg',
                        ],
                        'type' => [
                            'profile' => 'image/jpg',
                        ],
                        'size' => [
                            'profile' => 2345,
                        ],
                        'error' => [
                            'profile' => UPLOAD_ERR_OK,
                        ],
                    ],
                ],
                [
                    'avatar' => [
                        'profile' => new UploadedFile(
                            self::$file,
                            'profile.jpg',
                            'image/jpg',
                            2345,
                            UPLOAD_ERR_OK,
                            true
                        ),
                    ],
                ],
                [
                    'avatar' => [
                        'tmp_name' => [
                            'profile' => [
                                0 => self::$file,
                                1 => self::$file,
                            ],
                        ],
                        'name' => [
                            'profile' => [
                                0 => 'profile1.jpg',
                                1 => 'profile2.jpg',
                            ],
                        ],
                        'type' => [
                            'profile' => [
                                0 => 'image/jpg',
                                1 => 'image/jpg',
                            ],
                        ],
                        'size' => [
                            'profile' => [
                                0 => 1234,
                                1 => 4321,
                            ],
                        ],
                        'error' => [
                            'profile' => [
                                0 => UPLOAD_ERR_OK,
                                1 => UPLOAD_ERR_OK,
                            ],
                        ],
                    ],
                ],
                [
                    'avatar' => [
                        'profile' => [
                            0 => new UploadedFile(
                                self::$file,
                                'profile1.jpg',
                                'image/jpg',
                                1234,
                                UPLOAD_ERR_OK,
                                true
                            ),
                            1 => new UploadedFile(
                                self::$file,
                                'profile2.jpg',
                                'image/jpg',
                                4321,
                                UPLOAD_ERR_OK,
                                true
                            ),
                        ],
                    ],
                ],
            ],
        ];
    }
}
