<?php

namespace Kirby\Cms;

class SiteContentTest extends TestCase
{
    public function testDefaultContent()
    {
        $site = new Site();
        $this->assertInstanceOf(Content::class, $site->content());
    }

    public function testContent()
    {
        $content = [
            'text' => 'lorem ipsum'
        ];

        $site = new Site([
            'content' => $content
        ]);

        $this->assertEquals($content, $site->content()->toArray());
        $this->assertEquals('lorem ipsum', $site->text()->value());
    }

    /**
     * @expectedException TypeError
     */
    public function testInvalidContent()
    {
        $site = new Site([
            'content' => 'content'
        ]);
    }
}
