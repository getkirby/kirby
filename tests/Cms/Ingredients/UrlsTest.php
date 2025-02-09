<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\DataProvider;

class UrlsTest extends TestCase
{
	public static function defaultUrlProvider(): array
	{
		return [
			['/',      'index'],
			['/media', 'media'],
			['/api',   'api'],
		];
	}

	#[DataProvider('defaultUrlProvider')]
	public function testDefaulUrl(string $url, string $method)
	{
		$app  = new App([
			'roots' => [
				'index' => __DIR__
			]
		]);

		$urls = $app->urls();

		$this->assertSame($url, $urls->$method());
	}

	public static function customBaseUrlProvider(): array
	{
		return [
			['https://getkirby.com',       'index'],
			['https://getkirby.com/media', 'media'],
		];
	}

	#[DataProvider('customBaseUrlProvider')]
	public function testWithCustomBaseUrl(string $url, string $method)
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'urls' => [
				'index' => 'https://getkirby.com'
			]
		]);

		$urls = $app->urls();
		$this->assertSame($url, $urls->$method());
	}

	public static function customUrlProvider(): array
	{
		return [
			['https://getkirby.com',       'index'],
			['https://cdn.getkirby.com',   'media'],
		];
	}

	#[DataProvider('customUrlProvider')]
	public function testWithCustomUrl(string $url, string $method)
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'urls' => [
				'index' => 'https://getkirby.com',
				'media' => 'https://cdn.getkirby.com',
			]
		]);

		$urls = $app->urls();
		$this->assertSame($url, $urls->$method());
	}

	public function testCurrent()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
		]);

		$this->assertSame('/', $app->url('current'));
	}

	public function testCurrentInSubfolderSetup()
	{
		$app = $this->app->clone([
			'cli' => false,
			'server' => [
				'SERVER_NAME' => 'localhost',
				'SCRIPT_NAME' => '/starterkit/index.php'
			]
		]);

		$this->assertSame('http://localhost/starterkit', $app->url('index'));
		$this->assertSame('http://localhost/starterkit', $app->url('current'));

		$app = $this->app->clone([
			'cli' => false,
			'server' => [
				'SERVER_NAME' => 'localhost',
				'REQUEST_URI' => '/starterkit/sub/folder',
				'SCRIPT_NAME' => '/starterkit/index.php'
			]
		]);

		$this->assertSame('http://localhost/starterkit', $app->url('index'));
		$this->assertSame('http://localhost/starterkit/sub/folder', $app->url('current'));
	}

	public function testCurrentWithCustomIndex()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'urls' => [
				'index' => 'http://getkirby.com'
			]
		]);

		$this->assertSame('http://getkirby.com', $app->url('current'));
	}

	public function testCurrentWithCustomPath()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'path' => 'test/path'
		]);

		$this->assertSame('/test/path', $app->url('current'));
	}

	public function testCurrentWithCustomPathAndCustomIndex()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'urls' => [
				'index' => 'http://getkirby.com',
			],
			'path' => 'test/path'
		]);

		$this->assertSame('http://getkirby.com/test/path', $app->url('current'));
	}
}
