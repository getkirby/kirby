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

    public function testDefaultBlueprint()
    {
        $this->markTestIncomplete();
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 passed to Kirby\Cms\Site::setBlueprint() must be an instance of Kirby\Cms\SiteBlueprint or null, instance of Kirby\Cms\Blueprint given
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
