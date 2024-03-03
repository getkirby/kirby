<?php

namespace Kirby\Cms\Api;

use Kirby\Cms\App;
use Kirby\Cms\TestCase as TestCase;

class ApiModelTestCase extends TestCase
{
	protected $api;
	protected $app;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => $this->hasTmp() ? static::TMP : '/dev/null',
			],
		]);

		$this->api = $this->app->api();
	}

	public function tearDown(): void
	{
		App::destroy();
		$this->tearDownTmp();
	}

	public function attr($object, $attr)
	{
		return $this->api->resolve($object)->select($attr)->toArray()[$attr];
	}

	public function assertAttr($object, $attr, $value): void
	{
		$this->assertSame($this->attr($object, $attr), $value);
	}
}
