<?php

namespace Kirby\Panel\Controller\Dropdown;

use Kirby\Cms\App;
use Kirby\Cms\Language;
use Kirby\Cms\Page;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguagesDropdownController::class)]
class LanguagesDropdownControllerTest extends TestCase
{
	public function testFactoryForPageFile(): void
	{
		$app = new App([
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'files' => [
							['filename' => 'test.jpg']
						]
					]
				]
			]
		]);

		$app->impersonate('kirby');

		$dropdown = LanguagesDropdownController::factory('pages/test', 'test.jpg');
		$this->assertInstanceOf(LanguagesDropdownController::class, $dropdown);
		$this->assertSame($app->file('test/test.jpg'), $dropdown->model);
	}

	public function testFactoryForSiteFile(): void
	{
		$app = new App([
			'site' => [
				'files' => [
					['filename' => 'test.jpg']
				]
			]
		]);

		$app->impersonate('kirby');

		$dropdown = LanguagesDropdownController::factory('', 'test.jpg');
		$this->assertInstanceOf(LanguagesDropdownController::class, $dropdown);
		$this->assertSame($app->file('test.jpg'), $dropdown->model);
	}

	public function testFactoryForUserFile(): void
	{
		$app = new App([
			'users' => [
				[
					'id'    => 'test',
					'files' => [
						['filename' => 'test.jpg']
					]
				]
			]
		]);

		$app->impersonate('kirby');

		$dropdown = LanguagesDropdownController::factory('users/test', 'test.jpg');
		$this->assertInstanceOf(LanguagesDropdownController::class, $dropdown);
		$this->assertSame($app->user('test')->file('test.jpg'), $dropdown->model);
	}

	public function testFactoryForPage(): void
	{
		$app = new App([
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			]
		]);

		$app->impersonate('kirby');

		$dropdown = LanguagesDropdownController::factory('test');
		$this->assertInstanceOf(LanguagesDropdownController::class, $dropdown);
		$this->assertSame($app->page('test'), $dropdown->model);
	}

	public function testFactoryForSite(): void
	{
		$app = new App();
		$app->impersonate('kirby');
		$dropdown = LanguagesDropdownController::factory();
		$this->assertInstanceOf(LanguagesDropdownController::class, $dropdown);
		$this->assertSame($app->site(), $dropdown->model);
	}

	public function testLoadSingleLang(): void
	{
		$page     = new Page(['slug' => 'test']);
		$dropdown = new LanguagesDropdownController($page);
		$this->assertSame([], $dropdown->load());
	}

	public function testLoadMultiLang(): void
	{
		new App([
			'options' => [
				'languages' => true
			],
			'languages' => [
				'en' => [
					'code'    => 'en',
					'default' => true,
					'name'    => 'English'
				],
				'de' => [
					'code'    => 'de',
					'default' => false,
					'name'    => 'Deutsch'
				]
			]
		]);

		$page     = new Page(['slug' => 'test']);
		$dropdown = new LanguagesDropdownController($page);
		$this->assertSame([
			[
				'text'    => 'English',
				'code'    => 'en',
				'current' => true,
				'default' => true,
				'changes' => false,
				'lock'    => false
			],
			'-',
			[
				'text'    => 'Deutsch',
				'code'    => 'de',
				'current' => false,
				'default' => false,
				'changes' => false,
				'lock'    => false
			]
		], $dropdown->load());
	}

	public function testOption(): void
	{
		$page     = new Page(['slug' => 'test']);
		$dropdown = new LanguagesDropdownController($page);
		$language = new Language(['name' => 'Deutsch', 'code' => 'de']);
		$this->assertSame([
			'text'    => 'Deutsch',
			'code'    => 'de',
			'current' => false,
			'default' => false,
			'changes' => false,
			'lock'    => false
		], $dropdown->option($language));
	}
}
