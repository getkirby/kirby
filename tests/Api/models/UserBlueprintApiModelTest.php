<?php

namespace Kirby\Api;

use Kirby\Cms\App;
use Kirby\Cms\User;
use Kirby\Cms\UserBlueprint;
use PHPUnit\Framework\TestCase;

class UserBlueprintApiModelTest extends TestCase
{
    protected $api;
    protected $app;
    protected $user;

    public function attr($object, $attr)
    {
        return $this->api->resolve($object)->select($attr)->toArray()[$attr];
    }

    public function assertAttr($object, $attr, $value)
    {
        $this->assertEquals($this->attr($object, $attr), $value);
    }

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
        ]);

        $this->api  = $this->app->api();
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
