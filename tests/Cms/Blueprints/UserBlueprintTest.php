<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserBlueprint::class)]
class UserBlueprintTest extends TestCase
{
	public function tearDown(): void
	{
		Blueprint::$loaded = [];
	}

	public function testTranslatedDescription()
	{
		$blueprint = new UserBlueprint([
			'model' => new User(['email' => 'test@getkirby.com']),
			'description' => [
				'en' => 'User',
				'de' => 'Benutzer'
			]
		]);

		$this->assertSame('User', $blueprint->description());
	}

	public function testOptions()
	{
		$blueprint = new UserBlueprint([
			'model' => new User(['email' => 'test@getkirby.com'])
		]);

		$expected = [
			'access'         => null,
			'create'         => null,
			'changeEmail'    => null,
			'changeLanguage' => null,
			'changeName'     => null,
			'changePassword' => null,
			'changeRole'     => null,
			'delete'         => null,
			'list'           => null,
			'update'         => null,
		];

		$this->assertSame($expected, $blueprint->options());
	}

	public function testTitleI18n()
	{
		$app = new App([
			'blueprints' => [
				'users/editor' => [
					'name'  => 'editor',
					'title' => 'role.editor'
				]
			],
			'languages' => [
				[
					'code' => 'en',
					'default' => true,
					'translations' => [
						'role.editor' => 'Editor role'
					]
				],
				[
					'code' => 'de',
					'translations' => [
						'role.editor' => 'Editor-Rolle'
					],
				]
			],
			'users' => [
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor'
				]
			]
		]);

		$app->setCurrentTranslation('de');
		$user = $app->user('editor@getkirby.com');
		$this->assertSame('Editor-Rolle', $user->role()->title());

		// clone app to test other language
		// since $user object has not `->purge()` method
		$app = $app->clone();
		$app->setCurrentTranslation('en');
		$user = $app->user('editor@getkirby.com');
		$this->assertSame('Editor role', $user->role()->title());
	}

	public function testTitleI18nWithFallbackLanguage()
	{
		$app = new App([
			'blueprints' => [
				'users/editor' => [
					'name'  => 'editor',
					'title' => 'role.editor'
				]
			],
			'languages' => [
				[
					'code' => 'en',
					'default' => true,
					'translations' => [
						'role.editor' => 'Editor role'
					]
				],
				[
					'code' => 'de',
					'translations' => [],
				]
			],
			'users' => [
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor'
				]
			]
		]);

		$app->setCurrentTranslation('fr');
		$user = $app->user('editor@getkirby.com');
		$this->assertSame('Editor role', $user->role()->title());
	}

	public function testTitleI18nArray()
	{
		$app = new App([
			'blueprints' => [
				'users/editor' => [
					'name'  => 'editor',
					'title' => [
						'en' => 'Editor role',
						'de' => 'Editor-Rolle'
					]
				]
			],
			'languages' => [
				[
					'code' => 'en',
					'default' => true
				],
				[
					'code' => 'de'
				]
			],
			'users' => [
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor'
				]
			]
		]);

		$app->setCurrentTranslation('de');
		$user = $app->user('editor@getkirby.com');
		$this->assertSame('Editor-Rolle', $user->role()->title());

		// clone app to test other language
		// since $user object has not `->purge()` method
		$app = $app->clone();
		$app->setCurrentTranslation('en');
		$user = $app->user('editor@getkirby.com');
		$this->assertSame('Editor role', $user->role()->title());
	}

	public function testTitleI18nArrayFallBack()
	{
		$app = new App([
			'blueprints' => [
				'users/editor' => [
					'name'  => 'editor',
					'title' => [
						'en' => 'Editor role',
						'de' => 'Editor-Rolle'
					]
				]
			],
			'languages' => [
				[
					'code' => 'en',
					'default' => true
				],
				[
					'code' => 'de'
				]
			],
			'users' => [
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor'
				]
			]
		]);

		$app->setCurrentTranslation('fr');
		$user = $app->user('editor@getkirby.com');
		$this->assertSame('Editor role', $user->role()->title());
	}
}
