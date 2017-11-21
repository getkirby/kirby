<?php

namespace Kirby\Content;

use Kirby\Data\Data;
use Kirby\FileSystem\File;
use PHPUnit\Framework\TestCase;

class ContentTest extends TestCase
{

    protected $file = __DIR__ . '/fixtures/content.txt';

    public function testFile()
    {
        // with string
        $content = new Content($this->file);
        $this->assertInstanceOf(File::class, $content->file());

        // with file object
        $file    = new File($this->file);
        $content = new Content($file);
        $this->assertInstanceOf(File::class, $content->file());
        $this->assertTrue($file === $content->file());
    }

    /**
     * @expectedException  Exception
     * @expectedExceptionMessage Invalid content file type: object
     */
    public function testInvalidFileType()
    {
        new Content(new \stdClass);
    }

    public function testExists()
    {
        // existing content
        $content = new Content($this->file);
        $this->assertTrue($content->exists());

        // non-existing content
        $content = new Content('/foo/bar.txt');
        $this->assertFalse($content->exists());
    }

    public function testData()
    {
        // existing file
        $content  = new Content($this->file);
        $expected = ['a' => 'A', 'b' => 'B'];

        $this->assertEquals($expected, $content->data());
        $this->assertEquals($expected, $content->toArray());

        // non-existing file
        $content  = new Content('/does/not/exist.txt');
        $expected = [];

        $this->assertEquals($expected, $content->data());
        $this->assertEquals($expected, $content->toArray());
    }

    public function testFields()
    {
        $content = new Content($this->file);

        $this->assertInstanceOf(Field::class, $content->fields()['a']);
        $this->assertInstanceOf(Field::class, $content->fields()['b']);
        $this->assertNull($content->fields()['c'] ?? null);
    }

    public function testCall()
    {
        $content = new Content($this->file);

        $this->assertInstanceOf(Field::class, $content->a());
        $this->assertInstanceOf(Field::class, $content->b());
    }

    public function testSave()
    {
        $file = __DIR__ . '/tmp/' . uniqid() . '.txt';
        $data = ['a' => 'a', 'b' => 'b'];

        $content = new Content($file);
        $this->assertFalse($content->exists());

        $content->save($data);
        $this->assertTrue($content->exists());
        $this->assertEquals($data, $content->toArray());

        // delete the left-over content file
        $content->file()->delete();
    }

    public function testRename()
    {
        $file    = __DIR__ . '/tmp/' . uniqid() . '.txt';
        $content = new Content($file);

        $content->save([]);
        $this->assertTrue($content->exists());

        $content->rename('awesome');
        $this->assertEquals('awesome', $content->file()->name());

        // delete the left-over content file
        $content->file()->delete();
    }
}
