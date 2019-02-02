<?php

namespace Kirby\Cms;

use Kirby\Toolkit\I18n;
use PHPUnit\Framework\TestCase;

class FilesSectionTest extends TestCase
{
    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);
    }

    public function testHeadline()
    {

        // single headline
        $section = new Section('files', [
            'name'     => 'test',
            'model'    => new Page(['slug' => 'test']),
            'headline' => 'Test'
        ]);

        $this->assertEquals('Test', $section->headline());

        // translated headline
        $section = new Section('files', [
            'name'     => 'test',
            'model'    => new Page(['slug' => 'test']),
            'headline' => [
                'en' => 'Files',
                'de' => 'Dateien'
            ]
        ]);

        $this->assertEquals('Files', $section->headline());
    }

    public function testMax()
    {
        $model = new Page([
            'slug'  => 'test',
            'files' => [
                [
                    'filename' => 'a.jpg'
                ],
                [
                    'filename' => 'b.jpg'
                ]
            ]
        ]);

        // already reached the max
        $section = new Section('files', [
            'name'  => 'test',
            'model' => $model,
            'max'   => 2
        ]);

        $this->assertFalse($section->upload());

        // one left
        $section = new Section('files', [
            'name'  => 'test',
            'model' => $model,
            'max'   => 3
        ]);

        $this->assertFalse($section->upload()['multiple']);

        // no max
        $section = new Section('files', [
            'name'  => 'test',
            'model' => $model,
        ]);

        $this->assertTrue($section->upload()['multiple']);
    }

    public function testParent()
    {
        $app = new App([
            'site' => [
                'children' => [
                    [
                        'slug' => 'a'
                    ],
                    [
                        'slug' => 'b'
                    ]
                ]
            ]
        ]);

        $a = $app->page('a');
        $b = $app->page('b');

        // same parent
        $section = new Section('files', [
            'model' => $a,
        ]);

        $this->assertEquals(false, $section->link());
        $this->assertEquals($a, $section->parent());
        $this->assertEquals('pages/a/files', $section->upload()['api']);

        // different parent
        $section = new Section('files', [
            'model'  => $a,
            'parent' => 'site.find("b")'
        ]);

        $this->assertEquals('/pages/b', $section->link());
        $this->assertEquals($b, $section->parent());
        $this->assertEquals('pages/b/files', $section->upload()['api']);
    }

    public function testEmpty()
    {
        $section = new Section('files', [
            'name'  => 'test',
            'model' => new Page(['slug' => 'test']),
            'empty' => 'Test'
        ]);

        $this->assertEquals('Test', $section->empty());
    }

    public function testTranslatedEmpty()
    {
        $section = new Section('files', [
            'name'  => 'test',
            'model' => new Page(['slug' => 'test']),
            'empty' => ['en' => 'Test', 'de' => 'Töst']
        ]);

        $this->assertEquals('Test', $section->empty());
    }

    public function testDragText()
    {
        $model = new Page([
            'slug'  => 'test',
            'files' => [
                [
                    'filename' => 'a.jpg'
                ],
                [
                    'filename' => 'b.jpg'
                ]
            ]
        ]);

        // already reached the max
        $section = new Section('files', [
            'name'  => 'test',
            'model' => $model
        ]);

        $data = $section->data();
        $this->assertEquals('(image: a.jpg)', $data[0]['dragText']);
    }

    public function testDragTextWithDifferentParent()
    {
        $app = $this->app->clone([
            'site' => [
                'children' => [
                    [
                        'slug'  => 'a',
                        'files' => [
                            [
                                'filename' => 'a.jpg'
                            ],
                            [
                                'filename' => 'b.jpg'
                            ]
                        ]
                    ],
                    [
                        'slug' => 'b'
                    ]
                ]
            ]
        ]);

        // already reached the max
        $section = new Section('files', [
            'name'   => 'test',
            'model'  => $app->page('b'),
            'parent' => 'site.find("a")'
        ]);

        $data = $section->data();
        $this->assertEquals('(image: a/a.jpg)', $data[0]['dragText']);
    }

    public function testHelp()
    {

        // single help
        $section = new Section('files', [
            'name'  => 'test',
            'model' => new Page(['slug' => 'test']),
            'help'  => 'Test'
        ]);

        $this->assertEquals('Test', $section->help());

        // translated help
        $section = new Section('files', [
            'name'     => 'test',
            'model'    => new Page(['slug' => 'test']),
            'help' => [
                'en' => 'Information',
                'de' => 'Informationen'
            ]
        ]);

        $this->assertEquals('Information', $section->help());
    }
}
