<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class FilesSectionTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        App::destroy();

        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);
    }

    public function testAccept()
    {
        $section = new Section('files', [
            'name'     => 'test',
            'model'    => new Page(['slug' => 'test']),
            'template' => 'note'
        ]);

        $this->assertSame('*', $section->accept());
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

    public function testParentCollectionFail()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The parent for the section "files" has to be a page, site or user object');

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

        $section = new Section('files', [
            'model'  => $app->page('a'),
            'parent' => 'site.index'
        ]);
        $section->parentModel();
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

        $this->assertEquals('<p>Test</p>', $section->help());

        // translated help
        $section = new Section('files', [
            'name'     => 'test',
            'model'    => new Page(['slug' => 'test']),
            'help' => [
                'en' => 'Information',
                'de' => 'Informationen'
            ]
        ]);

        $this->assertEquals('<p>Information</p>', $section->help());
    }

    public function testSortBy()
    {
        $locale = setlocale(LC_ALL, 0);
        setlocale(LC_ALL, ['de_DE.ISO8859-1', 'de_DE']);

        $model = new Page([
            'slug'  => 'test',
            'files' => [
                [
                    'filename' => 'z.jpg'
                ],
                [
                    'filename' => 'ä.jpg'
                ],
                [
                    'filename' => 'b.jpg'
                ]
            ]
        ]);

        // no settings
        $section = new Section('files', [
            'name'  => 'test',
            'model' => $model
        ]);
        $this->assertEquals('b.jpg', $section->data()[0]['filename']);
        $this->assertEquals('z.jpg', $section->data()[1]['filename']);
        $this->assertEquals('ä.jpg', $section->data()[2]['filename']);

        // custom sorting direction
        $section = new Section('files', [
            'name'   => 'test',
            'model'  => $model,
            'sortBy' => 'filename desc'
        ]);
        $this->assertEquals('ä.jpg', $section->data()[0]['filename']);
        $this->assertEquals('z.jpg', $section->data()[1]['filename']);
        $this->assertEquals('b.jpg', $section->data()[2]['filename']);

        // custom flag
        $section = new Section('files', [
            'name'   => 'test',
            'model'  => $model,
            'sortBy' => 'filename SORT_LOCALE_STRING'
        ]);
        $this->assertEquals('ä.jpg', $section->data()[0]['filename']);
        $this->assertEquals('b.jpg', $section->data()[1]['filename']);
        $this->assertEquals('z.jpg', $section->data()[2]['filename']);

        // flag & sorting direction
        $section = new Section('files', [
            'name'   => 'test',
            'model'  => $model,
            'sortBy' => 'filename desc SORT_LOCALE_STRING'
        ]);
        $this->assertEquals('z.jpg', $section->data()[0]['filename']);
        $this->assertEquals('b.jpg', $section->data()[1]['filename']);
        $this->assertEquals('ä.jpg', $section->data()[2]['filename']);

        setlocale(LC_ALL, $locale);
    }

    public function testSortable()
    {
        $section = new Section('files', [
            'name'  => 'test',
            'model' => new Page(['slug' => 'test']),
        ]);

        $this->assertTrue($section->sortable());
    }

    public function testDisableSortable()
    {
        $section = new Section('files', [
            'name'     => 'test',
            'model'    => new Page(['slug' => 'test']),
            'sortable' => false
        ]);

        $this->assertFalse($section->sortable());
    }

    public function testDisableSortableWhenSortBy()
    {
        $section = new Section('files', [
            'name'   => 'test',
            'model'  => new Page(['slug' => 'test']),
            'sortBy' => 'filename desc'
        ]);

        $this->assertFalse($section->sortable());
    }

    public function testFlip()
    {
        $model = new Page([
            'slug'  => 'test',
            'files' => [
                [
                    'filename' => 'c.jpg'
                ],
                [
                    'filename' => 'a.jpg'
                ],
                [
                    'filename' => 'b.jpg'
                ]
            ]
        ]);

        $section = new Section('files', [
            'name'  => 'test',
            'model' => $model,
            'flip'  => true
        ]);

        $this->assertEquals('c.jpg', $section->data()[0]['filename']);
        $this->assertEquals('b.jpg', $section->data()[1]['filename']);
        $this->assertEquals('a.jpg', $section->data()[2]['filename']);
    }

    public function testTranslatedInfo()
    {
        $model = new Page([
            'slug'  => 'test',
            'files' => [
                ['filename' => 'a.jpg'],
                ['filename' => 'b.jpg']
            ]
        ]);

        $section = new Section('files', [
            'name'  => 'test',
            'model' => $model,
            'info'  => [
                'en' => 'en: {{ file.page.title }}',
                'de' => 'de: {{ file.page.title }}'
            ]
        ]);

        $this->assertSame('en: {{ file.page.title }}', $section->info());
        $this->assertSame('en: test', $section->data()[0]['info']);
        $this->assertSame('en: test', $section->data()[1]['info']);
    }

    public function testTranslatedText()
    {
        $model = new Page([
            'slug'  => 'test',
            'files' => [
                ['filename' => 'a.jpg'],
                ['filename' => 'b.jpg']
            ]
        ]);

        $section = new Section('files', [
            'name'  => 'test',
            'model' => $model,
            'text'  => [
                'en' => 'en: {{ file.filename }}',
                'de' => 'de: {{ file.filename }}'
            ]
        ]);

        $this->assertSame('en: {{ file.filename }}', $section->text());
        $this->assertSame('en: a.jpg', $section->data()[0]['text']);
        $this->assertSame('en: b.jpg', $section->data()[1]['text']);
    }
}
