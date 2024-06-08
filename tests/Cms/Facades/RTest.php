<?php

namespace Kirby\Cms;

class RTest extends TestCase
{
	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);
	}

	public function testInstance()
	{
		$this->assertSame($this->app->request(), R::instance());
	}
}
