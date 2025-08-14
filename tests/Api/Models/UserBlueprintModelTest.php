<?php

namespace Kirby\Api;

use Kirby\Cms\User;
use Kirby\Cms\UserBlueprint;

class UserBlueprintModelTest extends ModelTestCase
{
	protected User $user;

	public function setUp(): void
	{
		parent::setUp();
		$this->user = new User(['email' => 'test@getkirby.com']);
	}

	public function testName(): void
	{
		$blueprint = new UserBlueprint([
			'name'  => 'test',
			'model' => $this->user
		]);

		$this->assertAttr($blueprint, 'name', 'test');
	}

	public function testOptions(): void
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

	public function testTabs(): void
	{
		$blueprint = new UserBlueprint([
			'name'  => 'test',
			'model' => $this->user
		]);

		$this->assertAttr($blueprint, 'tabs', []);
	}

	public function testTitle(): void
	{
		$blueprint = new UserBlueprint([
			'name'  => 'test',
			'title' => 'Test',
			'model' => $this->user
		]);

		$this->assertAttr($blueprint, 'title', 'Test');
	}
}
