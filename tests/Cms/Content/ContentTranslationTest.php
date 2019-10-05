<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class ContentTranslationTest extends TestCase
{
    public function testParentAndCode()
    {
        $page = new Page([
            'slug' => 'test'
        ]);

        $translation = new ContentTranslation([
            'parent' => $page,
            'code'   => 'de'
        ]);

        $this->assertEquals($page, $translation->parent());
        $this->assertEquals('de', $translation->code());
        $this->assertEquals('de', $translation->id());
    }

    public function testContentAndSlug()
    {
        $page = new Page([
            'slug' => 'test'
        ]);

        $translation = new ContentTranslation([
            'parent'  => $page,
            'code'    => 'de',
            'slug'    => 'test',
            'content' => $content = [
                'title' => 'test'
            ]
        ]);

        $this->assertEquals('test', $translation->slug());
        $this->assertEquals($content, $translation->content());
    }

    public function testContentFile()
    {
        $app = new App([
            'roots' => [
                'content' => '/content',
            ]
        ]);


        $page = new Page([
            'slug'     => 'test',
            'template' => 'project'
        ]);

        $translation = new ContentTranslation([
            'parent' => $page,
            'code'   => 'de',
        ]);

        $this->assertEquals('/content/test/project.de.txt', $translation->contentFile());
    }

    public function testExists()
    {
        $page = new Page(['slug' => 'test']);

        $translation = new ContentTranslation([
            'parent' => $page,
            'code'   => 'de',
        ]);

        $this->assertFalse($translation->exists());
    }

    public function testToArrayAndDebugInfo()
    {
        $page = new Page(['slug' => 'test']);

        $translation = new ContentTranslation([
            'parent'  => $page,
            'code'    => 'de',
            'content' => $content = ['a' => 'A']
        ]);

        $expected = [
            'code'    => 'de',
            'content' => $content,
            'exists'  => false,
            'slug'    => null
        ];

        $this->assertEquals($expected, $translation->toArray());
        $this->assertEquals($expected, $translation->__debugInfo());
    }
}
