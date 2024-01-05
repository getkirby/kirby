<?php

namespace Kirby\Form\Fields;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Filesystem\Dir;
use Kirby\Form\Field;
use Kirby\Form\Fields;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
	protected $app;

	public function setUp(): void
	{
		// start with a fresh set of fields
		Field::$types = [];

		if ($this->hasTmp() === true) {
			Dir::make(static::TMP);
		}

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
		if ($this->hasTmp() === true) {
			Dir::remove(static::TMP);
		}
	}

	public function app()
	{
		return $this->app;
	}

	public function field(string $type, array $attr = [], ?Fields $formFields = null)
	{
		$page = new Page(['slug' => 'test']);
		return Field::factory($type, array_merge(['model' => $page], $attr), $formFields);
	}

	/**
	 * Checks if the test class extending this test case class
	 * has defined a temporary directory
	 */
	protected function hasTmp(): bool
	{
		return defined(get_class($this) . '::TMP');
	}
}
