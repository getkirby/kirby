<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class UserBlueprintTest extends TestCase
{
    public function testTranslatedDescription()
    {
        $blueprint = new UserBlueprint([
            'model' => new User(['email' => 'test@getkirby.com']),
            'description' => [
                'en' => 'User',
                'de' => 'Benutzer'
            ]
        ]);

        $this->assertEquals('User', $blueprint->description());
    }

    public function testOptions()
    {
        $blueprint = new UserBlueprint([
            'model' => new User(['email' => 'test@getkirby.com'])
        ]);

        $expected = [
            'create'         => null,
            'changeEmail'    => null,
            'changeLanguage' => null,
            'changeName'     => null,
            'changePassword' => null,
            'changeRole'     => null,
            'delete'         => null,
            'update'         => null,
        ];

        $this->assertEquals($expected, $blueprint->options());
    }
}
