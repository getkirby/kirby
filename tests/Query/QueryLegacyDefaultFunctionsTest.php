<?php

namespace Kirby\Query;

use Kirby\Cms\App;
use Kirby\Cms\Pages;
use Kirby\Filesystem\Dir;
use Kirby\Image\QrCode;
use Kirby\TestCase;
use Kirby\Toolkit\I18n;

class QueryLegacyDefaultFunctionsTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Query.QueryLegacyDefaultFunctions';

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testKirby(): void
	{
		$query = new Query('kirby');
		$this->assertInstanceOf(App::class, $query->resolve());

		$query = new Query('kirby.site');
		$this->assertIsSite($query->resolve());
	}

	public function testCollection(): void
	{
		new App([
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			],
			'collections' => [
				'test' => fn ($pages) => $pages
			]
		]);

		$query = new Query('collection("test")');
		$collection = $query->resolve();
		$this->assertInstanceOf(Pages::class, $collection);
		$this->assertCount(1, $collection);
	}

	public function testFile(): void
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

	public function testPage(): void
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

	public function testqr(): void
	{
		$query = new Query('qr("https://getkirby.com")');
		$qr = $query->resolve();
		$this->assertInstanceOf(QrCode::class, $qr);
		$this->assertSame('https://getkirby.com', $qr->data);
	}

	public function testSite(): void
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

	public function testT(): void
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

	public function testUser(): void
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

	public function testUsers(): void
	{
		new App([
			'users' => [
				['id' => 'user-a', 'email' => 'foo@getkirby.com'],
				['id' => 'user-b', 'email' => 'bar@getkirby.com']
			]
		]);

		$query = new Query('users()');
		$this->assertCount(2, $query->resolve());
	}
}
