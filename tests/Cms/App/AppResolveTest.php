<?php

namespace Kirby\Cms;

use Kirby\Filesystem\F;

class AppResolveTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';
	public const TMP      = KIRBY_TMP_DIR . '/Cms.AppResolve';

	public function testResolveHomePage()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'children' => [
					[
						'slug' => 'home'
					]
				]
			]
		]);

		$result = $app->resolve(null);

		$this->assertIsPage($result);
		$this->assertTrue($result->isHomePage());
	}

	public function testResolveMainPage()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test'
					]
				]
			]
		]);

		$result = $app->resolve('test');

		$this->assertIsPage($result);
		$this->assertSame('test', $result->id());
	}

	public function testResolveSubPage()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'children' => [
							['slug' => 'subpage']
						]
					]
				]
			]
		]);

		$result = $app->resolve('test/subpage');

		$this->assertIsPage($result);
		$this->assertSame('test/subpage', $result->id());
	}

	public function testResolvePageRepresentation()
	{
		F::write($template = static::TMP . '/test.php', 'html');
		F::write($template = static::TMP . '/test.xml.php', 'xml');
		F::write(
			$template = static::TMP . '/test.png.php',
			'<?php $kirby->response()->type("image/jpeg"); ?>png'
		);

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
			]
		]);

		// missing representation
		$result = $app->resolve('test.json');
		$this->assertNull($result);
		$result = $app->resolve('test.');
		$this->assertNull($result);

		// xml representation
		$result = $app->clone()->resolve('test.xml');
		$this->assertInstanceOf(Responder::class, $result);
		$this->assertSame('text/xml', $result->type());
		$this->assertSame('xml', $result->body());

		// representation with custom MIME type
		$result = $app->clone()->resolve('test.png');
		$this->assertInstanceOf(Responder::class, $result);
		$this->assertSame('image/jpeg', $result->type());
		$this->assertSame('png', $result->body());
	}

	public function testResolveSiteFile()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'site' => [
				'files' => [
					['filename' => 'test.jpg']
				],
			]
		]);

		// missing file
		$result = $app->resolve('test.png');
		$this->assertNull($result);

		// existing file
		$result = $app->resolve('test.jpg');

		$this->assertIsFile($result);
		$this->assertSame('test.jpg', $result->id());
	}

	public function testResolvePageFile()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'files' => [
							['filename' => 'test.jpg']
						],
					]
				]
			]
		]);

		// missing file
		$result = $app->resolve('test/test.png');
		$this->assertNull($result);

		// existing file
		$result = $app->resolve('test/test.jpg');

		$this->assertIsFile($result);
		$this->assertSame('test/test.jpg', $result->id());
	}

	public function testResolveMultilangPageRepresentation()
	{
		F::write($template = static::TMP . '/test.php', 'html');
		F::write($template = static::TMP . '/test.xml.php', 'xml');

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

		/**
		 * Default language (DE)
		 */

		// finding the page
		$result = $app->resolve('test');

		$this->assertIsPage($result);
		$this->assertSame('test', $result->id());
		$this->assertSame('de', $app->language()->code());

		// missing representation
		$result = $app->resolve('test.json');

		$this->assertNull($result);
		$this->assertSame('de', $app->language()->code());

		// xml presentation
		$result = $app->resolve('test.xml');

		$this->assertInstanceOf(Responder::class, $result);
		$this->assertSame('xml', $result->body());
		$this->assertSame('de', $app->language()->code());

		/**
		 * Secondary language (EN)
		 */

		// finding the page
		$result = $app->resolve('test', 'en');

		$this->assertIsPage($result);
		$this->assertSame('test', $result->id());
		$this->assertSame('en', $app->language()->code());

		// missing representation
		$result = $app->resolve('test.json', 'en');

		$this->assertNull($result);
		$this->assertSame('en', $app->language()->code());

		// xml presentation
		$result = $app->resolve('test.xml', 'en');

		$this->assertInstanceOf(Responder::class, $result);
		$this->assertSame('xml', $result->body());
		$this->assertSame('en', $app->language()->code());
	}

	public function testRepresentationErrorType()
	{
		$this->app = new App([
			'templates' => [
				'blog' => static::FIXTURES . '/templates/test.php',
			],
			'site' => [
				'children' => [
					[
						'slug' => 'blog',
						'template' => 'blog'
					]
				]
			]
		]);

		$this->assertNull($this->app->resolve('blog.php'));

		// there must be no forced php response type if the
		// representation cannot be found
		$this->assertNull($this->app->response()->type());
	}
}
