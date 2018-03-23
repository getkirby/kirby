<?php

namespace Kirby\Cms;

class FileBlueprintTest extends TestCase
{

    public function testTemplateFromContent()
    {
        $file = new File([
            'filename' => 'test.jpg',
            'content' => [
                'template' => 'gallery'
            ]
        ]);

        $this->assertEquals('gallery', $file->template());
    }

    public function testCustomTemplate()
    {
        $file = new File([
            'filename' => 'test.jpg',
            'template' => 'gallery'
        ]);

        $this->assertEquals('gallery', $file->template());
    }

    public function testDefaultBlueprint()
    {
        $file = new File([
            'filename' => 'test.jpg',
            'template' => 'does-not-exist',
        ]);

        $blueprint = $file->blueprint();

        $this->assertInstanceOf(FileBlueprint::class, $blueprint);
    }

    public function testCustomBlueprint()
    {
        new App([
            'blueprints' => [
                'files/gallery' => [
                    'name'  => 'gallery',
                    'title' => 'Gallery',
                ]
            ]
        ]);

        $file = new File([
            'filename' => 'test.jpg',
            'template' => 'gallery',
        ]);

        $blueprint = $file->blueprint();

        $this->assertInstanceOf(FileBlueprint::class, $blueprint);
        $this->assertEquals('Gallery', $blueprint->title());
    }

}
