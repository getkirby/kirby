<?php

namespace Kirby\Cms\App;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Responder;
use Kirby\Cms\TestCase;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Resolver::class)]
class ResolverTest extends TestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures';
	public const string TMP      = KIRBY_TMP_DIR . '/Cms.App.Resolver';

	/**
	 * Asserts that resolving the given path throws a NotFoundException
	 */
	protected function assertResolveNotFound(App $app, string|null $path): void
	{
		try {
			(new Resolver($app))->resolve($path);
			$this->fail('Expected a NotFoundException for path: ' . ($path ?? 'null'));
		} catch (NotFoundException) {
			$this->assertTrue(true);
		}
	}

	protected function resolve(App $app, string|null $path = null): mixed
	{
		return (new Resolver($app))->resolve($path);
	}

	public function testResolveHomePage(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'home']
				]
			]
		]);

		$result = $this->resolve($app, null);

		$this->assertIsPage($result);
		$this->assertTrue($result->isHomePage());
	}

	public function testResolveHomePageMissing(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => []
			]
		]);

		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('The home page does not exist');

		$this->resolve($app, null);
	}

	public function testResolveMainPage(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			]
		]);

		$result = $this->resolve($app, 'test');

		$this->assertIsPage($result);
		$this->assertSame('test', $result->id());
	}

	public function testResolveSubPage(): void
	{
		$app = $this->app->clone([
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

		$result = $this->resolve($app, 'test/subpage');

		$this->assertIsPage($result);
		$this->assertSame('test/subpage', $result->id());
	}

	public function testResolveDraft(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'   => 'test',
						'drafts' => [
							['slug' => 'a-draft']
						]
					]
				]
			]
		]);

		$this->assertResolveNotFound($app, 'test/a-draft');
	}

	public function testResolveDraftWithUser(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'   => 'test',
						'drafts' => [
							['slug' => 'a-draft']
						]
					]
				]
			],
			'users' => [
				['email' => 'admin@getkirby.com', 'role' => 'admin']
			]
		]);

		$app->impersonate('admin@getkirby.com');

		$result = $this->resolve($app, 'test/a-draft');

		$this->assertIsPage($result);
		$this->assertSame('test/a-draft', $result->id());
	}

	public function testResolveDraftWithUserDeniedByPermission(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'   => 'test',
						'drafts' => [
							['slug' => 'a-draft']
						]
					]
				]
			],
			'roles' => [
				[
					'name'        => 'editor',
					'permissions' => [
						'pages' => ['access' => false]
					]
				]
			],
			'users' => [
				['email' => 'editor@getkirby.com', 'role' => 'editor']
			]
		]);

		$app->impersonate('editor@getkirby.com');

		$this->assertResolveNotFound($app, 'test/a-draft');
	}

	public function testResolveDraftWithToken(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'   => 'test',
						'drafts' => [
							['slug' => 'a-draft']
						]
					]
				]
			]
		]);

		$token = $app->page('test/a-draft')->version()->previewToken();
		$app   = $app->clone([
			'request' => [
				'query' => ['_token' => $token]
			]
		]);

		$result = $this->resolve($app, 'test/a-draft');

		$this->assertIsPage($result);
		$this->assertSame('test/a-draft', $result->id());
	}

	public function testResolveDraftWithTokenBypassesPermission(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'   => 'test',
						'drafts' => [
							['slug' => 'a-draft']
						]
					]
				]
			],
			'roles' => [
				[
					'name'        => 'editor',
					'permissions' => [
						'pages' => ['access' => false]
					]
				]
			],
			'users' => [
				['email' => 'editor@getkirby.com', 'role' => 'editor']
			]
		]);

		$token = $app->page('test/a-draft')->version()->previewToken();
		$app   = $app->clone([
			'request' => [
				'query' => ['_token' => $token]
			]
		]);

		$app->impersonate('editor@getkirby.com');

		$result = $this->resolve($app, 'test/a-draft');

		$this->assertIsPage($result);
		$this->assertSame('test/a-draft', $result->id());
	}

	public function testResolvePageRepresentation(): void
	{
		F::write(static::TMP . '/test.php', 'html');
		F::write(static::TMP . '/test.xml.php', 'xml');
		F::write(
			static::TMP . '/test.png.php',
			'<?php $kirby->response()->type("image/jpeg"); ?>png'
		);

		$app = $this->app->clone([
			'roots' => [
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
		$this->assertResolveNotFound($app, 'test.json');

		// incomplete content representation
		$this->assertResolveNotFound($app, 'test.');

		// xml representation
		$result = $this->resolve($app->clone(), 'test.xml');
		$this->assertInstanceOf(Responder::class, $result);
		$this->assertSame('text/xml', $result->type());
		$this->assertSame('xml', $result->body());

		// representation with custom MIME type
		$result = $this->resolve($app->clone(), 'test.png');
		$this->assertInstanceOf(Responder::class, $result);
		$this->assertSame('image/jpeg', $result->type());
		$this->assertSame('png', $result->body());
	}

	public function testResolvePageHtmlRepresentation(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'template' => 'test'
					]
				],
			]
		]);

		$response = $this->resolve($app, 'test.html');
		$this->assertSame(301, $response->code());
		$this->assertSame('/test', $response->header('Location'));
	}

	public function testResolvePageContentNegotiation(): void
	{
		F::write(static::TMP . '/test.php', 'html');
		F::write(static::TMP . '/test.md.php', '# Markdown');

		$app = $this->app->clone([
			'roots' => [
				'templates' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'template' => 'test'
					]
				]
			]
		]);

		// the visitor prefers markdown and a representation exists
		$app->visitor()->acceptedMimeType('text/markdown');
		$result = $this->resolve($app, 'test');
		$this->assertInstanceOf(Responder::class, $result);
		$this->assertSame('text/markdown', $result->type());
		$this->assertSame('# Markdown', $result->body());
		$this->assertSame('Accept', $app->response()->header('Vary'));

		// the visitor prefers HTML: render the page as usual,
		// but the response still varies by the Accept header
		$app->visitor()->acceptedMimeType('text/html');
		$result = $this->resolve($app, 'test');
		$this->assertIsPage($result);
		$this->assertSame('Accept', $app->response()->header('Vary'));

		// a wildcard Accept header resolves to HTML
		$app->visitor()->acceptedMimeType('*/*');
		$this->assertIsPage($this->resolve($app, 'test'));

		// a preferred type without a representation falls back
		// to the next accepted type (HTML)
		$app->visitor()->acceptedMimeType('application/xml, text/html');
		$this->assertIsPage($this->resolve($app, 'test'));

		// a preferred type without a representation and without
		// HTML acceptance still renders the page as HTML
		$app->visitor()->acceptedMimeType('application/json');
		$this->assertIsPage($this->resolve($app, 'test'));
	}

	public function testResolvePageContentNegotiationDisabled(): void
	{
		F::write(static::TMP . '/test.php', 'html');
		F::write(static::TMP . '/test.md.php', '# Markdown');

		$app = $this->app->clone([
			'roots' => [
				'templates' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'template' => 'test'
					]
				]
			],
			'options' => [
				'content' => [
					'negotiation' => false
				]
			],
		]);

		$result = $this->resolve($app, 'test');
		$this->assertIsPage($result);
		$this->assertNull($app->response()->header('Vary'));
	}

	public function testResolveFileDefault(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'  => 'test',
						'files' => [
							['filename' => 'test.jpg']
						],
					]
				]
			]
		]);

		// missing file
		$this->assertResolveNotFound($app, 'test/test.png');

		// existing file, but file redirects are disabled by default
		$this->assertResolveNotFound($app, 'test/test.jpg');
	}

	public function testResolveSiteFile(): void
	{
		$app = $this->app->clone([
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
		$this->assertResolveNotFound($app, 'test.png');

		// existing file
		$result = $this->resolve($app, 'test.jpg');
		$this->assertIsFile($result);
		$this->assertSame('test.jpg', $result->id());
	}

	public function testResolvePageFile(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'  => 'test',
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
		$this->assertResolveNotFound($app, 'test/test.png');

		// file that only exists on the site
		$this->assertResolveNotFound($app, 'another-page/test-site.jpg');

		// existing file
		$result = $this->resolve($app, 'test/test.jpg');
		$this->assertIsFile($result);
		$this->assertSame('test/test.jpg', $result->id());
	}

	public function testResolveDraftFileWithoutAccess(): void
	{
		$app = $this->app->clone([
			'site' => [
				'drafts' => [
					[
						'slug'  => 'a-draft',
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

		// a file on a draft page must not be exposed to
		// anonymous visitors through clean file URLs
		$this->assertResolveNotFound($app, 'a-draft/test.jpg');
	}

	public function testResolveDraftFileWithUser(): void
	{
		$app = $this->app->clone([
			'site' => [
				'drafts' => [
					[
						'slug'  => 'a-draft',
						'files' => [
							['filename' => 'test.jpg']
						],
					]
				]
			],
			'users' => [
				['email' => 'admin@getkirby.com', 'role' => 'admin']
			],
			'options' => [
				'content' => [
					'fileRedirects' => true
				]
			]
		]);

		$app->impersonate('admin@getkirby.com');

		$result = $this->resolve($app, 'a-draft/test.jpg');
		$this->assertIsFile($result);
		$this->assertSame('a-draft/test.jpg', $result->id());
	}

	public function testResolveDraftFileWithUserDeniedByPermission(): void
	{
		$app = $this->app->clone([
			'site' => [
				'drafts' => [
					[
						'slug'  => 'a-draft',
						'files' => [
							['filename' => 'test.jpg']
						],
					]
				]
			],
			'roles' => [
				[
					'name'        => 'editor',
					'permissions' => [
						'pages' => ['access' => false]
					]
				]
			],
			'users' => [
				['email' => 'editor@getkirby.com', 'role' => 'editor']
			],
			'options' => [
				'content' => [
					'fileRedirects' => true
				]
			]
		]);

		$app->impersonate('editor@getkirby.com');

		$this->assertResolveNotFound($app, 'a-draft/test.jpg');
	}

	public function testResolveDraftFileWithToken(): void
	{
		$app = $this->app->clone([
			'site' => [
				'drafts' => [
					[
						'slug'  => 'a-draft',
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

		$token = $app->page('a-draft')->version()->previewToken();
		$app   = $app->clone([
			'request' => [
				'query' => ['_token' => $token]
			]
		]);

		$result = $this->resolve($app, 'a-draft/test.jpg');
		$this->assertIsFile($result);
		$this->assertSame('a-draft/test.jpg', $result->id());
	}

	public function testResolveFileEnabled(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'  => 'test',
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
		$this->assertResolveNotFound($app, 'test/test.png');

		// existing file
		$result = $this->resolve($app, 'test/test.jpg');
		$this->assertIsFile($result);
		$this->assertSame('test/test.jpg', $result->id());
	}

	public function testResolveFileDisabled(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'  => 'test',
						'files' => [
							['filename' => 'test.jpg']
						],
					]
				]
			],
		]);

		// missing file
		$this->assertResolveNotFound($app, 'test/test.png');

		// existing file, but file redirects are disabled
		$this->assertResolveNotFound($app, 'test/test.jpg');
	}

	public function testResolveFileClosure(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'  => 'test',
						'files' => [
							[
								'content'  => ['public' => 'true'],
								'filename' => 'test-public.jpg'
							],
							[
								'content'  => ['public' => 'false'],
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
		$this->assertResolveNotFound($app, 'test/test.png');

		// existing file (allowed)
		$result = $this->resolve($app, 'test/test-public.jpg');
		$this->assertIsFile($result);
		$this->assertSame('test/test-public.jpg', $result->id());

		// existing file (not allowed)
		$this->assertResolveNotFound($app, 'test/test-private.jpg');
	}

	public function testIsResolvableFile(): void
	{
		$props = [
			'site' => [
				'files' => [
					[
						'content'  => ['public' => 'true'],
						'filename' => 'public.jpg'
					],
					[
						'content'  => ['public' => 'false'],
						'filename' => 'private.jpg'
					]
				]
			]
		];

		// disabled by default
		$app = $this->app->clone($props);
		$this->assertFalse((new Resolver($app))->isResolvableFile($app->site()->file('public.jpg')));

		// enabled for all files
		$app = $this->app->clone([
			...$props,
			'options' => ['content' => ['fileRedirects' => true]]
		]);
		$this->assertTrue((new Resolver($app))->isResolvableFile($app->site()->file('public.jpg')));

		// enabled per file through a closure
		$app = $this->app->clone([
			...$props,
			'options' => [
				'content' => [
					'fileRedirects' => fn (File $file): bool => $file->public()->toBool()
				]
			]
		]);
		$resolver = new Resolver($app);
		$this->assertTrue($resolver->isResolvableFile($app->site()->file('public.jpg')));
		$this->assertFalse($resolver->isResolvableFile($app->site()->file('private.jpg')));
	}

	public function testResolveRepresentationErrorType(): void
	{
		$app = $this->app->clone([
			'templates' => [
				'blog' => static::FIXTURES . '/templates/test.php',
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'blog',
						'template' => 'blog'
					]
				]
			]
		]);

		$this->assertResolveNotFound($app, 'blog.php');

		// there must be no forced php response type if the
		// representation cannot be found
		$this->assertNull($app->response()->type());
	}
}
