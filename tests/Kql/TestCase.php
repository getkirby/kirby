<?php

namespace Kirby\Kql;

use Kirby\Cms\App;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestObject
{
	public function foo(string|int $bar = 'hello'): array
	{
		return [$bar];
	}

	public function more(): string
	{
		return 'no';
	}

	/**
	 * @kql-allowed
	 */
	public function homer()
	{
	}
}

class TestObjectWithMethods extends TestObject
{
	public static array $methods = [];

	public function toArray()
	{
		return [];
	}
}

class TestObjectWithMethodsAsChild extends TestObjectWithMethods
{
	public static array $methods = [];
}

class TestCase extends BaseTestCase
{
	protected App $app;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'children' => [
					[
						'slug' => 'projects'
					],
					[
						'slug' => 'about'
					],
					[
						'slug' => 'contact'
					]
				],
				'content' => [
					'title' => 'Test Site'
				],
			]
		]);
	}
}
