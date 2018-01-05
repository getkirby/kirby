<?php

namespace Kirby\Cms;

class SiteBlueprintTest extends TestCase
{

    public function blueprint()
    {
        return new SiteBlueprint([
            'name'  => 'site',
            'tabs'  => [],
            'title' => 'Site'
        ]);
    }

    public function testBlueprint()
    {
        $site = new Site([
            'blueprint' => $blueprint = $this->blueprint()
        ]);

        $this->assertEquals($blueprint, $site->blueprint());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The plugin "store" does not exist
     */
    public function testBlueprintWithoutStore()
    {
        $site = new Site();
        $site->blueprint();
    }

    public function testBlueprintWithStore()
    {
        $blueprint = $this->blueprint();
        $store = new Store([
            'site.blueprint' => function () use ($blueprint) {
                return $blueprint;
            }
        ]);

        $site = new Site(['store' => $store]);
        $this->assertEquals($blueprint, $site->blueprint());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "blueprint" property must be of type "Kirby\Cms\SiteBlueprint"
     */
    public function testInvalidBlueprint()
    {
        $site = new Site([
            'blueprint' => new Blueprint([
                'name'  => 'site',
                'tabs'  => [],
                'title' => 'Site'
            ])
        ]);
    }

}
