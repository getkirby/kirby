<?php

namespace Kirby\Form\Fields;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Form\Field;

class UsersFieldTest extends TestCase
{
    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'users' => [
                [
                    'email' => 'leonardo@getkirby.com'
                ],
                [
                    'email' => 'raphael@getkirby.com'
                ],
                [
                    'email' => 'michelangelo@getkirby.com'
                ],
                [
                    'email' => 'donatello@getkirby.com'
                ]
            ]
        ]);
    }

    public function testDefaultProps()
    {
        $field = new Field('users', [
            'model' => new Page(['slug' => 'test'])
        ]);

        $this->assertEquals('users', $field->type());
        $this->assertEquals('users', $field->name());
        $this->assertEquals([], $field->value());
        $this->assertEquals([], $field->default());
        $this->assertEquals(null, $field->max());
        $this->assertEquals(true, $field->multiple());
        $this->assertTrue($field->save());
    }

    public function testDefaultUser()
    {
        $this->app->impersonate('raphael@getkirby.com');

        $field = new Field('users', [
            'model' => new Page(['slug' => 'test'])
        ]);

        $this->assertEquals('raphael@getkirby.com', $field->default()[0]['email']);
    }

    public function testMultipleDefaultUsers()
    {
        $this->app->impersonate('raphael@getkirby.com');

        $field = new Field('users', [
            'model' => new Page(['slug' => 'test']),
            'default' => [
                'raphael@getkirby.com',
                'donatello@getkirby.com'
            ]
        ]);

        $this->assertEquals('raphael@getkirby.com', $field->default()[0]['email']);
        $this->assertEquals('donatello@getkirby.com', $field->default()[1]['email']);
    }

    public function testDefaultUserDisabled()
    {
        $this->app->impersonate('raphael@getkirby.com');

        $field = new Field('users', [
            'model' => new Page(['slug' => 'test']),
            'default' => false
        ]);

        $this->assertEquals([], $field->default());
    }

    public function testValue()
    {
        $field = new Field('users', [
            'model' => new Page(['slug' => 'test']),
            'value' => [
                'leonardo@getkirby.com', // exists
                'raphael@getkirby.com', // exists
                'homer@getkirby.com'  // does not exist
            ]
        ]);

        $value = $field->value();
        $ids   = array_column($value, 'email');

        $expected = [
            'leonardo@getkirby.com',
            'raphael@getkirby.com'
        ];

        $this->assertEquals($expected, $ids);
    }

    public function testMin()
    {
        $field = new Field('users', [
            'model' => new Page(['slug' => 'test']),
            'value' => [
                'leonardo@getkirby.com',
                'raphael@getkirby.com'
            ],
            'min' => 3
        ]);

        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('min', $field->errors());
    }

    public function testMax()
    {
        $field = new Field('users', [
            'model' => new Page(['slug' => 'test']),
            'value' => [
                'leonardo@getkirby.com',
                'raphael@getkirby.com'
            ],
            'max' => 1
        ]);

        $this->assertFalse($field->isValid());
        $this->assertArrayHasKey('max', $field->errors());
    }
}
