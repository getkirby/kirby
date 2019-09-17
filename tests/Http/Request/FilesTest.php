<?php

namespace Kirby\Http\Request;

use PHPUnit\Framework\TestCase;

class FilesTest extends TestCase
{
    public function testMultipleUploads()
    {
        $upload = [
            'upload' => [
                'name'     => ['a.txt', 'b.txt'],
                'tmp_name' => ['/tmp/a', '/tmp/b'],
                'size'     => [123, 456],
                'error'    => [0, 0]
            ]
        ];

        $files = new Files($upload);

        $this->assertEquals('a.txt', $files->get('upload')[0]['name']);
        $this->assertEquals('/tmp/a', $files->get('upload')[0]['tmp_name']);
        $this->assertEquals(123, $files->get('upload')[0]['size']);
        $this->assertEquals(0, $files->get('upload')[0]['error']);

        $this->assertEquals('b.txt', $files->get('upload')[1]['name']);
        $this->assertEquals('/tmp/b', $files->get('upload')[1]['tmp_name']);
        $this->assertEquals(456, $files->get('upload')[1]['size']);
        $this->assertEquals(0, $files->get('upload')[1]['error']);
    }

    public function testData()
    {
        // default
        $files = new Files();
        $this->assertEquals([], $files->data());

        // custom
        $upload = [
            'upload' => [
                'name'     => 'test.txt',
                'tmp_name' => '/tmp/abc',
                'size'     => 123,
                'error'    => 0
            ]
        ];

        $files = new Files($upload);
        $this->assertEquals($upload, $files->data());
    }

    public function testGet()
    {
        // test with default data
        $files = new Files();
        $this->assertNull($files->get('upload'));

        // test with mock data
        $files = new Files([
            'upload' => [
                'name'     => 'test.txt',
                'tmp_name' => '/tmp/abc',
                'size'     => 123,
                'error'    => 0
            ]
        ]);

        $this->assertEquals(123, $files->get('upload')['size']);
    }

    public function testToArrayAndDebuginfo()
    {
        $data  = [
            'upload' => [
                'name'     => 'test.txt',
                'tmp_name' => '/tmp/abc',
                'size'     => 123,
                'error'    => 0
            ]
        ];

        $files = new Files($data);
        $this->assertEquals($data, $files->toArray());
        $this->assertEquals($data, $files->__debugInfo());
    }

    public function testToJson()
    {
        $data  = [
            'upload' => [
                'name'     => 'test.txt',
                'tmp_name' => '/tmp/abc',
                'size'     => 123,
                'error'    => 0
            ]
        ];

        $files = new Files($data);
        $this->assertEquals(json_encode($data), $files->toJson());
    }
}
