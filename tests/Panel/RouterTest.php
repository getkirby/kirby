<?php

namespace Kirby\Panel;

use Kirby\Exception\Exception;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Response;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Router::class)]
class RouterTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Panel';

	public function testResponse(): void
	{
		$response = new Response('Test');
		$router   = new Router($this->app->panel());

		// response objects should not be modified
		$this->assertSame($response, $router->response($response));
	}

	public function testResponseFromFalse(): void
	{
		// fake json request for easier assertions
		$this->setRequest(['_json' => true]);

		// false is interpreted as 404
		$router = new Router($this->app->panel());

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The data could not be found');

		$router->response(false);
	}

	public function testResponseFromNull(): void
	{
		// fake json request for easier assertions
		$this->setRequest(['_json' => true]);

		// null is interpreted as 404
		$router = new Router($this->app->panel());

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The data could not be found');

		$router->response(null);
	}

	public function testResponseFromString(): void
	{
		// fake json request for easier assertions
		$this->setRequest(['_json' => true]);

		// strings are interpreted as errors
		$router = new Router($this->app->panel());

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Test');

		$router->response('Test');
	}

	public function testRoutes(): void
	{
		$areas  = new Areas([]);
		$panel  = $this->app->panel();
		$router = new Router($panel);
		$routes = $router->routes($areas);

		$this->assertSame('browser', $routes[0]['pattern']);
		$this->assertSame(['/', 'installation', 'login'], $routes[1]['pattern']);
		$this->assertSame('(:all)', $routes[2]['pattern']);

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('Could not find Panel view for route: foo');

		$routes[2]['action']('foo');
	}

	public function testSetLanguageWithoutRequest(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'languages' => true,
			],
			'languages' => [
				[
					'code' => 'en',
					'name' => 'English',
					'default' => true
				],
				[
					'code' => 'de',
					'name' => 'Deutsch',
				]
			]
		]);

		// set for the first time
		$language = $this->app->panel()->router()->setLanguage();

		$this->assertSame('en', $language);
		$this->assertSame('en', $this->app->language()->code());

		// language is not stored in the session yet
		$this->assertNull($this->app->session()->get('panel.language'));
	}

	public function testSetLanguage(): void
	{
		$this->app = $this->app->clone([
			'languages' => [
				[
					'code' => 'en',
					'name' => 'English',
					'default' => true
				],
				[
					'code' => 'de',
					'name' => 'Deutsch',
				]
			],
			'options' => [
				'languages' => true,
			],
			'request' => [
				'query' => [
					'language' => 'de'
				]
			]
		]);

		// set for the first time
		$language = $this->app->panel()->router()->setLanguage();

		$this->assertSame('de', $language);
		$this->assertSame('de', $this->app->language()->code());

		// language is now stored in the session after request query
		$this->assertSame('de', $this->app->session()->get('panel.language'));
	}

	public function testSetLanguageWithCustomDefault(): void
	{
		$this->app = $this->app->clone([
			'languages' => [
				[
					'code' => 'de',
					'name' => 'Deutsch',
					'default' => true
				],
				[
					'code' => 'en',
					'name' => 'English',
				],
			],
			'options' => [
				'languages' => true,
			]
		]);

		// set for the first time
		$language = $this->app->panel()->router()->setLanguage();

		$this->assertSame('de', $language);
		$this->assertSame('de', $this->app->language()->code());
	}

	public function testSetLanguageViaGet(): void
	{
		// switch via get request
		// needs to come first before the app is cloned
		$_GET['language'] = 'de';

		$this->app = $this->app->clone([
			'options' => [
				'languages' => true,
			],
			'languages' => [
				[
					'code' => 'en',
					'name' => 'English',
					'default' => true
				],
				[
					'code' => 'de',
					'name' => 'Deutsch',
				]
			]
		]);

		// set for the first time
		$language = $this->app->panel()->router()->setLanguage();

		$this->assertSame('de', $language);
		$this->assertSame('de', $this->app->session()->get('panel.language'));
		$this->assertSame('de', $this->app->language()->code());
	}

	public function testSetLanguageInSingleLanguageSite(): void
	{
		$language = $this->app->panel()->router()->setLanguage();

		$this->assertNull($language);
		$this->assertNull($this->app->language());
	}

	public function testSetTranslation(): void
	{
		$translation = $this->app->panel()->router()->setTranslation();

		$this->assertSame('en', $translation);
		$this->assertSame('en', $this->app->translation()->code());
	}

	public function testSetTranslationViaUser(): void
	{
		$this->app = $this->app->clone([
			'users' => [
				[
					'email' => 'test@getkirby.com',
					'language' => 'de',
					'role' => 'admin'
				]
			]
		]);

		$this->app->impersonate('test@getkirby.com');

		$translation = $this->app->panel()->router()->setTranslation();

		$this->assertSame('de', $translation);
		$this->assertSame('de', $this->app->translation()->code());
	}
}
