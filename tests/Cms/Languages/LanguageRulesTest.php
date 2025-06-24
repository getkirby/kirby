<?php

namespace Kirby\Cms;

use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\PermissionException;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Cms\LanguageRules
 */
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

	/**
	 * @covers ::create
	 */
	public function testCreate()
	{
		$this->app->impersonate('admin@getkirby.com');

		$language = new Language([
			'code' => 'de',
			'name' => 'Deutsch'
		]);

		$this->expectNotToPerformAssertions();

		LanguageRules::create($language);
	}

	/**
	 * @covers ::create
	 */
	public function testCreateWithInvalidCode()
	{
		$language = new Language([
			'code' => 'l',
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid code for the language');

		LanguageRules::create($language);
	}

	/**
	 * @covers ::create
	 */
	public function testCreateWithInvalidName()
	{
		$language = new Language([
			'code' => 'de',
			'name' => ''
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid name for the language');

		LanguageRules::create($language);
	}

	/**
	 * @covers ::create
	 */
	public function testCreateWhenExists()
	{
		$language = $this->createMock(Language::class);
		$language->method('code')->willReturn('de');
		$language->method('name')->willReturn('Deutsch');
		$language->method('exists')->willReturn(true);

		$this->expectException(DuplicateException::class);
		$this->expectExceptionMessage('The language already exists');

		LanguageRules::create($language);
	}

	/**
	 * @covers ::create
	 */
	public function testCreateWithoutCurrentUser()
	{
		$language = new Language([
			'code' => 'de',
			'name' => 'Deutsch'
		]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to create a language');

		LanguageRules::create($language);
	}

	/**
	 * @covers ::create
	 */
	public function testCreateWithoutPermissions()
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

	/**
	 * @covers ::delete
	 */
	public function testDelete()
	{
		$this->app->impersonate('admin@getkirby.com');

		$language = new Language([
			'code' => 'de',
			'name' => 'Deutsch'
		]);

		$this->expectNotToPerformAssertions();

		LanguageRules::delete($language);
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteWhenNotDeletable()
	{
		$language = $this->createMock(Language::class);
		$language->method('isDeletable')->willReturn(false);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to delete the language');

		LanguageRules::delete($language);
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteWithoutCurrentUser()
	{
		$language = new Language([
			'code' => 'de',
			'name' => 'Deutsch'
		]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to delete the language');

		LanguageRules::delete($language);
	}

	/**
	 * @covers ::delete
	 */
	public function testDeleteWithoutPermissions()
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

	/**
	 * @covers ::update
	 */
	public function testUpdate()
	{
		$this->app->impersonate('admin@getkirby.com');

		$language = new Language([
			'code' => 'de',
			'name' => 'Deutsch'
		]);

		$this->expectNotToPerformAssertions();

		LanguageRules::update($language);
	}

	/**
	 * @covers ::update
	 */
	public function testUpdateWithoutCode()
	{
		$language = $this->createMock(Language::class);
		$language->method('code')->willReturn('');
		$language->method('name')->willReturn('Deutsch');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid code for the language');

		LanguageRules::update($language);
	}

	/**
	 * @covers ::update
	 */
	public function testUpdateWithoutName()
	{
		$language = $this->createMock(Language::class);
		$language->method('code')->willReturn('de');
		$language->method('name')->willReturn('');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid name for the language');

		LanguageRules::update($language);
	}

	/**
	 * @covers ::update
	 */
	public function testUpdateWithoutCurrentUser()
	{
		$language = new Language([
			'code' => 'de',
			'name' => 'Deutsch'
		]);

		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('You are not allowed to update the language');

		LanguageRules::update($language);
	}

	/**
	 * @covers ::update
	 */
	public function testUpdateWithoutPermissions()
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

	/**
	 * @covers ::update
	 */
	public function testUpdateDemoteDefault()
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
