<?php

namespace Kirby\Cms;

use Kirby\Exception\LogicException;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(App::class)]
class AppResolveTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.AppResolve';

	public function testResolve(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			]
		]);

		// the resolver result is passed through
		$result = $app->resolve('test');

		$this->assertIsPage($result);
		$this->assertSame('test', $result->id());
	}

	public function testResolveMissingPageReturnsNull(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			]
		]);

		// a NotFoundException from the resolver is turned into null
		// so the router can fall through to the error page
		$this->assertNull($app->resolve('does-not-exist'));
	}

	public function testResolveInaccessibleFileReturnsNull(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'test',
						'files' => [
							['filename' => 'test.jpg']
						]
					]
				]
			]
		]);

		// file redirects are disabled by default, so the file
		// is not resolvable and null is returned
		$this->assertNull($app->resolve('test/test.jpg'));
	}

	public function testResolveMissingHomePageThrows(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'children' => []
			]
		]);

		// a missing home page is a hard error that is not caught
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The home page does not exist');

		$app->resolve(null);
	}

	public function testResolveWithLanguage(): void
	{
		F::write(static::TMP . '/test.php', 'html');
		F::write(static::TMP . '/test.xml.php', 'xml');

		$app = new App([
			'roots' => [
				'index'     => '/dev/null',
				'templates' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'template' => 'test'
					]
				],
			],
			'options' => [
				'languages' => true
			],
			'languages' => [
				[
					'code'    => 'de',
					'default' => true,
					'url'     => '/'
				],
				[
					'code' => 'en',
				]
			]
		]);

		// resolving sets the current language (default)
		$result = $app->resolve('test.xml');
		$this->assertInstanceOf(Responder::class, $result);
		$this->assertSame('xml', $result->body());
		$this->assertSame('de', $app->language()->code());

		// the language argument switches the current language
		$result = $app->resolve('test.xml', 'en');
		$this->assertInstanceOf(Responder::class, $result);
		$this->assertSame('xml', $result->body());
		$this->assertSame('en', $app->language()->code());

		// a missing representation still returns null in the given language
		$this->assertNull($app->resolve('test.json', 'en'));
		$this->assertSame('en', $app->language()->code());
	}

	public function testResolveFile(): void
	{
		$props = [
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'children' => [
					[
						'slug'  => 'test',
						'files' => [
							['filename' => 'test.jpg']
						]
					]
				]
			]
		];

		// disabled by default
		$app = new App($props);
		$this->assertNull($app->resolveFile($app->page('test')->file('test.jpg')));

		// enabled
		$app = new App([
			...$props,
			'options' => ['content' => ['fileRedirects' => true]]
		]);
		$file = $app->page('test')->file('test.jpg');
		$this->assertSame($file, $app->resolveFile($file));

		// non-existing file
		$this->assertNull($app->resolveFile(null));
	}
}
