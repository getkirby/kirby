<?php

namespace Kirby\Form\Fields;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Form\Field;
use Kirby\Form\Fields;
use Kirby\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
	protected $app;

	public function setUp(): void
	{
		// start with a fresh set of fields
		Field::$types = [];

		$this->setUpTmp();

		$this->app = new App([
			'roots' => [
				'index' => $this->hasTmp() ? static::TMP : '/dev/null'
			],
			'urls' => [
				'index' => 'https://getkirby.com/subfolder'
			]
		]);
	}

	public function tearDown(): void
	{
		$this->tearDownTmp();
	}

	public function app()
	{
		return $this->app;
	}

	/**
	 * App with an editor who must not list the pages `b` and `c`
	 */
	protected function appWithProtectedPages(): App
	{
		// the permission cache is keyed by template and role,
		// so both need to be unique for each test
		$uuid = uuid();

		return new App([
			'blueprints' => [
				'pages/secret-' . $uuid => [
					'options' => ['list' => false]
				]
			],
			'roles' => [
				['name' => 'editor-' . $uuid]
			],
			'site' => [
				'children' => [
					['slug' => 'a'],
					[
						'slug'     => 'b',
						'template' => 'secret-' . $uuid
					],
					[
						'slug'     => 'c',
						'template' => 'secret-' . $uuid
					]
				]
			],
			'users' => [
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor-' . $uuid
				]
			],
			'user' => 'editor@getkirby.com'
		]);
	}

	public function field(string $type, array $attr = [], Fields|null $formFields = null)
	{
		$page = new Page(['slug' => 'test']);
		return Field::factory($type, array_merge(['model' => $page], $attr), $formFields);
	}
}
