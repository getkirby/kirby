<?php

namespace Kirby\Cms;

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


    public function testIgnoreFieldCase()
    {
        $collection = Pages::factory([
            [
                'slug'    => 'homer',
                'content' => ['firstname' => 'Homer']
            ],
            [
                'slug'    => 'marge',
                'content' => ['firstname' => 'Marge']
            ],
            [
                'slug'    => 'maggie',
                'content' => ['firstname' => 'Maggie']
            ],
            [
                'slug'    => 'lisa',
                'content' => ['firstname' => 'Lisa']
            ]
        ]);

        $search = Search::collection($collection, 'ma', ['fields' => ['FirstName']]);
        $this->assertCount(2, $search);

        $search = Search::collection($collection, 'Ho', ['fields' => ['FirstName']]);
        $this->assertCount(1, $search);

        $search = Search::collection($collection, 'm', ['minlength' => 1, 'fields' => ['FirstName']]);
        $this->assertCount(3, $search);
    }
}
