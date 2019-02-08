<?php

namespace Kirby\Form\Fields;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\File;
use Kirby\Cms\User;
use Kirby\Form\Field;

class FilesFieldTest extends TestCase
{
    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'files' => [
                            [
                                'filename' => 'a.jpg'
                            ],
                            [
                                'filename' => 'b.jpg'
                            ],
                            [
                                'filename' => 'c.jpg'
                            ]
                        ]
                    ],
                ],
                'drafts' => [
                    [
                        'slug'  => 'test-draft',
                        'files' => [
                            [
                                'filename' => 'a.jpg'
                            ],
                            [
                                'filename' => 'b.jpg'
                            ],
                            [
                                'filename' => 'c.jpg'
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }

    public function model()
    {
        return $this->app->page('test');
    }

    public function testDefaultProps()
    {
        $field = new Field('files', [
            'model' => $this->model()
        ]);

        $this->assertEquals('files', $field->type());
        $this->assertEquals('files', $field->name());
        $this->assertEquals([], $field->value());
        $this->assertEquals([], $field->default());
        $this->assertEquals('list', $field->layout());
        $this->assertEquals(null, $field->max());
        $this->assertEquals(true, $field->multiple());
        $this->assertTrue($field->save());
    }

    public function testValue()
    {
        $field = new Field('files', [
            'model' => $this->model(),
            'value' => [
                'a.jpg', // exists
                'b.jpg', // exists
                'e.jpg'  // does not exist
            ]
        ]);

        $value = $field->value();
        $ids   = array_column($value, 'id');

        $expected = [
            'test/a.jpg',
            'test/b.jpg'
        ];

        $this->assertEquals($expected, $ids);
    }

    public function testMin()
    {
        $field = new Field('files', [
            'model' => $this->model(),
            'value' => [
                'a.jpg', // exists
                'b.jpg', // exists
            ],
            'min' => 3
        ]);

        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('min', $field->errors());
    }

    public function testMax()
    {
        $field = new Field('files', [
            'model' => $this->model(),
            'value' => [
                'a.jpg', // exists
                'b.jpg', // exists
            ],
            'max' => 1
        ]);

        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('max', $field->errors());
    }

    public function testFilesInDraft()
    {
        $field = new Field('files', [
            'model' => $this->app->page('test-draft'),
            'value' => [
                'a.jpg', // exists
                'b.jpg', // exists
                'e.jpg', // does not exist
            ]
        ]);

        $value = $field->value();
        $ids   = array_column($value, 'id');

        $expected = [
            'test-draft/a.jpg',
            'test-draft/b.jpg'
        ];

        $this->assertEquals($expected, $ids);
    }

    public function testQueryWithPageParent()
    {
        $field = new Field('files', [
            'model' => new Page(['slug' => 'test']),
        ]);

        $this->assertEquals('page.files', $field->query());
    }

    public function testQueryWithSiteParent()
    {
        $field = new Field('files', [
            'model' => new Site(),
        ]);

        $this->assertEquals('site.files', $field->query());
    }

    public function testQueryWithUserParent()
    {
        $field = new Field('files', [
            'model' => new User(['email' => 'test@getkirby.com']),
        ]);

        $this->assertEquals('user.files', $field->query());
    }
}
