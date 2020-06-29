<?php

namespace Kirby\Cms;

class FileBlueprintTest extends TestCase
{
    public function testOptions()
    {
        $blueprint = new FileBlueprint([
            'model' => new File(['filename' => 'test.jpg'])
        ]);

        $expected = [
            'changeName' => null,
            'create'     => null,
            'delete'     => null,
            'read'       => null,
            'replace'    => null,
            'update'     => null,
        ];

        $this->assertEquals($expected, $blueprint->options());
    }

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

    public function testExtendAccept()
    {
        new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'blueprints' => [
                'files/base' => [
                    'name'  => 'base',
                    'title' => 'Base',
                    'accept' => [
                        'mime' => 'image/jpeg'
                    ]
                ],
                'files/image' => [
                    'name'    => 'image',
                    'title'   => 'Image',
                    'extends' => 'files/base'
                ]
            ]
        ]);

        $file = new File([
            'filename' => 'test.jpg',
            'template' => 'image',
        ]);

        $blueprint = $file->blueprint();
        $this->assertEquals('image/jpeg', $blueprint->accept()['mime']);
    }
}
