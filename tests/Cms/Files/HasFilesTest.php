<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase as BaseTestCase;

class HasFileTraitUser
{
    use HasFiles;

    protected $files;

    public function __construct($files)
    {
        $this->files = $files;
    }

    public function files()
    {
        return new Files($this->files);
    }
}


class HasFilesTest extends TestCase
{
    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);
    }

    public function fileProvider()
    {
        return [
            ['test.mp3', 'audio', true],
            ['test.jpg', 'audio', false],
            ['test.json', 'code', true],
            ['test.jpg', 'code', false],
            ['test.pdf', 'documents', true],
            ['test.jpg', 'documents', false],
            ['test.jpg', 'images', true],
            ['test.mov', 'images', false],
            ['test.mov', 'videos', true],
            ['test.jpg', 'videos', false],
        ];
    }

    /**
     * @dataProvider fileProvider
     */
    public function testTypes($filename, $type, $expected)
    {
        $parent = new HasFileTraitUser([
            new File(['filename' => $filename])
        ]);

        if ($expected === true) {
            $this->assertCount(1, $parent->{$type}());
        } else {
            $this->assertCount(0, $parent->{$type}());
        }
    }

    /**
     * @dataProvider fileProvider
     */
    public function testHas($filename, $type, $expected)
    {
        $parent = new HasFileTraitUser([
            new File(['filename' => $filename])
        ]);

        $this->assertEquals($expected, $parent->{'has' . $type}());
    }
}
