<?php

namespace Kirby\Form\Field;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Form\Field;
use Kirby\Form\Fields;
use Kirby\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
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

	public function field(
		string $type,
		array $attr = [],
		Fields|null $formFields = null
	): Field|BaseField {
		$page = new Page(['slug' => 'test']);
		return Field::factory($type, ['model' => $page, ...$attr], $formFields);
	}
}
