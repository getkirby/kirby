<?php

namespace Kirby\Cms;

use Kirby\Toolkit\I18n;
use PHPUnit\Framework\TestCase;

class PagesSectionTest extends TestCase
{
    public function setUp()
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
}
