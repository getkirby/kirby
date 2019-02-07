<?php

namespace Kirby\Cms;

class PageStatesTest extends TestCase
{

    /**
     * Deregister any plugins for the page
     *
     * @return void
     */
    public function setUp()
    {
        new App([
            'roots' => [
                'index' => __DIR__ . '/fixtures/PageStatesTest'
            ]
        ]);
    }

    public function family()
    {
        return new Site([
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
        ]);
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

        $this->assertFalse($grandma->isChildOf($mother));
        $this->assertTrue($mother->isChildOf($grandma));
        $this->assertTrue($child->isChildOf($mother));
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
    }

    public function testIsDescendantOfActive()
    {
        $family  = $this->family();
        $grandma = $family->find('grandma');
        $mother  = $grandma->find('mother');
        $child   = $mother->find('child');

        $family->visit('grandma');

        $this->assertTrue($mother->isDescendantOfActive());
        $this->assertTrue($child->isDescendantOfActive());
    }
}
