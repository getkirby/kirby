<?php

namespace Kirby\Kql;

use Exception;
use Kirby\Cms\Page;
use Kirby\Exception\PermissionException;

/**
 * @coversDefaultClass \Kirby\Kql\Kql
 */
class KqlTest extends TestCase
{
	/**
	 * @covers ::fetch
	 */
	public function testFetch()
	{
		$object = new TestObject();
		$result = Kql::fetch($object, 'more', true);
		$this->assertSame('no', $result);

		$object = new Page(['slug' => 'test']);
		$result = Kql::fetch($object, 'slug', []);
		$this->assertSame('test', $result);

		$object = new Page(['slug' => 'test']);
		$result = Kql::fetch($object, null, ['query' => 'page.slug']);
		$this->assertSame('test', $result);
	}

	/**
	 * @covers ::help
	 */
	public function testHelp()
	{
		$result = Kql::help('foo');
		$this->assertSame(['type' => 'string', 'value' => 'foo'], $result);
	}

	/**
	 * @covers ::query
	 */
	public function testQuery()
	{
		$result = Kql::run([
			'query'  => 'site.children',
			'select' => 'slug'
		]);

		$expected = [
			['slug' => 'projects'],
			['slug' => 'about'],
			['slug' => 'contact']
		];

		$this->assertSame($expected, $result);
	}

	/**
	 * @covers ::render
	 */
	public function testRender()
	{
		// non-object: returns value directly
		$result = Kql::render('foo');
		$this->assertSame('foo', $result);

		// intercepted object
		$object = new Page(['slug' => 'test']);
		$result = Kql::render($object);
		$this->assertIsArray($result);
	}

	/**
	 * @covers ::render
	 */
	public function testRenderOriginalObject()
	{
		$this->app->clone([
			'options' => [
				'kql' => ['classes' => ['allowed' => [
					'Kirby\Kql\TestObject',
					'Kirby\Kql\TestObjectWithMethods'
				]]]
			]
		]);

		$object = new TestObjectWithMethods();
		$result = Kql::render($object);
		$this->assertIsArray($result);

		$object = new TestObject();
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('The object "Kirby\Kql\TestObject" cannot be rendered. Try querying one of its methods instead.');
		Kql::render($object);
	}

	/**
	 * @covers ::run
	 */
	public function testRun()
	{
		$result   = Kql::run('site.title');
		$expected = 'Test Site';
		$this->assertSame($expected, $result);

		$result = Kql::run(['queries' => ['site.title']]);
		$this->assertSame([$expected], $result);

		$result = Kql::run(['query' => 'site', 'select' => 'title']);
		$this->assertSame(['title' => $expected], $result);
	}

	/**
	 * @covers ::run
	 */
	public function testRunInvalidQuery()
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('The query must be a string');
		Kql::run(['query' => false]);
	}

	/**
	 * @covers ::run
	 */
	public function testRunForbiddenMethod()
	{
		$this->expectException(PermissionException::class);
		$this->expectExceptionMessage('The method "Kirby\Cms\Page::delete()" is not allowed in the API context');
		Kql::run('site.children.first.delete');
	}

	/**
	 * @covers ::select
	 */
	public function testSelect()
	{
		// no select, returns data via ::render
		$result = Kql::select('foo');
		$this->assertSame('foo', $result);

		// help
		$result = Kql::select('foo', '?');
		$this->assertSame(['type' => 'string', 'value' => 'foo'], $result);
	}

	/**
	 * @covers ::select
	 */
	public function testSelectWithAlias()
	{
		$result = Kql::run([
			'select' => [
				'myTitle' => 'site.title'
			]
		]);

		$this->assertSame(['myTitle' => 'Test Site'], $result);
	}

	/**
	 * @covers ::select
	 * @covers ::selectFromArray
	 */
	public function testSelectFromArray()
	{
		$data = [
			'title' => 'Test Site',
			'url'   => '/'
		];

		$result = Kql::select($data, ['title' => true, 'url' => false]);
		$this->assertSame(['title' => 'Test Site'], $result);

		$result = Kql::select($data, ['title']);
		$this->assertSame(['title' => 'Test Site'], $result);
	}

	/**
	 * @covers ::select
	 * @covers ::selectFromCollection
	 */
	public function testSelectFromCollection()
	{
		$result = Kql::run([
			'select' => [
				'children' => [
					'query'      => 'site.children',
					'select'     => 'slug',
					'pagination' =>  ['limit' => 2]
				]
			]
		]);

		$this->assertCount(2, $result['children']['data']);
		$this->assertSame(2, $result['children']['pagination']['limit']);
	}

	/**
	 * @covers ::select
	 * @covers ::selectFromObject
	 */
	public function testSelectFromObject()
	{
		$result = Kql::run([
			'select' => [
				'test' => [
					'query'  => 'site.page("about")',
					'select' => ['url' => true, 'slug' => false],
				]
			]
		]);

		$this->assertSame('/about', $result['test']['url']);
	}

	/**
	 * @covers ::select
	 */
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

	/**
	 * @covers ::select
	 * @covers ::selectFromCollection
	 * @covers ::selectFromObject
	 */
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

	/**
	 * @covers ::select
	 */
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
