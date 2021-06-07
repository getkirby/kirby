<?php

namespace Kirby\Cms;

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
    protected $app;

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

    public function testFileWithSlash()
    {
        $page = new Page([
            'slug' => 'mother',
            'children' => [
                [
                    'slug' => 'child',
                    'files' => [
                        ['filename' => 'file.jpg']
                    ]
                ]
            ]
        ]);

        $file = $page->file('child/file.jpg');
        $this->assertEquals('mother/child/file.jpg', $file->id());
    }

    /**
     * @dataProvider fileProvider
     */
    public function testTypes($filename, $type, $expected)
    {
        $page = new Page([
            'slug' => 'test'
        ]);

        $parent = new HasFileTraitUser([
            new File(['filename' => $filename, 'parent' => $page])
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
        $page = new Page([
            'slug' => 'test'
        ]);

        $parent = new HasFileTraitUser([
            new File(['filename' => $filename, 'parent' => $page])
        ]);

        $this->assertEquals($expected, $parent->{'has' . $type}());
    }

    public function testHasFiles()
    {
        $page = new Page([
            'slug' => 'test'
        ]);

        // no files
        $parent = new HasFileTraitUser([
        ]);

        $this->assertFalse($parent->hasFiles());

        // files
        $parent = new HasFileTraitUser([
            new File(['filename' => 'test.jpg', 'parent' => $page])
        ]);

        $this->assertTrue($parent->hasFiles());
    }
}
