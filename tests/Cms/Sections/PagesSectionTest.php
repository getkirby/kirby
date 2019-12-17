<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class PagesSectionTest extends TestCase
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

    public function testHeadline()
    {

        // single headline
        $section = new Section('pages', [
            'name'     => 'test',
            'model'    => new Page(['slug' => 'test']),
            'headline' => 'Test'
        ]);

        $this->assertEquals('Test', $section->headline());

        // translated headline
        $section = new Section('pages', [
            'name'     => 'test',
            'model'    => new Page(['slug' => 'test']),
            'headline' => [
                'en' => 'Pages',
                'de' => 'Seiten'
            ]
        ]);

        $this->assertEquals('Pages', $section->headline());
    }

    public function testParent()
    {
        $this->app->impersonate('kirby');

        $parent = new Page([
            'slug' => 'test',
            'children' => [
                ['slug' => 'a']
            ]
        ]);

        // regular parent
        $section = new Section('pages', [
            'name'  => 'test',
            'model' => $parent,
        ]);

        $this->assertEquals('test', $section->parent()->id());

        // page.find
        $section = new Section('pages', [
            'name'  => 'test',
            'model' => $parent,
            'parent' => 'page.find("a")'
        ]);

        $this->assertEquals('test/a', $section->parent()->id());
    }

    public function statusProvider()
    {
        return [
            [null, 'all'],
            ['', 'all'],
            ['draft', 'draft'],
            ['drafts', 'draft'],
            ['published', 'published'],
            ['listed', 'listed'],
            ['unlisted', 'unlisted'],
            ['invalid', 'all'],
        ];
    }

    /**
     * @dataProvider statusProvider
     */
    public function testStatus($input, $expected)
    {
        $section = new Section('pages', [
            'name'   => 'test',
            'model'  => new Page(['slug' => 'test']),
            'status' => $input
        ]);

        $this->assertEquals($expected, $section->status());
    }

    public function addableStatusProvider()
    {
        return [
            ['all', true],
            ['draft', true],
            ['published', false],
            ['listed', false],
            ['unlisted', false],
        ];
    }

    public function testAdd()
    {
        $section = new Section('pages', [
            'name'  => 'test',
            'model' => new Page(['slug' => 'test']),
        ]);

        $this->assertTrue($section->add());
    }

    /**
     * @dataProvider addableStatusProvider
     */
    public function testAddWhenStatusIs($input, $expected)
    {
        $section = new Section('pages', [
            'name'     => 'test',
            'model'    => new Page(['slug' => 'test']),
            'status'   => $input
        ]);

        $this->assertEquals($expected, $section->add());
    }

    public function testAddWhenSectionIsFull()
    {
        $page = new Page([
            'slug'     => 'test',
            'children' => [
                ['slug' => 'subpage']
            ]
        ]);

        $section = new Section('pages', [
            'name'  => 'test',
            'model' => $page,
            'max'   => 1
        ]);

        $this->assertFalse($section->add());
    }

    public function testSortBy()
    {
        $locale = setlocale(LC_ALL, 0);
        setlocale(LC_ALL, ['de_DE.ISO8859-1', 'de_DE']);

        $page = new Page([
            'slug'     => 'test',
            'children' => [
                ['slug' => 'subpage-1', 'content' => ['title' => 'Z']],
                ['slug' => 'subpage-2', 'content' => ['title' => 'Ä']],
                ['slug' => 'subpage-3', 'content' => ['title' => 'B']]
            ]
        ]);

        // no settings
        $section = new Section('pages', [
            'name'  => 'test',
            'model' => $page
        ]);
        $this->assertEquals('Z', $section->data()[0]['text']);
        $this->assertEquals('Ä', $section->data()[1]['text']);
        $this->assertEquals('B', $section->data()[2]['text']);

        // sort by field
        $section = new Section('pages', [
            'name'   => 'test',
            'model'  => $page,
            'sortBy' => 'title'
        ]);
        $this->assertEquals('B', $section->data()[0]['text']);
        $this->assertEquals('Z', $section->data()[1]['text']);
        $this->assertEquals('Ä', $section->data()[2]['text']);

        // custom sorting direction
        $section = new Section('pages', [
            'name'   => 'test',
            'model'  => $page,
            'sortBy' => 'title desc'
        ]);
        $this->assertEquals('Ä', $section->data()[0]['text']);
        $this->assertEquals('Z', $section->data()[1]['text']);
        $this->assertEquals('B', $section->data()[2]['text']);

        // custom flag
        $section = new Section('pages', [
            'name'   => 'test',
            'model'  => $page,
            'sortBy' => 'title SORT_LOCALE_STRING'
        ]);
        $this->assertEquals('Ä', $section->data()[0]['text']);
        $this->assertEquals('B', $section->data()[1]['text']);
        $this->assertEquals('Z', $section->data()[2]['text']);

        // flag & sorting direction
        $section = new Section('pages', [
            'name'   => 'test',
            'model'  => $page,
            'sortBy' => 'title desc SORT_LOCALE_STRING'
        ]);
        $this->assertEquals('Z', $section->data()[0]['text']);
        $this->assertEquals('B', $section->data()[1]['text']);
        $this->assertEquals('Ä', $section->data()[2]['text']);

        setlocale(LC_ALL, $locale);
    }

    public function testSortable()
    {
        $section = new Section('pages', [
            'name'  => 'test',
            'model' => new Page(['slug' => 'test']),
        ]);

        $this->assertTrue($section->sortable());
    }

    public function testDisableSortable()
    {
        $section = new Section('pages', [
            'name'     => 'test',
            'model'    => new Page(['slug' => 'test']),
            'sortable' => false
        ]);

        $this->assertFalse($section->sortable());
    }

    public function testFlip()
    {
        $page = new Page([
            'slug'     => 'test',
            'children' => [
                ['slug' => 'subpage-1', 'content' => ['title' => 'C']],
                ['slug' => 'subpage-2', 'content' => ['title' => 'A']],
                ['slug' => 'subpage-3', 'content' => ['title' => 'B']]
            ]
        ]);

        $section = new Section('pages', [
            'name'  => 'test',
            'model' => $page,
            'flip'  => true
        ]);

        $this->assertEquals('B', $section->data()[0]['text']);
        $this->assertEquals('A', $section->data()[1]['text']);
        $this->assertEquals('C', $section->data()[2]['text']);
    }

    public function sortableStatusProvider()
    {
        return [
            ['all', true],
            ['listed', true],
            ['published', true],
            ['draft', false],
            ['unlisted', false],
        ];
    }

    /**
     * @dataProvider sortableStatusProvider
     */
    public function testSortableStatus($input, $expected)
    {
        $section = new Section('pages', [
            'name'     => 'test',
            'model'    => new Page(['slug' => 'test']),
            'status'   => $input
        ]);

        $this->assertEquals($expected, $section->sortable());
    }

    public function testImageString()
    {
        $this->app->impersonate('kirby');

        $model = new Page([
            'slug' => 'test',
            'children' => [
                [
                    'slug' => 'a',
                    'files' => [
                        ['filename' => 'cover.jpg']
                    ]
                ],
                [
                    'slug' => 'b',
                    'files' => [
                        ['filename' => 'cover.jpg']
                    ]
                ],
                [
                    'slug' => 'c'
                ]
            ]
        ]);

        $section = new Section('pages', [
            'name'   => 'test',
            'model'  => $model,
            'image'  => 'page.image("cover.jpg")',
        ]);

        $data = $section->data();

        // existing covers
        $this->assertStringContainsString('data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw', $data[0]['image']['cards']['url']);

        // non-existing covers
        $this->assertNull($data[2]['image']['cards']['url'] ?? null);
    }

    public function testTemplates()
    {
        // single template
        $section = new Section('pages', [
            'name'      => 'test',
            'model'     => new Page(['slug' => 'test']),
            'templates' => 'blog'
        ]);

        $this->assertEquals(['blog'], $section->templates());

        // multiple templates
        $section = new Section('pages', [
            'name'      => 'test',
            'model'     => new Page(['slug' => 'test']),
            'templates' => ['blog', 'notes']
        ]);

        $this->assertEquals(['blog', 'notes'], $section->templates());

        // template via alias
        $section = new Section('pages', [
            'name'     => 'test',
            'model'    => new Page(['slug' => 'test']),
            'template' => 'blog'
        ]);

        $this->assertEquals(['blog'], $section->templates());
    }

    public function testEmpty()
    {
        $section = new Section('pages', [
            'name'  => 'test',
            'model' => new Page(['slug' => 'test']),
            'empty' => 'Test'
        ]);

        $this->assertEquals('Test', $section->empty());
    }

    public function testTranslatedEmpty()
    {
        $section = new Section('pages', [
            'name'  => 'test',
            'model' => new Page(['slug' => 'test']),
            'empty' => ['en' => 'Test', 'de' => 'Töst']
        ]);

        $this->assertEquals('Test', $section->empty());
    }

    public function testHelp()
    {

        // single help
        $section = new Section('pages', [
            'name'  => 'test',
            'model' => new Page(['slug' => 'test']),
            'help'  => 'Test'
        ]);

        $this->assertEquals('<p>Test</p>', $section->help());

        // translated help
        $section = new Section('pages', [
            'name'     => 'test',
            'model'    => new Page(['slug' => 'test']),
            'help' => [
                'en' => 'Information',
                'de' => 'Informationen'
            ]
        ]);

        $this->assertEquals('<p>Information</p>', $section->help());
    }
}
