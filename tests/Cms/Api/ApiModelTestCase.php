<?php

namespace Kirby\Cms\Api;

use Kirby\Cms\App;
use Kirby\Cms\TestCase as TestCase;
use Kirby\Filesystem\Dir;

class ApiModelTestCase extends TestCase
{
	protected $api;
	protected $app;
	protected $tmp = __DIR__ . '/tmp';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => $this->tmp,
			],
		]);

		$this->api = $this->app->api();
		Dir::make($this->tmp);
	}

	public function tearDown(): void
	{
		Dir::remove($this->tmp);
	}

	public function attr($object, $attr)
	{
		return $this->api->resolve($object)->select($attr)->toArray()[$attr];
	}

	public function assertAttr($object, $attr, $value)
	{
		$this->assertSame($this->attr($object, $attr), $value);
	}
}
