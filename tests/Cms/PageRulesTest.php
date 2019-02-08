<?php

namespace Kirby\Cms;

class PageRulesTest extends TestCase
{
    public function appWithAdmin()
    {
        return new App([
            'user' => 'test@getkirby.com',
            'users' => [
                [
                    'email' => 'test@getkirby.com',
                    'role'  => 'admin'
                ]
            ],
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);
    }

    public function testChangeNum()
    {
        $page = new Page([
            'slug'  => 'test',
            'kirby' => $this->appWithAdmin(),
        ]);

        $this->assertTrue(PageRules::changeNum($page, 2));
        $this->assertTrue(PageRules::changeNum($page));
    }

    public function testInvalidChangeNum()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionCode('error.page.num.invalid');

        $page = new Page([
            'slug'  => 'test',
            'kirby' => $this->appWithAdmin(),
        ]);

        PageRules::changeNum($page, -1);
    }

    public function testChangeSlug()
    {
        $page = new Page([
            'slug'  => 'test',
            'kirby' => $this->appWithAdmin(),
        ]);

        $this->assertTrue(PageRules::changeSlug($page, 'test-a'));
    }

    public function testChangeSlugWithHomepage()
    {
        $this->expectException('Kirby\Exception\PermissionException');
        $this->expectExceptionCode('error.page.changeSlug.permission');

        $app = new App([
            'roots' => [
                'index' => '/dev/null',
            ],
            'site' => [
                'children' => [
                    ['slug' => 'home']
                ]
            ]
        ]);

        $app->impersonate('kirby');

        PageRules::changeSlug($app->page('home'), 'test-a');
    }

    public function testChangeSlugWithErrorPage()
    {
        $this->expectException('Kirby\Exception\PermissionException');
        $this->expectExceptionCode('error.page.changeSlug.permission');

        $app = new App([
            'roots' => [
                'index' => '/dev/null',
            ],
            'site' => [
                'children' => [
                    ['slug' => 'error']
                ]
            ]
        ]);

        $app->impersonate('kirby');

        PageRules::changeSlug($app->page('error'), 'test-a');
    }

    public function testChangeTemplate()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'templates' => [
                'a' => __FILE__,
                'b' => __FILE__
            ],
            'blueprints' => [
                'pages/a' => ['title' => 'a'],
                'pages/b' => ['title' => 'b'],
            ]
        ]);

        $app->impersonate('kirby');

        $page = new Page([
            'kirby' => $app,
            'slug'  => 'test',
            'template' => 'a',
            'blueprint' => [
                'name' => 'a',
                'options' => [
                    'template' => [
                        'a',
                        'b'
                    ]
                ]
            ]
        ]);

        $this->assertTrue(PageRules::changeTemplate($page, 'b'));
    }

    public function testUpdate()
    {
        $page = new Page([
            'kirby' => $this->appWithAdmin(),
            'slug'  => 'test',
        ]);

        $this->assertTrue(PageRules::update($page, [
            'color' => 'red'
        ]));
    }

    public function testDelete()
    {
        $page = new Page([
            'kirby' => $this->appWithAdmin(),
            'slug'  => 'test',
        ]);

        $this->assertTrue(PageRules::delete($page));
    }

    public function testDeleteNotExists()
    {
        $page = new Page([
            'kirby' => $this->appWithAdmin(),
            'slug'  => 'test',
        ]);

        $this->assertTrue(PageRules::delete($page));
    }

    public function testDeleteHomepage()
    {
        $this->expectException('Kirby\Exception\PermissionException');
        $this->expectExceptionCode('error.page.delete.permission');

        $app = new App([
            'roots' => [
                'index' => '/dev/null',
            ],
            'site' => [
                'children' => [
                    ['slug' => 'home']
                ]
            ]
        ]);

        $app->impersonate('kirby');

        PageRules::delete($app->page('home'));
    }

    public function testDeleteErrorPage()
    {
        $this->expectException('Kirby\Exception\PermissionException');
        $this->expectExceptionCode('error.page.delete.permission');

        $app = new App([
            'roots' => [
                'index' => '/dev/null',
            ],
            'site' => [
                'children' => [
                    ['slug' => 'error']
                ]
            ]
        ]);

        $app->impersonate('kirby');

        PageRules::delete($app->page('error'));
    }

    public function testDeleteWithChildren()
    {
        $this->expectException('Kirby\Exception\LogicException');
        $this->expectExceptionCode('error.page.delete.hasChildren');

        $page = new Page([
            'kirby' => $this->appWithAdmin(),
            'slug'  => 'test',
            'children' => [
                ['slug' => 'a'],
                ['slug' => 'b']
            ],
        ]);

        PageRules::delete($page);
    }

    public function testDeleteWithChildrenForce()
    {
        $page = new Page([
            'kirby' => $this->appWithAdmin(),
            'slug'  => 'test',
            'children' => [
                ['slug' => 'a'],
                ['slug' => 'b']
            ],
        ]);


        $this->assertTrue(PageRules::delete($page, true));
    }
}
