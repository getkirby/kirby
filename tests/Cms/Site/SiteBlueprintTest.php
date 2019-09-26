<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class SiteBlueprintTest extends TestCase
{
    public function testOptions()
    {
        $blueprint = new SiteBlueprint([
            'model' => new Site()
        ]);

        $expected = [
            'changeTitle' => null,
            'update'      => null,
        ];

        $this->assertEquals($expected, $blueprint->options());
    }
}
