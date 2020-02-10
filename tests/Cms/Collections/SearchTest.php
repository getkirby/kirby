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
            ],
            [
                'slug'    => 'snowball',
                'content' => ['firstname' => 'Šnowball']
            ]
        ]);

        $search = Search::collection($collection, 'ma', ['fields' => ['FirstName']]);
        $this->assertCount(2, $search);

        $search = Search::collection($collection, 'Ho', ['fields' => ['FirstName']]);
        $this->assertCount(1, $search);

        $search = Search::collection($collection, 'm', ['minlength' => 1, 'fields' => ['FirstName']]);
        $this->assertCount(3, $search);
    }

    public function testIgnoreCaseI18n()
    {
        $collection = Pages::factory([
            [
                'slug'    => 'santa',
                'content' => ['full' => 'Santa\'s Little Helper']
            ],
            [
                'slug'    => 'snowball',
                'content' => ['full' => 'Šnowball']
            ],
            [
                'slug'    => 'garfield',
                'content' => ['full' => 'Garfield']
            ]
        ]);

        $search = Search::collection($collection, 's', ['minlength' => 1]);
        $this->assertCount(2, $search);
        $search = Search::collection($collection, 'S', ['minlength' => 1]);
        $this->assertCount(2, $search);

        $search = Search::collection($collection, 'š', ['minlength' => 1]);
        $this->assertCount(1, $search);
        $search = Search::collection($collection, 'Š', ['minlength' => 1]);
        $this->assertCount(1, $search);

        $search = Search::collection($collection, 'g', ['minlength' => 1]);
        $this->assertCount(1, $search);
        $search = Search::collection($collection, 'G', ['minlength' => 1]);
        $this->assertCount(1, $search);
    }
}
