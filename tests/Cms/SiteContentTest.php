<?php

namespace Kirby\Cms;

class SiteContentTest extends TestCase
{

    /**
     * Freshly register all field methods
     */
    protected function setUp()
    {
        Field::methods(require __DIR__ . '/../../extensions/methods.php');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The plugin "store" does not exist
     */
    public function testDefaultContentWithoutStore()
    {
        $site = new Site();
        $this->assertInstanceOf(Content::class, $site->content());
    }

    public function testDefaultContentWithStore()
    {
        $store = new Store([
            'site.content' => function ($site) {
                return new Content([
                    'text' => 'lorem ipsum'
                ], $site);
            }
        ]);

        $site = new Site([
            'store' => $store
        ]);

        $this->assertEquals('lorem ipsum', $site->text()->value());
    }

    public function testContent()
    {
        $content = new Content([
            'text' => 'lorem ipsum'
        ]);

        $site = new Site([
            'content' => $content
        ]);

        $this->assertEquals($content, $site->content());
        $this->assertEquals('lorem ipsum', $site->text()->value());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "content" property must be of type "Kirby\Cms\Content"
     */
    public function testInvalidContent()
    {
        $site = new Site([
            'content' => 'content'
        ]);
    }

    public function testDateWithoutFormat()
    {
        $site = new Site([
            'content' => new Content([
                'date' => '2012-12-12'
            ])
        ]);

        $this->assertEquals(strtotime('2012-12-12'), $site->date());
    }

    public function testDateWithFormat()
    {
        $site = new Site([
            'content' => new Content([
                'date' => '2012-12-12'
            ])
        ]);

        $this->assertEquals('12.12.2012', $site->date('d.m.Y'));
    }

}
