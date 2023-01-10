<?php

namespace Kirby\Cms;

class UrlsTest extends TestCase
{
	public function defaultUrlProvider(): array
	{
		return [
			['/',      'index'],
			['/media', 'media'],
			['/api',   'api'],
		];
	}

	/**
	 * @dataProvider defaultUrlProvider
	 */
	public function testDefaulUrl($url, $method)
	{
		$app  = new App([
			'roots' => [
				'index' => __DIR__
			]
		]);

		$urls = $app->urls();

		$this->assertEquals($url, $urls->$method());
	}

	public function customBaseUrlProvider(): array
	{
		return [
			['https://getkirby.com',       'index'],
			['https://getkirby.com/media', 'media'],
		];
	}

	/**
	 * @dataProvider customBaseUrlProvider
	 */
	public function testWithCustomBaseUrl($url, $method)
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
		$this->assertEquals($url, $urls->$method());
	}

	public function customUrlProvider(): array
	{
		return [
			['https://getkirby.com',       'index'],
			['https://cdn.getkirby.com',   'media'],
		];
	}

	/**
	 * @dataProvider customUrlProvider
	 */
	public function testWithCustomUrl($url, $method)
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
		$this->assertEquals($url, $urls->$method());
	}

	public function testCurrent()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
		]);

		$this->assertEquals('/', $app->url('current'));
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

		$this->assertEquals('http://localhost/starterkit', $app->url('index'));
		$this->assertEquals('http://localhost/starterkit', $app->url('current'));

		$app = $this->app->clone([
			'cli' => false,
			'server' => [
				'SERVER_NAME' => 'localhost',
				'REQUEST_URI' => '/starterkit/sub/folder',
				'SCRIPT_NAME' => '/starterkit/index.php'
			]
		]);

		$this->assertEquals('http://localhost/starterkit', $app->url('index'));
		$this->assertEquals('http://localhost/starterkit/sub/folder', $app->url('current'));
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

		$this->assertEquals('http://getkirby.com', $app->url('current'));
	}

	public function testCurrentWithCustomPath()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'path' => 'test/path'
		]);

		$this->assertEquals('/test/path', $app->url('current'));
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

		$this->assertEquals('http://getkirby.com/test/path', $app->url('current'));
	}
}
