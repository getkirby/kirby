<?php

namespace Kirby\Cms;

use Kirby\Toolkit\I18n;
use PHPUnit\Framework\TestCase;

class PagesSectionTest extends TestCase
{
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
        $this->assertContains('/media/pages/test/a', $data[0]['image']['url']);
        $this->assertContains('/media/pages/test/b', $data[1]['image']['url']);

        // non-existing covers
        $this->assertNull($data[2]['image']['url'] ?? null);
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

        $this->assertEquals('Test', $section->help());

        // translated help
        $section = new Section('pages', [
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
