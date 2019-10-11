<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class PagePermissionsTest extends TestCase
{
    protected $kirby;

    public function setUp(): void
    {
        $this->kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);
    }

    public function actionProvider()
    {
        return [
            ['changeSlug'],
            ['changeStatus'],
            // ['changeTemplate'], Returns false because of only one blueprint
            ['changeTitle'],
            ['create'],
            ['delete'],
            ['duplicate'],
            ['preview'],
            ['sort'],
            ['update'],
        ];
    }

    /**
     * @dataProvider actionProvider
     */
    public function testWithAdmin($action)
    {
        $this->kirby->impersonate('kirby');

        $page = new Page([
            'slug' => 'test',
            'num'  => 1
        ]);

        $this->assertTrue($page->permissions()->can($action));
    }

    /**
     * @dataProvider actionProvider
     */
    public function testWithAdminButDisabledOption($action)
    {
        $this->kirby->impersonate('kirby');

        $page = new Page([
            'slug' => 'test',
            'num'  => 1,
            'blueprint' => [
                'name' => 'test',
                'options' => [
                    $action => false
                ]
            ]
        ]);

        $this->assertFalse($page->permissions()->can($action));
    }

    /**
     * @dataProvider actionProvider
     */
    public function testWithEditorAndPositiveWildcard($action)
    {
        $app = $this->kirby->clone([
            'roles' => [
                ['name' => 'editor']
            ],
            'users' => [
                ['email' => 'editor@getkirby.com', 'role' => 'editor']
            ]
        ]);

        $app->impersonate('editor@getkirby.com');

        $page = new Page([
            'slug' => 'test',
            'num'  => 1,
            'blueprint' => [
                'name' => 'test',
                'options' => [
                    $action => [
                        '*' => true
                    ]
                ]
            ]
        ]);

        $this->assertTrue($page->permissions()->can($action));
    }

    /**
     * @dataProvider actionProvider
     */
    public function testWithEditorAndPositivePermission($action)
    {
        $app = $this->kirby->clone([
            'roles' => [
                ['name' => 'editor']
            ],
            'users' => [
                ['email' => 'editor@getkirby.com', 'role' => 'editor']
            ]
        ]);

        $app->impersonate('editor@getkirby.com');

        $page = new Page([
            'slug' => 'test',
            'num'  => 1,
            'blueprint' => [
                'name' => 'test',
                'options' => [
                    $action => [
                        '*' => false,
                        'editor' => true
                    ]
                ]
            ]
        ]);

        $this->assertTrue($page->permissions()->can($action));
    }

    /**
     * @dataProvider actionProvider
     */
    public function testWithEditorAndNegativeWildcard($action)
    {
        $app = $this->kirby->clone([
            'roles' => [
                ['name' => 'editor']
            ],
            'users' => [
                ['email' => 'editor@getkirby.com', 'role' => 'editor']
            ]
        ]);

        $app->impersonate('editor@getkirby.com');

        $page = new Page([
            'slug' => 'test',
            'num'  => 1,
            'blueprint' => [
                'name' => 'test',
                'options' => [
                    $action => [
                        '*' => false
                    ]
                ]
            ]
        ]);

        $this->assertFalse($page->permissions()->can($action));
    }

    /**
     * @dataProvider actionProvider
     */
    public function testWithEditorAndNegativePermission($action)
    {
        $app = $this->kirby->clone([
            'roles' => [
                ['name' => 'editor']
            ],
            'users' => [
                ['email' => 'editor@getkirby.com', 'role' => 'editor']
            ]
        ]);

        $app->impersonate('editor@getkirby.com');

        $page = new Page([
            'slug' => 'test',
            'num'  => 1,
            'blueprint' => [
                'name' => 'test',
                'options' => [
                    $action => [
                        '*' => true,
                        'editor' => false
                    ]
                ]
            ]
        ]);

        $this->assertFalse($page->permissions()->can($action));
    }

    public function testCanSortListedPages()
    {
        $this->kirby->impersonate('kirby');

        $page = new Page([
            'slug' => 'test',
            'num'  => 1
        ]);

        $this->assertTrue($page->permissions()->can('sort'));
    }

    public function testCannotSortUnlistedPages()
    {
        $this->kirby->impersonate('kirby');

        $page = new Page([
            'slug' => 'test'
        ]);

        $this->assertFalse($page->permissions()->can('sort'));
    }

    public function testCannotSortErrorPage()
    {
        $this->kirby->impersonate('kirby');

        $site = new Site([
            'children' => [
                [
                    'slug' => 'error',
                    'num'  => 1
                ]
            ]
        ]);

        $page = $site->find('error');

        $this->assertFalse($page->permissions()->can('sort'));
    }

    public function testCannotSortPagesWithSortMode()
    {
        $this->kirby->impersonate('kirby');

        // sort mode: zero
        $page = new Page([
            'slug' => 'test',
            'num'  => 0,
            'blueprint' => [
                'num' => 'zero'
            ]
        ]);

        $this->assertFalse($page->permissions()->can('sort'));

        // sort mode: date
        $page = new Page([
            'slug' => 'test',
            'num'  => 20161121,
            'blueprint' => [
                'num' => 'date'
            ]
        ]);

        $this->assertFalse($page->permissions()->can('sort'));

        // sort mode: custom
        $page = new Page([
            'slug' => 'test',
            'num'  => 2012,
            'blueprint' => [
                'num' => '{{ page.year }}'
            ]
        ]);

        $this->assertFalse($page->permissions()->can('sort'));
    }

    /**
     * @dataProvider actionProvider
     */
    public function testWithNobody($action)
    {
        $page  = new Page(['slug' => 'test']);
        $perms = $page->permissions();

        $this->assertFalse($perms->can($action));
    }

    public function testCannotChangeTemplate()
    {
        $this->kirby->impersonate('kirby');

        $page = new Page([
            'slug' => 'test',
        ]);

        $this->assertFalse($page->permissions()->can('changeTemplate'));
    }

    public function testCanChangeTemplate()
    {
        $this->kirby = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'blueprints' => [
                'pages/a' => [
                    'title' => 'A'
                ],
                'pages/b' => [
                    'title' => 'B'
                ]
            ]
        ]);

        $this->kirby->impersonate('kirby');

        $page = new Page([
            'slug' => 'test',
            'blueprint' => [
                'options' => [
                    'template' => [
                        'a',
                        'b'
                    ]
                ]
            ]
        ]);

        $this->assertTrue($page->permissions()->can('changeTemplate'));
    }
}
