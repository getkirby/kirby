<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Collection;
use Kirby\Toolkit\Obj;

class SearchTest extends TestCase
{
    public function testCollection()
    {
        $collection = Pages::factory([
            [
                'slug'    => 'homer',
                'content' => ['name' => 'Homer']
            ],
            [
                'slug'    => 'marge',
                'content' => ['name' => 'Marge']
            ],
            [
                'slug'    => 'maggie',
                'content' => ['name' => 'Maggie']
            ],
            [
                'slug'    => 'lisa',
                'content' => ['name' => 'Lisa']
            ]
        ]);

        $search = Search::collection($collection, 'ma');
        $this->assertCount(2, $search);

        $search = Search::collection($collection, 'Ho');
        $this->assertCount(1, $search);

        $search = Search::collection($collection, 'm', ['minlength' => 1]);
        $this->assertCount(3, $search);
    }
}
