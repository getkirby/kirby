<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class KirbyTextTest extends TestCase
{
	public function testBeforeHook()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'hooks' => [
				'kirbytext:before' => function ($text) {
					return strtolower($text);
				}
			]
		]);

		$this->assertSame('<p>test</p>', $app->kirbytext('Test'));
		// Let's see if it works twice
		$this->assertSame('<p>test</p>', $app->kirbytext('Test'));
	}

	public function testAfterHook()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'hooks' => [
				'kirbytext:after' => function ($text) {
					return strip_tags($text);
				}
			]
		]);

		$this->assertSame('Test', $app->kirbytext('Test'));
		// Let's see if it works twice
		$this->assertSame('Test', $app->kirbytext('Test'));
	}
}
