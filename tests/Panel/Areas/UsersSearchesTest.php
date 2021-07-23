<?php

namespace Kirby\Panel\Areas;

class UsersSearchesTest extends AreaTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->install();
        $this->login();
    }

    public function testUserSearch(): void
    {
        $this->app([
            'request' => [
                'query' => [
                    'query' => 'test'
                ]
            ]
        ]);

        $this->login();

        $results = $this->search('users')['results'];

        $this->assertCount(1, $results);

        $image = [
            'back' => 'black',
            'color' => 'gray-500',
            'cover' => false,
            'icon'  => 'user',
            'ratio' => '1/1'
        ];

        $this->assertSame($image, $results[0]['image']);
        $this->assertSame('test@getkirby.com', $results[0]['text']);
        $this->assertSame('/users/test', $results[0]['link']);
        $this->assertSame('Admin', $results[0]['info']);
    }
}
