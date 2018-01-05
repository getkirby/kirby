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
     * @expectedException Exception
     * @expectedExceptionMessage The plugin "store" does not exist
     */
    public function testBlueprintWithoutStore()
    {
        $user = $this->user();
        $user->blueprint();
    }

    public function testBlueprintWithStore()
    {
        $blueprint = $this->blueprint();
        $store = new Store([
            'user.blueprint' => function () use ($blueprint) {
                return $blueprint;
            }
        ]);

        $user = $this->user([
            'store' => $store
        ]);

        $this->assertEquals($blueprint, $user->blueprint());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The "blueprint" property must be of type "Kirby\Cms\UserBlueprint"
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
