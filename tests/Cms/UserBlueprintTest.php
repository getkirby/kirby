<?php

namespace Kirby\Cms;

class UserBlueprintTest extends TestCase
{

    public function blueprint()
    {
        return new UserBlueprint([
            'name'  => 'admin',
            'tabs'  => [],
            'title' => 'Admin'
        ]);
    }

    public function user(array $props = [])
    {
        return new User($props + ['id' => 'test@example.com']);
    }

    public function testBlueprint()
    {
        $user = $this->user([
            'blueprint' => $blueprint = $this->blueprint()
        ]);

        $this->assertEquals($blueprint, $user->blueprint());
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 1 passed to Kirby\Cms\User::setBlueprint() must be an instance of Kirby\Cms\UserBlueprint or null, instance of Kirby\Cms\Blueprint given
     */
    public function testInvalidBlueprint()
    {
        $user = $this->user([
            'blueprint' => new Blueprint([
                'name'  => 'test',
                'tabs'  => [],
                'title' => 'Test'
            ])
        ]);
    }

}
