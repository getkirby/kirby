<?php

namespace Kirby\Form\Fields;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;

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
        $field = $this->field('files', [
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
        $field = $this->field('files', [
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
        $field = $this->field('files', [
            'model' => $this->model(),
            'value' => [
                'a.jpg', // exists
                'b.jpg', // exists
            ],
            'min' => 3
        ]);

        $this->assertFalse($field->isValid());
        $this->assertEquals(3, $field->min());
        $this->assertTrue($field->required());
        $this->assertArrayHasKey('min', $field->errors());
    }

    public function testMax()
    {
        $field = $this->field('files', [
            'model' => $this->model(),
            'value' => [
                'a.jpg', // exists
                'b.jpg', // exists
            ],
            'max' => 1
        ]);

        $this->assertFalse($field->isValid());
        $this->assertEquals(1, $field->max());
        $this->assertArrayHasKey('max', $field->errors());
    }

    public function testFilesInDraft()
    {
        $field = $this->field('files', [
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
        $field = $this->field('files', [
            'model' => new Page(['slug' => 'test']),
        ]);

        $this->assertEquals('page.files', $field->query());
    }

    public function testQueryWithSiteParent()
    {
        $field = $this->field('files', [
            'model' => new Site(),
        ]);

        $this->assertEquals('site.files', $field->query());
    }

    public function testQueryWithUserParent()
    {
        $field = $this->field('files', [
            'model' => new User(['email' => 'test@getkirby.com']),
        ]);

        $this->assertEquals('user.files', $field->query());
    }

    public function testEmpty()
    {
        $field = $this->field('files', [
            'model' => new Page(['slug' => 'test']),
            'empty' => 'Test'
        ]);

        $this->assertEquals('Test', $field->empty());
    }

    public function testTranslatedEmpty()
    {
        $field = $this->field('files', [
            'model' => new Page(['slug' => 'test']),
            'empty' => ['en' => 'Test', 'de' => 'Töst']
        ]);

        $this->assertEquals('Test', $field->empty());
    }

    public function testRequiredProps()
    {
        $field = $this->field('files', [
            'model'    => $this->model(),
            'required' => true
        ]);

        $this->assertTrue($field->required());
        $this->assertEquals(1, $field->min());
    }

    public function testRequiredInvalid()
    {
        $field = $this->field('files', [
            'model'    => $this->model(),
            'required' => true
        ]);

        $this->assertFalse($field->isValid());
    }

    public function testRequiredValid()
    {
        $field = $this->field('files', [
            'model'    => $this->model(),
            'required' => true,
            'value' => [
                'a.jpg',
            ],
        ]);

        $this->assertTrue($field->isValid());
    }

    public function testApi()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => ['api.allowImpersonation' => true],
            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'files' => [
                            ['filename' => 'a.jpg'],
                            ['filename' => 'b.jpg'],
                            ['filename' => 'c.jpg'],
                        ],
                        'blueprint' => [
                            'title' => 'Test',
                            'name' => 'test',
                            'fields' => [
                                'gallery' => [
                                    'type' => 'files',
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $app->impersonate('kirby');
        $api = $app->api()->call('pages/test/fields/gallery');

        $this->assertCount(2, $api);
        $this->assertArrayHasKey('data', $api);
        $this->assertArrayHasKey('pagination', $api);
        $this->assertCount(3, $api['data']);
        $this->assertSame('test/a.jpg', $api['data'][0]['id']);
        $this->assertSame('test/b.jpg', $api['data'][1]['id']);
        $this->assertSame('test/c.jpg', $api['data'][2]['id']);
    }
}
