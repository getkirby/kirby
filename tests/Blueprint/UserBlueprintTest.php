<?php

namespace Kirby\Blueprint;

use Kirby\Cms\App;
use Kirby\Cms\User;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UserBlueprint::class)]
class UserBlueprintTest extends TestCase
{
	public function testTranslatedDescription(): void
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

	public function testOptions(): void
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

	public function testDescriptionI18nAcrossModels(): void
	{
		$app = new App([
			'blueprints' => [
				'users/editor' => [
					'name'        => 'editor',
					'description' => 'role.editor'
				]
			],
			'languages' => [
				[
					'code'         => 'en',
					'default'      => true,
					'translations' => ['role.editor' => 'Editor role']
				],
				[
					'code'         => 'de',
					'translations' => ['role.editor' => 'Editor-Rolle']
				]
			],
			'users' => [
				['email' => 'a@getkirby.com', 'role' => 'editor'],
				['email' => 'b@getkirby.com', 'role' => 'editor']
			]
		]);

		$app->setCurrentTranslation('en');
		$this->assertSame(
			'Editor role',
			$app->user('a@getkirby.com')->blueprint()->description()
		);

		// the second user reuses the normalized props of the first one,
		// which must not freeze the description to the first language
		$app->setCurrentTranslation('de');
		$this->assertSame(
			'Editor-Rolle',
			$app->user('b@getkirby.com')->blueprint()->description()
		);
	}

	public function testTitleI18n(): void
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

	public function testTitleI18nWithFallbackLanguage(): void
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

	public function testTitleI18nArray(): void
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

	public function testTitleI18nArrayFallBack(): void
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
