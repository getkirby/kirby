<?php

namespace Kirby\Cms;

use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\PermissionException;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageRules::class)]
class LanguageRulesTest extends TestCase
{
	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
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
		$language = new Language([
			'code' => 'l',
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid code for the language');

		LanguageRules::create($language);
	}

	public function testCreateWithInvalidName(): void
	{
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
		$language = $this->createStub(Language::class);
		$language->method('code')->willReturn('de');
		$language->method('name')->willReturn('Deutsch');
		$language->method('exists')->willReturn(true);

		$this->expectException(DuplicateException::class);
		$this->expectExceptionMessage('The language already exists');

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
		$language = $this->createStub(Language::class);
		$language->method('isDeletable')->willReturn(false);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to delete the language');

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
		$language = $this->createStub(Language::class);
		$language->method('code')->willReturn('');
		$language->method('name')->willReturn('Deutsch');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid code for the language');

		LanguageRules::update($language);
	}

	public function testUpdateWithoutName(): void
	{
		$language = $this->createStub(Language::class);
		$language->method('code')->willReturn('de');
		$language->method('name')->willReturn('');

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
