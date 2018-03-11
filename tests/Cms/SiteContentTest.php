<?php

namespace Kirby\Cms;

class SiteContentTest extends TestCase
{

    /**
     * Freshly register all field methods
     */
    public function setUp()
    {
        parent::setUp();
        ContentField::$methods = require __DIR__ . '/../../extensions/methods.php';
    }

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

    public function testDateWithoutFormat()
    {
        $site = new Site([
            'content' => [
                'date' => '2012-12-12'
            ]
        ]);

        $this->assertEquals(strtotime('2012-12-12'), $site->date());
    }

    public function testDateWithFormat()
    {
        $site = new Site([
            'content' => [
                'date' => '2012-12-12'
            ]
        ]);

        $this->assertEquals('12.12.2012', $site->date('d.m.Y'));
    }

}
