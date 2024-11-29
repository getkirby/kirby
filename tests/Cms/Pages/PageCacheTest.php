<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class PageCacheTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageCache';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug' => 'one'
					],
					[
						'slug' => 'another'
					]
				]
			],
			'options' => [
				'cache.pages' => true
			]
		]);
	}

	public static function requestMethodProvider(): array
	{
		return [
			['GET', true],
			['HEAD', true],
			['POST', false],
			['DELETE', false],
			['PATCH', false],
			['PUT', false],
		];
	}

	/**
	 * @dataProvider requestMethodProvider
	 */
	public function testRequestMethod($method, $expected)
	{
		$app = $this->app->clone([
			'request' => [
				'method' => $method
			]
		]);

		$this->assertSame($expected, $app->page('one')->isCacheable());
	}

	/**
	 * @dataProvider requestMethodProvider
	 */
	public function testRequestData($method)
	{
		$app = $this->app->clone([
			'request' => [
				'method' => $method,
				'query'  => ['foo' => 'bar']
			]
		]);

		$this->assertFalse($app->page('one')->isCacheable());
	}

	public function testRequestParams()
	{
		$app = $this->app->clone([
			'request' => [
				'url' => 'https://getkirby.com/blog/page:2'
			]
		]);

		$this->assertFalse($app->page('one')->isCacheable());
	}

	public function testIgnoreId()
	{
		$app = $this->app->clone([
			'options' => [
				'cache.pages' => [
					'ignore' => [
						'another'
					]
				]
			]
		]);

		$this->assertTrue($app->page('one')->isCacheable());
		$this->assertFalse($app->page('another')->isCacheable());
	}

	public function testIgnoreCallback()
	{
		$app = $this->app->clone([
			'options' => [
				'cache.pages' => [
					'ignore' => fn ($page) => $page->id() === 'one'
				]
			]
		]);

		$this->assertFalse($app->page('one')->isCacheable());
		$this->assertTrue($app->page('another')->isCacheable());
	}

	public function testDisabledCache()
	{
		// deactivate on top level
		$app = $this->app->clone([
			'options' => [
				'cache.pages' => false
			]
		]);

		$this->assertFalse($app->page('one')->isCacheable());

		// deactivate in array
		$app = $this->app->clone([
			'options' => [
				'cache.pages' => [
					'active' => false
				]
			]
		]);

		$this->assertFalse($app->page('one')->isCacheable());
	}
}
