<?php

namespace Kirby\Cms;

class PageStatesTest extends TestCase
{
    /**
     * Deregister any plugins for the page
     *
     * @return void
     */
    public function setUp(): void
    {
        new App([
            'roots' => [
                'index' => __DIR__ . '/fixtures/PageStatesTest'
            ]
        ]);
    }

    public function family()
    {
        $app = new App([
            'site' => [
                'children' => [
                    [
                        'slug'     => 'grandma',
                        'children' => [
                            [
                                'slug'     => 'mother',
                                'children' => [
                                    [
                                        'slug' => 'child'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        return $app->site();
    }

    public function testIs()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'children' => [
                    ['slug' => 'a'],
                    ['slug' => 'b'],
                ]
            ]
        ]);

        $a = $app->page('a');
        $b = $app->page('b');
        $site = $app->site();

        $this->assertTrue($a->is($a));
        $this->assertTrue($a->is('a'));

        $this->assertFalse($a->is($b));
        $this->assertFalse($a->is('b'));
        $this->assertFalse($a->is($site));
        $this->assertFalse($a->is('does/not/exist'));
    }

    public function testIsAncestorOf()
    {
        $family  = $this->family();
        $grandma = $family->find('grandma');
        $mother  = $grandma->find('mother');
        $child   = $mother->find('child');

        $this->assertTrue($mother->isAncestorOf($child));
        $this->assertTrue($grandma->isAncestorOf($child));
    }

    public function testIsChildOf()
    {
        $family  = $this->family();
        $grandma = $family->find('grandma');
        $mother  = $grandma->find('mother');
        $child   = $mother->find('child');

        $this->assertTrue($mother->isChildOf($grandma));
        $this->assertTrue($child->isChildOf($mother));
        $this->assertTrue($child->isChildOf($mother->id()));
        $this->assertFalse($grandma->isChildOf($mother));
        $this->assertFalse($child->isChildOf($grandma));
        $this->assertFalse($child->isChildOf('gibberish'));
        $this->assertFalse($child->isChildOf(null));
    }

    public function testIsDescendantOf()
    {
        $family  = $this->family();
        $grandma = $family->find('grandma');
        $mother  = $grandma->find('mother');
        $child   = $mother->find('child');

        $this->assertTrue($child->isDescendantOf($mother));
        $this->assertTrue($child->isDescendantOf('grandma/mother'));
        $this->assertTrue($child->isDescendantOf($grandma));
        $this->assertTrue($child->isDescendantOf('grandma'));
        $this->assertFalse($child->isDescendantOf('does/not/exist'));
    }

    public function testIsDescendantOfActive()
    {
        $family  = $this->family();
        $grandma = $family->find('grandma');
        $mother  = $grandma->find('mother');
        $child   = $mother->find('child');

        $family->visit('grandma');

        $this->assertFalse($grandma->isDescendantOfActive());
        $this->assertTrue($mother->isDescendantOfActive());
        $this->assertTrue($child->isDescendantOfActive());
    }

    public function testIsListed()
    {
        $page = new Page([
            'slug' => 'test',
            'num'  => 1
        ]);

        $this->assertTrue($page->isListed());

        $page = new Page([
            'slug' => 'test',
        ]);

        $this->assertFalse($page->isListed());
    }

    public function testIsUnlisted()
    {
        $page = new Page([
            'slug' => 'test',
        ]);

        $this->assertTrue($page->isUnlisted());

        $page = new Page([
            'slug' => 'test',
            'num'  => 1
        ]);

        $this->assertFalse($page->isUnlisted());
    }

    public function testIsDraft()
    {
        $page = new Page([
            'slug'    => 'test',
            'isDraft' => true
        ]);

        $this->assertTrue($page->isDraft());

        $page = new Page([
            'slug'    => 'test',
        ]);

        $this->assertFalse($page->isDraft());
    }

    public function testIsPublished()
    {
        $page = new Page([
            'slug'    => 'test',
            'isDraft' => true
        ]);

        $this->assertFalse($page->isPublished());

        $page = new Page([
            'slug'    => 'test',
        ]);

        $this->assertTrue($page->isPublished());
    }

    public function testIsActive()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'mother',
                        'children' => [
                            [
                                'slug' => 'child'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $site   = $app->site();
        $mother = $app->page('mother');
        $child  = $app->page('mother/child');

        $this->assertFalse($mother->isActive());
        $this->assertFalse($child->isActive());

        $site->visit('mother');

        $this->assertTrue($mother->isActive());
        $this->assertFalse($child->isActive());

        $site->visit('mother/child');

        $this->assertFalse($mother->isActive());
        $this->assertTrue($child->isActive());
    }

    public function testIsOpen()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'mother',
                        'children' => [
                            [
                                'slug' => 'child'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $site   = $app->site();
        $mother = $app->page('mother');
        $child  = $app->page('mother/child');

        $this->assertFalse($mother->isOpen());
        $this->assertFalse($child->isOpen());

        $site->visit('mother');

        $this->assertTrue($mother->isOpen());
        $this->assertFalse($child->isOpen());

        $site->visit('mother/child');

        $this->assertTrue($mother->isOpen());
        $this->assertTrue($child->isOpen());
    }
}
