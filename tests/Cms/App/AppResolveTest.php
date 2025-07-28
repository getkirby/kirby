<?php

namespace Kirby\Cms;

use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(App::class)]
class AppResolveTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';
	public const TMP      = KIRBY_TMP_DIR . '/Cms.AppResolve';

	public function testResolveHomePage(): void
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

	public function testResolveMainPage(): void
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

	public function testResolveSubPage(): void
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

	public function testResolveDraft(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'drafts' => [
							[
								'slug'  => 'a-draft',
							]
						]
					]
				]
			]
		]);

		$result = $app->resolve('test/a-draft');
		$this->assertNull($result);

		$app = $app->clone([
			'request' => [
				'query' => [
					'_token' => $app->page('test/a-draft')->version()->previewToken()
				]
			]
		]);

		$result = $app->resolve('test/a-draft');

		$this->assertIsPage($result);
		$this->assertSame('test/a-draft', $result->id());
	}

	public function testResolvePageRepresentation(): void
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

	public function testResolvePageHtmlRepresentation(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null',
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

		$response = $app->resolve('test.html');
		$this->assertSame(301, $response->code());
		$this->assertSame('/test', $response->header('Location'));

	}

	public function testResolveFileDefault(): void
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
		$this->assertNull($result);
	}

	public function testResolveSiteFile(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null',
			],
			'site' => [
				'files' => [
					['filename' => 'test.jpg']
				],
			],
			'options' => [
				'content' => [
					'fileRedirects' => true
				]
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

	public function testResolvePageFile(): void
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
				],
				'files' => [
					['filename' => 'test-site.jpg']
				]
			],
			'options' => [
				'content' => [
					'fileRedirects' => true
				]
			]
		]);

		// missing file
		$result = $app->resolve('test/test.png');
		$this->assertNull($result);

		// file that only exists on the site
		$result = $app->resolve('another-page/test-site.jpg');
		$this->assertNull($result);

		// existing file
		$result = $app->resolve('test/test.jpg');

		$this->assertIsFile($result);
		$this->assertSame('test/test.jpg', $result->id());
	}

	public function testResolveFileEnabled(): void
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
			],
			'options' => [
				'content' => [
					'fileRedirects' => true
				]
			]
		]);

		// missing file
		$result1 = $app->resolve('test/test.png');
		$result2 = $app->resolveFile($app->page('test')->file('test.png'));
		$this->assertNull($result1);
		$this->assertNull($result2);

		// existing file
		$result1 = $app->resolve('test/test.jpg');
		$result2 = $app->resolveFile($app->page('test')->file('test.jpg'));
		$this->assertSame($result1, $result2);
		$this->assertIsFile($result1);
		$this->assertSame('test/test.jpg', $result1->id());
	}

	public function testResolveFileDisabled(): void
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
			],
		]);

		// missing file
		$result1 = $app->resolve('test/test.png');
		$result2 = $app->resolveFile($app->page('test')->file('test.png'));
		$this->assertNull($result1);
		$this->assertNull($result2);

		// existing file
		$result1 = $app->resolve('test/test.jpg');
		$result2 = $app->resolveFile($app->page('test')->file('test.jpg'));
		$this->assertNull($result1);
		$this->assertNull($result2);
	}

	public function testResolveFileClosure(): void
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
							[
								'content'  => [
									'public' => 'true'
								],
								'filename' => 'test-public.jpg'
							],
							[
								'content'  => [
									'public' => 'false'
								],
								'filename' => 'test-private.jpg'
							]
						],
					]
				]
			],
			'options' => [
				'content' => [
					'fileRedirects' => fn (File $file): bool => $file->public()->toBool()
				]
			]
		]);

		// missing file
		$result1 = $app->resolve('test/test.png');
		$result2 = $app->resolveFile($app->page('test')->file('test.png'));
		$this->assertNull($result1);
		$this->assertNull($result2);

		// existing file (allowed)
		$result1 = $app->resolve('test/test-public.jpg');
		$result2 = $app->resolveFile($app->page('test')->file('test-public.jpg'));
		$this->assertSame($result1, $result2);
		$this->assertIsFile($result1);
		$this->assertSame('test/test-public.jpg', $result1->id());

		// existing file (not allowed)
		$result1 = $app->resolve('test/test-private.jpg');
		$result2 = $app->resolveFile($app->page('test')->file('test-private.jpg'));
		$this->assertNull($result1);
		$this->assertNull($result2);
	}

	public function testResolveMultilangPageRepresentation(): void
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

	public function testRepresentationErrorType(): void
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
