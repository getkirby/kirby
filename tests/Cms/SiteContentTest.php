<?php

namespace Kirby\Cms;

class SiteContentTest extends TestCase
{

    /**
     * Freshly register all field methods
     */
    protected function setUp()
    {
        ContentField::methods(require __DIR__ . '/../../extensions/methods.php');
    }

    public function testDefaultContent()
    {
        $site = new Site();
        $this->assertInstanceOf(Content::class, $site->content());
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
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 passed to Kirby\Cms\Site::setContent() must be an instance of Kirby\Cms\Content or null, string given
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
