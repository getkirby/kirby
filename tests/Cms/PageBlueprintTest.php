<?php

namespace Kirby\Cms;

class PageBlueprintTest extends TestCase
{

    public function blueprint()
    {
        return new PageBlueprint([
            'name'  => 'test',
            'tabs'  => [],
            'title' => 'Test'
        ]);
    }

    public function testBlueprint()
    {
        $page = new Page([
            'id'        => 'test',
            'blueprint' => $blueprint = $this->blueprint()
        ]);

        $this->assertEquals($blueprint, $page->blueprint());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The plugin "store" does not exist
     */
    public function testBlueprintWithoutStore()
    {
        $page = new Page(['id' => 'test']);
        $page->blueprint();
    }

    public function testBlueprintWithStore()
    {
        $blueprint = $this->blueprint();
        $store = new Store([
            'page.blueprint' => function () use ($blueprint) {
                return $blueprint;
            }
        ]);

        $page = new Page([
            'id'    => 'test',
            'store' => $store
        ]);

        $this->assertEquals($blueprint, $page->blueprint());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "blueprint" attribute must be of type "Kirby\Cms\PageBlueprint"
     */
    public function testInvalidBlueprint()
    {
        $page = new Page([
            'id'        => 'test',
            'blueprint' => new Blueprint([
                'name'  => 'test',
                'tabs'  => [],
                'title' => 'Test'
            ])
        ]);
    }

}
