<?php

namespace Kirby\Cms;

use Kirby\Exception\AbilityException;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\PermissionException;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageRules::class)]
class LanguageRulesTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.LanguageRules';

	protected function setUp(): void
	{
		$this->setUpTmp();

		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'roles' => [
				'editor' => [
					'name' => 'editor',
					'permissions' => [
						'languages' => [
							'*' => false
						]
					]
				],
			],
			'users' => [
				['email' => 'admin@getkirby.com', 'role' => 'admin'],
				['email' => 'test@getkirby.com', 'role' => 'editor']
			]
		]);
	}

	protected function tearDown(): void
	{
		$this->tearDownTmp();
	}

	public function testCreate(): void
	{
		$this->app->impersonate('admin@getkirby.com');

		$language = new Language([
			'code' => 'de',
			'name' => 'Deutsch'
		]);

		$this->expectNotToPerformAssertions();

		LanguageRules::create($language);
	}

	public function testCreateWithInvalidCode(): void
	{
		$this->app->impersonate('admin@getkirby.com');

		$language = new Language([
			'code' => 'l',
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid code for the language');

		LanguageRules::create($language);
	}

	public function testCreateWithInvalidName(): void
	{
		$this->app->impersonate('admin@getkirby.com');

		$language = new Language([
			'code' => 'de',
			'name' => ''
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid name for the language');

		LanguageRules::create($language);
	}

	public function testCreateWhenExists(): void
	{
		$this->app->impersonate('admin@getkirby.com');

		Language::create([
			'code' => 'de',
			'name' => 'Deutsch'
		]);

		$language = new Language([
			'code' => 'de',
			'name' => 'Deutsch'
		]);

		$this->assertTrue($language->exists());

		$this->expectException(DuplicateException::class);
		$this->expectExceptionCode('error.language.duplicate');

		LanguageRules::create($language);
	}

	public function testCreateWithoutCurrentUser(): void
	{
		$language = new Language([
			'code' => 'de',
			'name' => 'Deutsch'
		]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to create a language');

		LanguageRules::create($language);
	}

	public function testCreateWithoutPermissions(): void
	{
		$this->app->impersonate('test@getkirby.com');

		$language = new Language([
			'code' => 'de',
			'name' => 'Deutsch'
		]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to create a language');

		LanguageRules::create($language);
	}

	public function testDelete(): void
	{
		$this->app->impersonate('admin@getkirby.com');

		$language = new Language([
			'code' => 'de',
			'name' => 'Deutsch'
		]);

		$this->expectNotToPerformAssertions();

		LanguageRules::delete($language);
	}

	public function testDeleteWhenNotDeletable(): void
	{
		$this->app->impersonate('admin@getkirby.com');

		$language = new Language([
			'code'   => 'de',
			'name'   => 'Deutsch',
			'single' => true
		]);

		$this->assertFalse($language->isDeletable());

		$this->expectException(AbilityException::class);
		$this->expectExceptionMessage('The main language in a single-language installation cannot be deleted');

		LanguageRules::delete($language);
	}

	public function testDeleteWithoutCurrentUser(): void
	{
		$language = new Language([
			'code' => 'de',
			'name' => 'Deutsch'
		]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to delete the language');

		LanguageRules::delete($language);
	}

	public function testDeleteWithoutPermissions(): void
	{
		$this->app->impersonate('test@getkirby.com');

		$language = new Language([
			'code' => 'de',
			'name' => 'Deutsch'
		]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to delete the language');

		LanguageRules::delete($language);
	}

	public function testUpdate(): void
	{
		$this->app->impersonate('admin@getkirby.com');

		$language = new Language([
			'code' => 'de',
			'name' => 'Deutsch'
		]);

		$this->expectNotToPerformAssertions();

		LanguageRules::update($language);
	}

	public function testUpdateWithoutCode(): void
	{
		$this->app->impersonate('admin@getkirby.com');

		$language = new Language([
			'code' => 'd',
			'name' => 'Deutsch'
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid code for the language');

		LanguageRules::update($language);
	}

	public function testUpdateWithoutName(): void
	{
		$this->app->impersonate('admin@getkirby.com');

		$language = new Language([
			'code' => 'de',
			'name' => ''
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid name for the language');

		LanguageRules::update($language);
	}

	public function testUpdateWithoutCurrentUser(): void
	{
		$language = new Language([
			'code' => 'de',
			'name' => 'Deutsch'
		]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to update the language');

		LanguageRules::update($language);
	}

	public function testUpdateWithoutPermissions(): void
	{
		$this->app->impersonate('test@getkirby.com');

		$language = new Language([
			'code' => 'de',
			'name' => 'Deutsch'
		]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to update the language');

		LanguageRules::update($language);
	}

	public function testUpdateDemoteDefault(): void
	{
		$this->app = $this->app->clone([
			'languages' => [
				'de' => [
					'code' => 'de',
					'name'    => 'Deutsch',
					'default' => true
				],
				'en' => [
					'code' => 'en'
				]
			]
		]);

		$this->app->impersonate('admin@getkirby.com');

		$oldLanguage = $this->app->language('de');
		$newLanguage = new Language([
			'code'    => 'de',
			'name'    => 'Deutsch',
			'default' => false
		]);

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Please select another language to be the primary language');

		LanguageRules::update($newLanguage, $oldLanguage);
	}
}
