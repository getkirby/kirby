<?php

namespace Kirby\Cms;

use Kirby\Toolkit\I18n;
use PHPUnit\Framework\TestCase;

class FilesSectionTest extends TestCase
{
    public function setUp(): void
    {
        new App([
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
}
