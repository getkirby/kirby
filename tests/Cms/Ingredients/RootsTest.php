<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\DataProvider;

class RootsTest extends TestCase
{
	protected string|null $indexRoot;

	public function setUp(): void
	{
		$this->indexRoot = Core::$indexRoot;
	}

	public function tearDown(): void
	{
		// ensure that the index root used for testing is reset
		Core::$indexRoot = $this->indexRoot;
	}

	protected static function rootProvider(string $index): array
	{
		$kirby = realpath(__DIR__ . '/../../..');

		return [
			[$kirby, 'kirby'],
			[$kirby . '/i18n', 'i18n'],
			[$kirby . '/i18n/translations', 'i18n:translations'],
			[$kirby . '/i18n/rules', 'i18n:rules'],
			[$index, 'index'],
			[$index . '/assets', 'assets'],
			[$index . '/content', 'content'],
			[$index . '/media', 'media'],
			[$kirby . '/panel', 'panel'],
			[$site = $index . '/site', 'site'],
			[$site . '/accounts', 'accounts'],
			[$site . '/blueprints', 'blueprints'],
			[$site . '/cache', 'cache'],
			[$site . '/collections', 'collections'],
			[$site . '/config', 'config'],
			[$site . '/config/.license', 'license'],
			[$site . '/controllers', 'controllers'],
			[$site . '/languages', 'languages'],
			[$site . '/logs', 'logs'],
			[$site . '/models', 'models'],
			[$site . '/plugins', 'plugins'],
			[$site . '/sessions', 'sessions'],
			[$site . '/snippets', 'snippets'],
			[$site . '/templates', 'templates'],
			[$site . '/blueprints/users', 'roles'],
		];
	}

	public static function defaultRootProvider(): array
	{
		return static::rootProvider(realpath(__DIR__ . '/../../../../'));
	}

	#[DataProvider('defaultRootProvider')]
	public function testDefaultRoot($root, $method)
	{
		// fake the default behavior for this test
		Core::$indexRoot = null;

		$roots = (new App())->roots();

		$this->assertSame($root, $roots->$method());
	}

	public static function customIndexRootProvider(): array
	{
		return static::rootProvider('/var/www/getkirby.com');
	}

	#[DataProvider('customIndexRootProvider')]
	public function testCustomIndexRoot($root, $method)
	{
		$app = new App([
			'roots' => [
				'index' => '/var/www/getkirby.com'
			]
		]);

		$roots = $app->roots();

		$this->assertSame($root, $roots->$method());
	}

	public static function customRootProvider(): array
	{
		$base    = '/var/www/getkirby.com';
		$public  = $base . '/public';

		return [
			[$public, 'index'],
			[$public . '/media', 'media'],
			[$base . '/content', 'content'],
			[$base . '/site', 'site'],
			[$base . '/site/config', 'config'],
		];
	}

	#[DataProvider('customRootProvider')]
	public function testCustomRoot(string $root, string $method)
	{
		// public directory setup
		$base   = '/var/www/getkirby.com';
		$public = $base . '/public';

		$app = new App([
			'roots' => [
				'index'   => $public,
				'media'   => $public . '/media',
				'content' => $base . '/content',
				'site'    => $base . '/site'
			]
		]);

		$roots = $app->roots();

		$this->assertSame($root, $roots->$method());
	}
}
