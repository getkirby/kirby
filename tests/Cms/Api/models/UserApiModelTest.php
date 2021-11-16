<?php

namespace Kirby\Cms;

use Kirby\Cms\Api\ApiModelTestCase;

class UserApiModelTest extends ApiModelTestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = new User(['email' => 'test@getkirby.com']);
    }

    public function testFiles()
    {
        $user = new User([
            'email' => 'test@getkirby.com',
            'files' => [
                ['filename' => 'a.jpg'],
                ['filename' => 'b.jpg'],
            ]
        ]);

        $model = $this->api->resolve($user)->select('files')->toArray();

        $this->assertEquals('a.jpg', $model['files'][0]['filename']);
        $this->assertEquals('b.jpg', $model['files'][1]['filename']);
    }

    public function testImage()
    {
        $image = $this->attr($this->user, 'panelImage');
        $expected = [
            'back' => 'black',
            'color' => 'gray-500',
            'cover' => false,
            'icon'  => 'user',
            'ratio' => '1/1'
        ];

        $this->assertSame($expected, $image);
    }
}
