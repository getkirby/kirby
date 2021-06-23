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

    public function testImage()
    {
        $image = $this->attr($this->user, 'panelImage');
        $expected = [
            'back' => 'black',
            'cover' => false,
            'ratio' => '1/1',
            'color' => 'gray-500',
            'icon'  => 'user'
        ];

        $this->assertSame($expected, $image);
    }
}
