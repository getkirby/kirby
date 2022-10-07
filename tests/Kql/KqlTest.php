<?php

namespace Kirby\Kql;

use Kirby\Cms\App;
use Kirby\Exception\PermissionException;
use PHPUnit\Framework\TestCase;

class KqlTest extends TestCase
{
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

	public function testForbiddenMethod()
	{
		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('The method "Kirby\Cms\Page::delete()" is not allowed in the API context');
		$result = Kql::run('site.children.first.delete');
	}

	public function testRun()
	{
		$result   = Kql::run('site.title');
		$expected = 'Test Site';

		$this->assertSame($expected, $result);
	}

	public function testQuery()
	{
		$result = Kql::run([
			'query'  => 'site.children',
			'select' => 'slug'
		]);

		$expected = [
			[
				'slug' => 'projects',
			],
			[
				'slug' => 'about',
			],
			[
				'slug' => 'contact',
			]
		];

		$this->assertSame($expected, $result);
	}

	public function testSelectWithAlias()
	{
		$result = Kql::run([
			'select' => [
				'myTitle' => 'site.title'
			]
		]);

		$expected = [
			'myTitle' => 'Test Site',
		];

		$this->assertSame($expected, $result);
	}

	public function testSelectWithArray()
	{
		$result = Kql::run([
			'select' => ['title', 'url']
		]);

		$expected = [
			'title' => 'Test Site',
			'url'   => '/'
		];

		$this->assertSame($expected, $result);
	}

	public function testSelectWithBoolean()
	{
		$result = Kql::run([
			'select' => [
				'title' => true
			]
		]);

		$expected = [
			'title' => 'Test Site'
		];

		$this->assertSame($expected, $result);
	}

	public function testSelectWithQuery()
	{
		$result = Kql::run([
			'select' => [
				'children' => [
					'query'  => 'site.children',
					'select' => 'slug'
				]
			]
		]);

		$expected = [
			'children' => [
				[
					'slug' => 'projects',
				],
				[
					'slug' => 'about',
				],
				[
					'slug' => 'contact',
				]
			]
		];

		$this->assertSame($expected, $result);
	}

	public function testSelectWithString()
	{
		$result = Kql::run([
			'select' => [
				'title' => 'site.title.upper'
			]
		]);

		$expected = [
			'title' => 'TEST SITE'
		];

		$this->assertSame($expected, $result);
	}
}
