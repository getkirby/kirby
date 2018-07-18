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

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     * @expectedExceptionCode error.page.num.invalid
     */
    public function testInvalidChangeNum()
    {
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

    /**
     * @expectedException Kirby\Exception\PermissionException
     * @expectedExceptionCode error.page.changeSlug.permission
     */
    public function testChangeSlugWithHomepage()
    {
        // TODO: should actually throw a different exception than above
        $site = new Site();
        $page = new Page([
            'kirby' => $this->appWithAdmin(),
            'slug'  => 'test',
            'site'  => $site
        ]);

        $site->setHomepage($page);
        PageRules::changeSlug($page, 'test-a');
    }

    /**
     * @expectedException Kirby\Exception\PermissionException
     * @expectedExceptionCode error.page.changeSlug.permission
     */
    public function testChangeSlugWithErrorPage()
    {
        // TODO: should actually throw a different exception than above
        $site = new Site();
        $page = new Page([
            'kirby' => $this->appWithAdmin(),
            'slug'  => 'test',
            'site'  => $site
        ]);

        $site->setErrorPage($page);
        PageRules::changeSlug($page, 'test-a');
    }

    public function testChangeTemplate()
    {
        // TODO: this currently fails, since there is only 1 template
        // which is why the template cannot be changed
        $this->markTestIncomplete();

        $page = new Page([
            'kirby' => $this->appWithAdmin(),
            'slug'  => 'test',
        ]);

        $this->assertTrue(PageRules::changeTemplate($page, 'project'));
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

    /**
     * @expectedException Kirby\Exception\PermissionException
     * @expectedExceptionCode error.page.delete.permission
     */
    public function testDeleteHomepage()
    {
        $site = new Site();
        $page = new Page([
            'kirby' => $this->appWithAdmin(),
            'slug'  => 'test',
            'site'  => $site
        ]);
        $site->setHomepage($page);
        PageRules::delete($page);
    }

    /**
     * @expectedException Kirby\Exception\PermissionException
     * @expectedExceptionCode error.page.delete.permission
     */
    public function testDeleteErrorPage()
    {
        // TODO: is there actually a check in the backend for this?
        $site = new Site();
        $page = new Page([
            'kirby' => $this->appWithAdmin(),
            'slug'  => 'test',
            'site'  => $site
        ]);
        $site->setErrorPage($page);
        PageRules::delete($page);
    }

    /**
     * @expectedException Kirby\Exception\LogicException
     * @expectedExceptionCode error.page.delete.hasChildren
     */
    public function testDeleteWithChildren()
    {
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
