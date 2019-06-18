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

    public function testInvalidContent()
    {
        $this->expectException('TypeError');

        $site = new Site([
            'content' => 'content'
        ]);
    }
}
