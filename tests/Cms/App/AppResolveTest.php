<?php

namespace Kirby\Cms;

use Kirby\Content\VersionId;
use Kirby\Filesystem\F;

class AppResolveTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';
	public const TMP      = KIRBY_TMP_DIR . '/Cms.AppResolve';

	public function tearDown(): void
	{
		parent::tearDown();

		// make sure to switch back to regular render mode
		VersionId::$render = null;
	}

	public function testResolveChangesUnauthenticated()
	{
		$app = new App([
			'request' => [
				'query' => [
					'_version' => 'changes'
				]
			],
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
					]
				]
			]
		]);

		$page = $app->page('test');

		// create some changes
		$page->version('changes')->save([
			'title' => 'Changed title'
		]);

		$result = $app->resolve('test');

		// check that the page gets resolved
		$this->assertIsPage($result);

		// the render mode is still null, which
		// keeps rendering the published version
		// because there's no token or authenticated user
		$this->assertNull(VersionId::$render);
	}

	public function testResolveChangesAuthenticated()
	{
		$app = new App([
			'request' => [
				'query' => [
					'_version' => 'changes'
				]
			],
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
					]
				]
			]
		]);

		// create some changes
		$app->page('test')->version('changes')->save([
			'title' => 'Changed title'
		]);

		$app->impersonate('kirby');

		$result = $app->resolve('test');

		// check that the page gets resolved
		$this->assertIsPage($result);

		// the render mode is now set to `changes`
		// because a user is authenticated and may
		// view the changes
		$this->assertTrue(VersionId::$render->is(VersionId::changes()));
	}

	public function testResolveChangesWithToken()
	{
		$token = hash_hmac(
			'sha1',
			// combination of id and template
			'testdefault',
			// salt
			'salty'
		);

		$app = new App([
			'options' => [
				'content' => [
					'salt' => 'salty'
				]
			],
			'request' => [
				'query' => [
					'_token'   => $token,
					'_version' => 'changes'
				]
			],
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'template' => 'default'
					]
				]
			]
		]);

		// create some changes
		$app->page('test')->version('changes')->save([
			'title' => 'Changed title'
		]);

		$result = $app->resolve('test');

		// check that the page gets resolved
		$this->assertIsPage($result);

		// the render mode is now set to `changes`
		// because a user is authenticated and may
		// view the changes
		$this->assertTrue(VersionId::$render->is(VersionId::changes()));
	}

	public function testResolveDraftPageUnauthenticated()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'drafts' => [
					[
						'slug' => 'draft',
					]
				]
			]
		]);

		// without token or authenticated user,
		// the draft should not be found
		$result = $app->resolve('draft');
		$this->assertNull($result);
	}

	public function testResolveDraftPageAuthenticated()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'drafts' => [
					[
						'slug' => 'draft',
					]
				]
			]
		]);

		// authenticate to verify access to the draft
		$app->impersonate('kirby');

		$result = $app->resolve('draft');
		$this->assertIsPage($result);
	}

	public function testResolveDraftPageWithToken()
	{
		$token = hash_hmac(
			'sha1',
			// combination of id and template
			'draftdefault',
			// salt
			'salty'
		);

		$app = new App([
			'options' => [
				'content' => [
					'salt' => 'salty'
				]
			],
			'request' => [
				'query' => [
					'_token' => $token
				]
			],
			'roots' => [
				'index' => '/dev/null'
			],
			'site' => [
				'drafts' => [
					[
						'slug'     => 'draft',
						'template' => 'default'
					]
				]
			]
		]);

		$result = $app->resolve('draft');
		$this->assertIsPage($result);
	}

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
