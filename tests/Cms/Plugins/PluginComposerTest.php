<?php

namespace Kirby\Cms;

use Composer\Autoload\ClassLoader;
use PHPUnit\Framework\TestCase;

class PluginComposerTest extends TestCase
{
	public static function setUpBeforeClass(): void
	{
		(new ClassLoader(__DIR__ . '/fixtures/plugin-version-composer'))->register();
	}

	public static function tearDownAfterClass(): void
	{
		(new ClassLoader(__DIR__ . '/fixtures/plugin-version-composer'))->unregister();
	}

	public function setUp(): void
	{
		App::destroy();
	}

	public function tearDown(): void
	{
		App::destroy();
	}

	public function testVersion()
	{
		$plugin = new Plugin('getkirby/test-plugin', [
			'root' => __DIR__ . '/fixtures/plugin-version-composer'
		]);

		$this->assertSame('5.2.3', $plugin->version());
	}
}
