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
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 passed to Kirby\Cms\Page::setBlueprint() must be an instance of Kirby\Cms\PageBlueprint or null, instance of Kirby\Cms\Blueprint given
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
