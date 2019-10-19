<?php

namespace Kirby\Form\Fields;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Form\Field;

class UserPickerMixinTest extends TestCase
{
    public function setUp(): void
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'roles' => [
                ['name' => 'admin'],
                ['name' => 'editor']
            ],
            'users' => [
                ['email' => 'a@getkirby.com', 'role' => 'admin'],
                ['email' => 'b@getkirby.com', 'role' => 'editor'],
                ['email' => 'c@getkirby.com', 'role' => 'editor']
            ]
        ]);
    }

    public function testUsersWithoutQuery()
    {
        Field::$types = [
            'test' => [
                'mixins'  => ['userpicker'],
                'methods' => [
                    'users' => function () {
                        return $this->userpicker()['data'];
                    }
                ]
            ]
        ];

        $page = new Page([
            'slug' => 'test'
        ]);

        $field = $this->field('test', [
            'model' => $page
        ]);

        $users = $field->users();

        $this->assertCount(3, $users);
        $this->assertEquals('a@getkirby.com', $users[0]['email']);
        $this->assertEquals('b@getkirby.com', $users[1]['email']);
        $this->assertEquals('c@getkirby.com', $users[2]['email']);
    }

    public function testUsersWithQuery()
    {
        Field::$types = [
            'test' => [
                'mixins'  => ['userpicker'],
                'methods' => [
                    'users' => function () {
                        return $this->userpicker([
                            'query' => 'kirby.users.role("editor")'
                        ])['data'];
                    }
                ]
            ]
        ];

        $page = new Page([
            'slug' => 'test'
        ]);

        $field = $this->field('test', [
            'model' => $page
        ]);

        $users = $field->users();

        $this->assertCount(2, $users);
        $this->assertEquals('b@getkirby.com', $users[0]['email']);
        $this->assertEquals('c@getkirby.com', $users[1]['email']);
    }

    public function testMap()
    {
        Field::$types = [
            'test' => [
                'mixins'  => ['userpicker'],
                'methods' => [
                    'users' => function () {
                        return $this->userpicker([
                            'map' => function ($user) {
                                return $user->email();
                            }
                        ])['data'];
                    }
                ]
            ]
        ];

        $page = new Page([
            'slug' => 'test'
        ]);

        $field = $this->field('test', [
            'model' => $page
        ]);

        $users = $field->users();

        $this->assertEquals([
            'a@getkirby.com',
            'b@getkirby.com',
            'c@getkirby.com',
        ], $users);
    }
}
