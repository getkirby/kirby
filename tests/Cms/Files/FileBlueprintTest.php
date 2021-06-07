<?php

namespace Kirby\Cms;

class FileBlueprintTest extends TestCase
{
    public function testOptions()
    {
        $page = new Page([
            'slug' => 'test'
        ]);

        $blueprint = new FileBlueprint([
            'model' => new File(['filename' => 'test.jpg', 'parent' => $page])
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
        $page = new Page([
            'slug' => 'test'
        ]);

        $file = new File([
            'filename' => 'test.jpg',
            'parent' => $page,
            'content' => [
                'template' => 'gallery'
            ]
        ]);

        $this->assertEquals('gallery', $file->template());
    }

    public function testCustomTemplate()
    {
        $page = new Page([
            'slug' => 'test'
        ]);

        $file = new File([
            'filename' => 'test.jpg',
            'parent'   => $page,
            'template' => 'gallery'
        ]);

        $this->assertEquals('gallery', $file->template());
    }

    public function testDefaultBlueprint()
    {
        $page = new Page([
            'slug' => 'test'
        ]);

        $file = new File([
            'filename' => 'test.jpg',
            'parent'   => $page,
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

        $page = new Page([
            'slug' => 'test'
        ]);

        $file = new File([
            'filename' => 'test.jpg',
            'parent'   => $page,
            'template' => 'gallery',
        ]);

        $blueprint = $file->blueprint();

        $this->assertInstanceOf(FileBlueprint::class, $blueprint);
        $this->assertEquals('Gallery', $blueprint->title());
    }

    public function testAccept()
    {
        $page = new Page([
            'slug' => 'test'
        ]);

        $file = new File([
            'filename' => 'test.jpg',
            'parent'   => $page
        ]);

        // string = MIME types
        $blueprint = new FileBlueprint([
            'accept' => 'image/jpeg, text/*',
            'model'  => $file
        ]);
        $this->assertSame([
            'extension'   => null,
            'mime'        => ['image/jpeg', 'text/*'],
            'maxheight'   => null,
            'maxsize'     => null,
            'maxwidth'    => null,
            'minheight'   => null,
            'minsize'     => null,
            'minwidth'    => null,
            'orientation' => null,
            'type'        => null
        ], $blueprint->accept());

        // empty value = no restrictions
        $expected = [
            'extension'   => null,
            'mime'        => null,
            'maxheight'   => null,
            'maxsize'     => null,
            'maxwidth'    => null,
            'minheight'   => null,
            'minsize'     => null,
            'minwidth'    => null,
            'orientation' => null,
            'type'        => null
        ];

        $blueprint = new FileBlueprint([
            'accept' => true,
            'model'  => $file
        ]);
        $this->assertSame($expected, $blueprint->accept());

        $blueprint = new FileBlueprint([
            'accept' => [
                'mime' => null
            ],
            'model' => $file
        ]);
        $this->assertSame($expected, $blueprint->accept());

        $blueprint = new FileBlueprint([
            'accept' => [
                'extension' => null
            ],
            'model' => $file
        ]);
        $this->assertSame($expected, $blueprint->accept());

        $blueprint = new FileBlueprint([
            'accept' => [
                'type' => null
            ],
            'model' => $file
        ]);
        $this->assertSame($expected, $blueprint->accept());

        $blueprint = new FileBlueprint([
            'accept' => [
                'mime' => null,
                'type' => null
            ],
            'model' => $file
        ]);
        $this->assertSame($expected, $blueprint->accept());

        // no value = default type restriction
        $expected = [
            'extension'   => null,
            'mime'        => null,
            'maxheight'   => null,
            'maxsize'     => null,
            'maxwidth'    => null,
            'minheight'   => null,
            'minsize'     => null,
            'minwidth'    => null,
            'orientation' => null,
            'type'        => ['image', 'document', 'archive', 'audio', 'video']
        ];

        $blueprint = new FileBlueprint([
            'model' => $file
        ]);
        $this->assertSame($expected, $blueprint->accept());

        $blueprint = new FileBlueprint([
            'accept' => null,
            'model'  => $file
        ]);
        $this->assertSame($expected, $blueprint->accept());

        $blueprint = new FileBlueprint([
            'accept' => [],
            'model'  => $file
        ]);
        $this->assertSame($expected, $blueprint->accept());

        // array with mixed case
        $blueprint = new FileBlueprint([
            'accept' => [
                'extensION' => ['txt'],
                'MiMe'      => ['image/jpeg', 'text/*'],
                'MAXsize'   => 100,
                'typE'      => ['document']
            ],
            'model' => $file
        ]);
        $this->assertSame([
            'extension'   => ['txt'],
            'mime'        => ['image/jpeg', 'text/*'],
            'maxheight'   => null,
            'maxsize'     => 100,
            'maxwidth'    => null,
            'minheight'   => null,
            'minsize'     => null,
            'minwidth'    => null,
            'orientation' => null,
            'type'        => ['document']
        ], $blueprint->accept());

        // MIME, extension and type normalization
        $blueprint = new FileBlueprint([
            'accept' => [
                'mime'      => 'image/jpeg,  image/png;q=0.7',
                'extension' => 'txt,json  ,  jpg',
                'type'      => 'document;audio  ,  video'
            ],
            'model' => $file
        ]);
        $this->assertSame([
            'extension'   => ['txt', 'json', 'jpg'],
            'mime'        => ['image/jpeg', 'image/png'],
            'maxheight'   => null,
            'maxsize'     => null,
            'maxwidth'    => null,
            'minheight'   => null,
            'minsize'     => null,
            'minwidth'    => null,
            'orientation' => null,
            'type'        => ['document;audio', 'video']
        ], $blueprint->accept());
    }

    public function testAcceptMime()
    {
        $page = new Page([
            'slug' => 'test'
        ]);

        $file = new File([
            'filename' => 'test.jpg',
            'parent'   => $page
        ]);

        // default restrictions
        $blueprint = new FileBlueprint([
            'model'  => $file
        ]);
        $this->assertSame('*', $blueprint->acceptMime());

        // no restrictions
        $blueprint = new FileBlueprint([
            'accept' => true,
            'model'  => $file
        ]);
        $this->assertSame('*', $blueprint->acceptMime());

        // just MIME restrictions
        $blueprint = new FileBlueprint([
            'accept' => 'image/jpeg,  image/png;q=0.7',
            'model'  => $file
        ]);
        $this->assertSame('image/jpeg, image/png', $blueprint->acceptMime());

        // just extension restrictions
        $blueprint = new FileBlueprint([
            'accept' => [
                'extension' => 'jpg, mp4'
            ],
            'model' => $file
        ]);
        $this->assertSame('image/jpeg, video/mp4', $blueprint->acceptMime());

        // just type restrictions
        $blueprint = new FileBlueprint([
            'accept' => [
                'type' => 'archive, audio'
            ],
            'model' => $file
        ]);
        $this->assertSame(
            'application/x-gzip, application/x-tar, application/x-zip, ' .
            'audio/x-aiff, audio/mp4, audio/midi, audio/mpeg, audio/x-wav',
            $blueprint->acceptMime()
        );

        // combined extension and type restrictions
        $blueprint = new FileBlueprint([
            'accept' => [
                'extension' => 'jpg, txt, png',
                'type'      => 'image, audio'
            ],
            'model' => $file
        ]);
        $this->assertSame('image/jpeg, image/png', $blueprint->acceptMime());

        // don't override explicit MIME types with other restrictions
        $blueprint = new FileBlueprint([
            'accept' => [
                'mime'      => 'image/jpeg,  application/pdf;q=0.7',
                'extension' => 'jpg, txt, png',
                'type'      => 'document, image'
            ],
            'model' => $file
        ]);
        $this->assertSame('image/jpeg, application/pdf', $blueprint->acceptMime());
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

        $page = new Page([
            'slug' => 'test'
        ]);

        $file = new File([
            'filename' => 'test.jpg',
            'parent'   => $page,
            'template' => 'image',
        ]);

        $blueprint = $file->blueprint();
        $this->assertEquals(['image/jpeg'], $blueprint->accept()['mime']);
    }
}
