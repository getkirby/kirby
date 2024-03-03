<?php

namespace Kirby\Query;

use Kirby\Cms\App;
use Kirby\Cms\Pages;
use Kirby\Filesystem\Dir;
use Kirby\Image\QrCode;
use Kirby\Toolkit\I18n;

class QueryDefaultFunctionsTest extends \Kirby\TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Query.QueryDefaultFunctions';

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testKirby()
	{
		$query = new Query('kirby');
		$this->assertInstanceOf(App::class, $query->resolve());

		$query = new Query('kirby.site');
		$this->assertIsSite($query->resolve());
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
		$this->assertIsFile($query->resolve());

		$query = new Query('file("a/test.jpg")');
		$this->assertIsFile($query->resolve());

		$query = new Query('file("b/test.jpg")');
		$this->assertNull($query->resolve());
	}

	public function testPage()
	{
		$app = new App([
			'roots' => [
				'index' => static::TMP
			],
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

	public function testqr()
	{
		$query = new Query('qr("https://getkirby.com")');
		$qr = $query->resolve();
		$this->assertInstanceOf(QrCode::class, $qr);
		$this->assertSame('https://getkirby.com', $qr->data);
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
		$this->assertIsSite($query->resolve());

		$query = new Query('site.children.first');
		$this->assertIsPage($query->resolve());
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
		$this->assertIsUser($query->resolve());

		$query = new Query('user("user://user-a").email');
		$this->assertSame('foo@bar.com', $query->resolve());

		$query = new Query('user("user-b")');
		$this->assertNull($query->resolve());
	}
}
