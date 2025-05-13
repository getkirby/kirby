<?php

namespace Kirby\Cms;

use InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\F;
use Kirby\Toolkit\I18n;

class RouterTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.Router';

	public function testHomeRoute()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'home']
				]
			]
		]);

		$page = $app->call('');
		$this->assertIsPage($page);
		$this->assertSame('home', $page->id());
	}

	public function testHomeFolderRoute()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'home']
				]
			]
		]);

		$response = $app->call('home');
		$this->assertInstanceOf(Responder::class, $response);
		$this->assertSame(302, $response->code());
	}

	public function testHomeCustomFolderRoute()
	{
		$app = $this->app->clone([
			'options' => [
				'home' => 'homie'
			],
			'site' => [
				'children' => [
					[
						'slug' => 'homie'
					]
				]
			]
		]);

		$response = $app->call('homie');
		$this->assertInstanceOf(Responder::class, $response);
		$this->assertSame(302, $response->code());
	}

	public function testHomeRouteWithoutHomePage()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => []
			]
		]);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The home page does not exist');

		$app->call('/');
	}

	public function testPageRoute()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'projects'
					]
				]
			]
		]);

		$page = $app->call('projects');
		$this->assertIsPage($page);
		$this->assertSame('projects', $page->id());
	}

	public function testPageRepresentationRoute()
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
			]
		]);

		// missing representation
		$result = $app->call('test.json');
		$this->assertNull($result);

		// xml presentation
		$result = $app->call('test.xml');

		$this->assertInstanceOf(Responder::class, $result);
		$this->assertSame('xml', $result->body());
	}

	public function testPageFileRoute()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'  => 'projects',
						'files' => [
							[
								'filename' => 'cover.jpg'
							]
						]
					]
				]
			]
		]);

		$file = $app->call('projects/cover.jpg');
		$this->assertIsFile($file);
		$this->assertSame('projects/cover.jpg', $file->id());
	}

	public function testSiteFileRoute()
	{
		$app = $this->app->clone([
			'site' => [
				'files' => [
					[
						'filename' => 'background.jpg'
					]
				]
			]
		]);

		$file = $app->call('background.jpg');
		$this->assertIsFile($file);
		$this->assertSame('background.jpg', $file->id());
	}

	public function testPageFileRouteDisabled()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'  => 'projects',
						'files' => [
							[
								'filename' => 'cover.jpg'
							]
						]
					]
				]
			],
			'options' => [
				'content' => [
					'fileRedirects' => false
				]
			]
		]);

		$file = $app->call('projects/cover.jpg');
		$this->assertNull($file);
	}

	public function testNestedPageRoute()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'  => 'projects',
						'children' => [
							[
								'slug' => 'project-a'
							]
						]
					]
				]
			]
		]);

		$page = $app->call('projects/project-a');
		$this->assertIsPage($page);
		$this->assertSame('projects/project-a', $page->id());
	}

	public function testNotFoundRoute()
	{
		$page = $this->app->call('not-found');
		$this->assertNull($page);
	}

	public function testPageMediaRoute()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'  => 'projects',
						'files' => [
							[
								'filename' => 'cover.jpg'
							]
						]
					]
				]
			]
		]);

		$mediaHash = $app->file('projects/cover.jpg')->mediaHash();

		$response = $app->call('media/pages/projects/' . $mediaHash . '/cover.jpg');
		$this->assertInstanceOf(Response::class, $response);
	}

	public function testSiteMediaRoute()
	{
		$app = $this->app->clone([
			'site' => [
				'files' => [
					[
						'filename' => 'background.jpg'
					]
				]
			]
		]);

		$mediaHash = $app->file('background.jpg')->mediaHash();

		$response = $app->call('media/site/' . $mediaHash . '/background.jpg');
		$this->assertInstanceOf(Response::class, $response);
	}

	public function testUserMediaRoute()
	{
		$app = $this->app->clone([
			'users' => [
				[
					'id'    => 'test',
					'email' => 'admin@getkirby.com',
					'files' => [
						[
							'filename' => 'test.jpg'
						]
					]
				]
			]
		]);

		$mediaHash = $app->user('test')->file('test.jpg')->mediaHash();

		$response = $app->call('media/users/test/' . $mediaHash . '/test.jpg');
		$this->assertInstanceOf(Response::class, $response);
	}

	public function testDisabledApi()
	{
		$app = $this->app->clone([
			'options' => [
				'api' => false
			]
		]);

		$this->assertNull($app->call('api'));
		$this->assertNull($app->call('api/something'));

		// the api route should still be there
		$patterns = array_column($app->routes(), 'pattern');
		$this->assertSame('api/(:all)', $patterns[0]);
	}

	public function testDisabledPanel()
	{
		$app = $this->app->clone([
			'options' => [
				'panel' => false
			]
		]);

		$this->assertNull($app->call('panel'));
		$this->assertNull($app->call('panel/something'));
	}

	public static function customRouteProvider(): array
	{
		return [
			// home
			['/', ''],
			['/', '/'],
			['', ''],
			['', '/'],

			// main page
			['(:any)', 'test'],
			['(:any)', '/test'],
			['/(:any)', 'test'],
			['/(:any)', '/test'],

			// subpages
			['(:all)', 'foo/bar'],
			['(:all)', '/foo/bar'],
			['/(:all)', 'foo/bar'],
			['/(:all)', '/foo/bar'],
		];
	}

	/**
	 * @dataProvider customRouteProvider
	 */
	public function testCustomRoute($pattern, $path)
	{
		$app = $this->app->clone([
			'routes' => [
				[
					'pattern' => $pattern,
					'action'  => fn () => 'test'
				]
			]
		]);

		$this->assertSame('test', $app->call($path));
	}

	public function testBadMethodRoute()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid routing method: WURST');
		$this->expectExceptionCode(400);

		$this->app->call('/', 'WURST');
	}

	public function testMultiLangHomeRoute()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'home'
					]
				]
			],
			'options' => [
				'languages' => true
			],
			'languages' => [
				[
					'code'    => 'en',
				],
				[
					'code' => 'fr',
					'default' => true
				]
			]
		]);


		// fr
		$page = $app->call('fr');

		$this->assertIsPage($page);
		$this->assertSame('home', $page->id());
		$this->assertSame('fr', $app->language()->code());
		$this->assertSame('fr', I18n::locale());

		// en
		$page = $app->call('en');

		$this->assertIsPage($page);
		$this->assertSame('home', $page->id());
		$this->assertSame('en', $app->language()->code());
		$this->assertSame('en', I18n::locale());

		// redirect
		$result = $app->call('/');

		$this->assertInstanceOf(Responder::class, $result);
	}

	public function testMultiLangHomeRouteWithoutLanguageCode()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'home'
					]
				]
			],
			'options' => [
				'languages' => true
			],
			'languages' => [
				[
					'code'    => 'fr',
					'default' => true,
					'url'     => '/'
				],
				[
					'code' => 'en',
					'url'  => '/en'
				],
			]
		]);

		// fr
		$page = $app->call('/');

		$this->assertIsPage($page);
		$this->assertSame('home', $page->id());
		$this->assertSame('fr', $app->language()->code());
		$this->assertSame('fr', I18n::locale());

		// en
		$page = $app->call('en');

		$this->assertIsPage($page);
		$this->assertSame('home', $page->id());
		$this->assertSame('en', $app->language()->code());
		$this->assertSame('en', I18n::locale());
	}

	public static function multiDomainProvider(): array
	{
		return [
			['https://getkirby.fr', 'fr'],
			['https://getkirby.com', 'en'],
		];
	}

	/**
	 * @dataProvider multiDomainProvider
	 */
	public function testMultiLangHomeWithDifferentDomains($domain, $language)
	{
		$app = $this->app->clone([
			'urls' => [
				'index' => $domain
			],
			'site' => [
				'children' => [
					[
						'slug' => 'home'
					]
				]
			],
			'options' => [
				'languages' => true
			],
			'languages' => [
				[
					'code'    => 'fr',
					'default' => true,
					'url'     => 'https://getkirby.fr'
				],
				[
					'code' => 'en',
					'url'  => 'https://getkirby.com'
				]
			]
		]);

		// home
		$page = $app->call('');

		$this->assertIsPage($page);
		$this->assertSame('home', $page->id());
		$this->assertSame($language, $app->language()->code());
		$this->assertSame($language, I18n::locale());
	}

	/**
	 * @dataProvider multiDomainProvider
	 */
	public function testMultiLangHomeWithDifferentDomainsAndPath($domain, $language)
	{
		$app = $this->app->clone([
			'urls' => [
				'index' => $domain
			],
			'site' => [
				'children' => [
					[
						'slug' => 'home'
					]
				]
			],
			'options' => [
				'languages' => true
			],
			'languages' => [
				[
					'code'    => 'fr',
					'default' => true,
					'url'     => 'https://getkirby.fr/subfolder'
				],
				[
					'code' => 'en',
					'url'  => 'https://getkirby.com/subfolder'
				]
			]
		]);

		// redirect
		$redirect = $app->call('');
		$this->assertInstanceOf(Responder::class, $redirect);

		// home
		$page = $app->call('subfolder');

		$this->assertIsPage($page);
		$this->assertSame('home', $page->id());
		$this->assertSame($language, $app->language()->code());
		$this->assertSame($language, I18n::locale());
	}

	public static function acceptedLanguageProvider(): array
	{
		return [
			['fr,en;q=0.8', '/fr'],
			['en', '/en'],
			['de', '/fr']
		];
	}

	/**
	 * @dataProvider acceptedLanguageProvider
	 */
	public function testMultiLangHomeRouteWithoutLanguageCodeAndLanguageDetection($accept, $redirect)
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'home'
					]
				]
			],
			'options' => [
				'languages' => true,
				'languages.detect' => true
			],
			'languages' => [
				[
					'code'    => 'fr',
					'default' => true,
				],
				[
					'code' => 'en',
				]
			]
		]);

		$acceptedLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;

		// set the accepted visitor language
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = $accept;
		$app = $app->clone();

		$response = $app->call('/');

		$this->assertInstanceOf(Responder::class, $response);
		$this->assertSame(['Location' => $redirect], $response->headers());

		// reset the accepted visitor language
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = $acceptedLanguage;
	}

	public function testMultiLangHomeRouteWithoutHomePage()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => []
			],
			'options' => [
				'languages' => true
			],
			'languages' => [
				[
					'code'    => 'fr',
					'default' => true,
					'url'     => '/'
				],
				[
					'code' => 'en',
				]
			]
		]);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The home page does not exist');

		$app->call('/');
	}

	public function testMultiLangPageRoute()
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'projects'
					]
				]
			],
			'options' => [
				'languages' => true
			],
			'languages' => [
				[
					'code'    => 'fr',
					'default' => true
				],
				[
					'code' => 'en',
				]
			]
		]);

		// en
		$page = $app->call('en/projects');

		$this->assertIsPage($page);
		$this->assertSame('projects', $page->id());
		$this->assertSame('en', $app->language()->code());
		$this->assertSame('en', I18n::locale());

		// fr
		$page = $app->call('fr/projects');

		$this->assertIsPage($page);
		$this->assertSame('projects', $page->id());
		$this->assertSame('fr', $app->language()->code());
		$this->assertSame('fr', I18n::locale());
	}

	public function testMultilangPageRepresentationRoute()
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
			'languages' => [
				[
					'code'    => 'fr',
					'default' => true
				],
				[
					'code' => 'en',
				]
			]
		]);

		// DE

		// missing representation
		$result = $app->call('fr/test.json');
		$this->assertNull($result);

		// xml presentation
		$result = $app->call('fr/test.xml');

		$this->assertInstanceOf(Responder::class, $result);
		$this->assertSame('xml', $result->body());
		$this->assertSame('fr', $app->language()->code());
		$this->assertSame('fr', I18n::locale());

		// EN

		// missing representation
		$result = $app->call('en/test.json');
		$this->assertNull($result);

		// xml presentation
		$result = $app->call('en/test.xml');

		$this->assertInstanceOf(Responder::class, $result);
		$this->assertSame('xml', $result->body());
		$this->assertSame('en', $app->language()->code());
		$this->assertSame('en', I18n::locale());
	}

	public function testMultilangPageRepresentationRouteWithoutLanguageCode()
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
			'languages' => [
				[
					'code'    => 'fr',
					'default' => true,
					'url'     => '/'
				],
				[
					'code' => 'en',
				]
			]
		]);

		// FR

		// missing representation
		$result = $app->call('test.json');
		$this->assertNull($result);

		// xml presentation
		$result = $app->call('test.xml');

		$this->assertInstanceOf(Responder::class, $result);
		$this->assertSame('xml', $result->body());
		$this->assertSame('fr', $app->language()->code());
		$this->assertSame('fr', I18n::locale());

		// EN

		// missing representation
		$result = $app->call('en/test.json');
		$this->assertNull($result);

		// xml presentation
		$result = $app->call('en/test.xml');

		$this->assertInstanceOf(Responder::class, $result);
		$this->assertSame('xml', $result->body());
		$this->assertSame('en', $app->language()->code());
		$this->assertSame('en', I18n::locale());
	}

	public function testCustomMediaFolder()
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'urls' => [
				'index' => 'https://getkirby.com',
				'media' => $media = 'https://getkirby.com/thumbs'
			]
		]);

		$this->assertSame($media, $app->url('media'));

		// call custom media route
		$route = $app->router()->find('thumbs/pages/a/b/1234-5678/test.jpg', 'GET');
		$this->assertStringContainsString('thumbs/pages/(.*)', $route->pattern());

		$route = $app->router()->find('thumbs/site/1234-5678/test.jpg', 'GET');
		$this->assertStringContainsString('thumbs/site/([a-z', $route->pattern());

		$route = $app->router()->find('thumbs/users/test@getkirby.com/1234-5678/test.jpg', 'GET');
		$this->assertStringContainsString('thumbs/users/([a-z', $route->pattern());

		// default media route should result in the fallback route
		$route = $app->router()->find('media/pages/a/b/1234-5678/test.jpg', 'GET');
		$this->assertSame('(.*)', $route->pattern());

		$route = $app->router()->find('media/site/1234-5678/test.jpg', 'GET');
		$this->assertSame('(.*)', $route->pattern());

		$route = $app->router()->find('media/users/test@getkirby.com/1234-5678/test.jpg', 'GET');
		$this->assertSame('(.*)', $route->pattern());
	}
}
