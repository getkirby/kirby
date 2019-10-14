<?php

namespace Kirby\Form\Fields;

use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Form\Field;

class FilePickerMixinTest extends TestCase
{
    public function testPageFiles()
    {
        Field::$types = [
            'test' => [
                'mixins'  => ['filepicker'],
                'methods' => [
                    'files' => function () {
                        return $this->filepicker()['data'];
                    }
                ]
            ]
        ];

        $page = new Page([
            'slug' => 'test',
            'files' => [
                ['filename' => 'a.jpg'],
                ['filename' => 'b.jpg'],
                ['filename' => 'c.jpg'],
            ]
        ]);

        $field = $this->field('test', [
            'model' => $page
        ]);

        $files = $field->files();

        $this->assertCount(3, $files);
        $this->assertEquals('test/a.jpg', $files[0]['id']);
        $this->assertEquals('test/b.jpg', $files[1]['id']);
        $this->assertEquals('test/c.jpg', $files[2]['id']);
    }

    public function testFileFiles()
    {
        Field::$types = [
            'test' => [
                'mixins'  => ['filepicker'],
                'methods' => [
                    'files' => function () {
                        return $this->filepicker()['data'];
                    }
                ]
            ]
        ];

        $page = new Page([
            'slug' => 'test',
            'files' => [
                ['filename' => 'a.jpg'],
                ['filename' => 'b.jpg'],
                ['filename' => 'c.jpg'],
            ]
        ]);

        $field = $this->field('test', [
            'model' => $page->file('b.jpg')
        ]);

        $files = $field->files();

        $this->assertCount(3, $files);
        $this->assertEquals('test/a.jpg', $files[0]['id']);
        $this->assertEquals('test/b.jpg', $files[1]['id']);
        $this->assertEquals('test/c.jpg', $files[2]['id']);
    }

    public function testUserFiles()
    {
        Field::$types = [
            'test' => [
                'mixins'  => ['filepicker'],
                'methods' => [
                    'files' => function () {
                        return $this->filepicker()['data'];
                    }
                ]
            ]
        ];

        $user = new User([
            'email' => 'test@getkirby.com',
            'files' => [
                ['filename' => 'a.jpg'],
                ['filename' => 'b.jpg'],
                ['filename' => 'c.jpg'],
            ]
        ]);

        $field = $this->field('test', [
            'model' => $user
        ]);

        $files = $field->files();

        $this->assertCount(3, $files);
        $this->assertEquals($user->id() . '/a.jpg', $files[0]['id']);
        $this->assertEquals($user->id() . '/b.jpg', $files[1]['id']);
        $this->assertEquals($user->id() . '/c.jpg', $files[2]['id']);
    }

    public function testSiteFiles()
    {
        Field::$types = [
            'test' => [
                'mixins'  => ['filepicker'],
                'methods' => [
                    'files' => function () {
                        return $this->filepicker()['data'];
                    }
                ]
            ]
        ];

        $site = new Site([
            'files' => [
                ['filename' => 'a.jpg'],
                ['filename' => 'b.jpg'],
                ['filename' => 'c.jpg'],
            ]
        ]);

        $field = $this->field('test', [
            'model' => $site
        ]);

        $files = $field->files();

        $this->assertCount(3, $files);
        $this->assertEquals('a.jpg', $files[0]['id']);
        $this->assertEquals('b.jpg', $files[1]['id']);
        $this->assertEquals('c.jpg', $files[2]['id']);
    }

    public function testCustomQuery()
    {
        Field::$types = [
            'test' => [
                'mixins'  => ['filepicker'],
                'props' => [
                    'query' => function (string $query = null) {
                        return $query;
                    }
                ],
                'methods' => [
                    'files' => function () {
                        return $this->filepicker([
                            'query' => $this->query
                        ])['data'];
                    }
                ]
            ]
        ];

        $site = new Site([
            'files' => [
                ['filename' => 'a.jpg'],
                ['filename' => 'b.jpg'],
                ['filename' => 'c.jpg'],
            ],
            'children' => [
                ['slug' => 'test']
            ]
        ]);

        $field = $this->field('test', [
            'model' => $site->find('test'),
            'query' => 'site.files'
        ]);

        $files = $field->files();

        $this->assertCount(3, $files);
        $this->assertEquals('a.jpg', $files[0]['id']);
        $this->assertEquals('b.jpg', $files[1]['id']);
        $this->assertEquals('c.jpg', $files[2]['id']);
    }

    public function testMap()
    {
        Field::$types = [
            'test' => [
                'mixins'  => ['filepicker'],
                'props' => [
                    'query' => function (string $query = null) {
                        return $query;
                    }
                ],
                'methods' => [
                    'files' => function () {
                        return $this->filepicker([
                            'map' => function ($file) {
                                return $file->id();
                            }
                        ])['data'];
                    }
                ]
            ]
        ];

        $page = new Page([
            'slug' => 'test',
            'files' => [
                ['filename' => 'a.jpg'],
                ['filename' => 'b.jpg'],
                ['filename' => 'c.jpg'],
            ],
        ]);

        $field = $this->field('test', [
            'model' => $page,
        ]);

        $files = $field->files();

        $expected = [
            'test/a.jpg',
            'test/b.jpg',
            'test/c.jpg'
        ];

        $this->assertEquals($expected, $files);
    }
}
