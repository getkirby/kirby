<?php

namespace Kirby\Cms;

use Kirby\Cms\Api\ApiModelTestCase;

class UserBlueprintApiModelTest extends ApiModelTestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = new User(['email' => 'test@getkirby.com']);
    }

    public function testName()
    {
        $blueprint = new UserBlueprint([
            'name'  => 'test',
            'model' => $this->user
        ]);

        $this->assertAttr($blueprint, 'name', 'test');
    }

    public function testOptions()
    {
        $blueprint = new UserBlueprint([
            'name'  => 'test',
            'model' => $this->user
        ]);

        $options = $this->attr($blueprint, 'options');

        $this->assertArrayHasKey('changeEmail', $options);
        $this->assertArrayHasKey('changeLanguage', $options);
        $this->assertArrayHasKey('changeName', $options);
        $this->assertArrayHasKey('changePassword', $options);
        $this->assertArrayHasKey('changeRole', $options);
        $this->assertArrayHasKey('create', $options);
        $this->assertArrayHasKey('delete', $options);
        $this->assertArrayHasKey('update', $options);
    }

    public function testTabs()
    {
        $blueprint = new UserBlueprint([
            'name'  => 'test',
            'model' => $this->user
        ]);

        $this->assertAttr($blueprint, 'tabs', []);
    }

    public function testTitle()
    {
        $blueprint = new UserBlueprint([
            'name'  => 'test',
            'title' => 'Test',
            'model' => $this->user
        ]);

        $this->assertAttr($blueprint, 'title', 'Test');
    }
}
