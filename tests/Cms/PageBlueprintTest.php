<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class PageBlueprintTest extends TestCase
{

    public function testOptions()
    {
        $blueprint = new PageBlueprint([
            'model' => new Page(['slug' => 'test'])
        ]);

        $expected = [
            'changeSlug'     => null,
            'changeStatus'   => null,
            'changeTemplate' => null,
            'changeTitle'    => null,
            'create'         => null,
            'delete'         => null,
            'preview'        => null,
            'sort'           => null,
            'update'         => null,
        ];

        $this->assertEquals($expected, $blueprint->options());
    }

}
