<?php

namespace Kirby\Query;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Toolkit\I18n;

/**
 * @coversDefaultClass Kirby\Query\Query
 */
class QueryDefaultFunctionsTest extends \PHPUnit\Framework\TestCase
{
	public function testKirby()
	{
		$query = new Query('kirby');
		$this->assertInstanceOf(App::class, $query->resolve());

		$query = new Query('kirby.site');
		$this->assertInstanceOf(Site::class, $query->resolve());
	}

	public function testCollection()
	{
		new App([
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			],
			'collections' => [
				'test' => function ($pages) {
					return $pages;
				}
			]
		]);

		$query = new Query('collection("test")');
		$collection = $query->resolve();
		$this->assertInstanceOf(Pages::class, $collection);
		$this->assertCount(1, $collection);
	}

	public function testFile()
	{
		new App([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'files' => [
							['filename' => 'test.jpg']
						]
					]
				],
				'files' => [
					['filename' => 'test.jpg']
				]
			]
		]);

		$query = new Query('file("test.jpg")');
		$this->assertInstanceOf(File::class, $query->resolve());

		$query = new Query('file("a/test.jpg")');
		$this->assertInstanceOf(File::class, $query->resolve());

		$query = new Query('file("b/test.jpg")');
		$this->assertNull($query->resolve());
	}

	public function testPage()
	{
		$app = new App([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
					],
					[
						'slug'    => 'b',
						'content' => ['uuid' => 'test']
					]
				]
			]
		]);

		$a = $app->page('a');
		$b = $app->page('b');

		$query = new Query('page("a")');
		$this->assertSame($a, $query->resolve());

		$query = new Query('page("a").slug');
		$this->assertSame('a', $query->resolve(['slug' => 'foo']));

		$query = new Query('page("page://test")');
		$this->assertSame($b, $query->resolve(['page' => $b]));

		$query = new Query('page("c")');
		$this->assertNull($query->resolve());
	}

	public function testSite()
	{
		new App([
			'site' => [
				'children' => [
					['slug' => 'a']
				]
			]
		]);

		$query = new Query('site');
		$this->assertInstanceOf(Site::class, $query->resolve());

		$query = new Query('site.children.first');
		$this->assertInstanceOf(Page::class, $query->resolve());
	}

	public function testT()
	{
		I18n::$translations = [
			'en' => ['add' => 'Add'],
			'de' => ['add' => 'Hinzufügen']
		];

		$query = new Query('t("add")');
		$this->assertSame('Add', $query->resolve());

		$query = new Query('t("notfound", "fallback")');
		$this->assertSame('fallback', $query->resolve());

		$query = new Query('t("add", null, "de")');
		$this->assertSame('Hinzufügen', $query->resolve());

		I18n::$translations = [];
	}

	public function testUser()
	{
		new App([
			'users' => [
				['id' => 'user-a', 'email' => 'foo@bar.com']
			]
		]);

		$query = new Query('user("user://user-a")');
		$this->assertInstanceOf(User::class, $query->resolve());

		$query = new Query('user("user://user-a").email');
		$this->assertSame('foo@bar.com', $query->resolve());

		$query = new Query('user("user-b")');
		$this->assertNull($query->resolve());
	}
}
