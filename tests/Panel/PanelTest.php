<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Blueprint;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Panel
 */
class PanelTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Panel';

	public function setUp(): void
	{
		Blueprint::$loaded = [];

		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			]
		]);

		Dir::make(static::TMP);
		// fix installation issues by creating directories
		Dir::make(static::TMP . '/content');
		Dir::make(static::TMP . '/media');
		Dir::make(static::TMP . '/site/accounts');
		Dir::make(static::TMP . '/site/sessions');

		// let's pretend we are on a supported server
		$_SERVER['SERVER_SOFTWARE'] = 'php';
	}

	public function tearDown(): void
	{
		// clear session file first
		$this->app->session()->destroy();

		Dir::remove(static::TMP);

		// clear fake json requests
		$_GET = [];

		// clean up $_SERVER
		unset($_SERVER['SERVER_SOFTWARE']);
	}

	/**
	 * @covers ::areas
	 */
	public function testAreas(): void
	{
		$this->assertInstanceOf(Areas::class, $this->app->panel()->areas());
	}

	/**
	 * @covers ::go
	 */
	public function testGo()
	{
		$thrown = false;
		try {
			Panel::go('test');
		} catch (Redirect $r) {
			$thrown = true;
			$this->assertSame('/panel/test', $r->getMessage());
			$this->assertSame(302, $r->getCode());
		}
		$this->assertTrue($thrown);
	}

	/**
	 * @covers ::go
	 */
	public function testGoWithCustomCode()
	{
		try {
			Panel::go('test', 301);
		} catch (Redirect $r) {
			$this->assertSame(301, $r->getCode());
		}
	}

	/**
	 * @covers ::go
	 */
	public function testGoWithCustomSlug()
	{
		$this->app = $this->app->clone([
			'options' => [
				'panel' => [
					'slug' => 'foo'
				]
			]
		]);

		try {
			Panel::go('test');
		} catch (Redirect $r) {
			$this->assertSame('/foo/test', $r->getMessage());
			$this->assertSame(302, $r->getCode());
		}
	}

	/**
	 * @covers ::isFiberRequest
	 */
	public function testIsFiberRequest(): void
	{
		// standard request
		$result = $this->app->panel()->isFiberRequest();
		$this->assertFalse($result);

		// fiber request via get
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_json' => true
				]
			]
		]);

		$result = $this->app->panel()->isFiberRequest();
		$this->assertTrue($result);

		// fiber request via header
		$this->app = $this->app->clone([
			'request' => [
				'headers' => [
					'X-Fiber' => true
				]
			]
		]);

		$result = $this->app->panel()->isFiberRequest();
		$this->assertTrue($result);

		// other request than GET
		$this->app = $this->app->clone([
			'request' => [
				'method' => 'POST'
			]
		]);

		$result = $this->app->panel()->isFiberRequest();
		$this->assertFalse($result);
	}

	/**
	 * @covers ::isPanelUrl
	 */
	public function testIsPanelUrl()
	{
		$this->assertTrue(Panel::isPanelUrl('/panel'));
		$this->assertTrue(Panel::isPanelUrl('/panel/pages/test'));
		$this->assertFalse(Panel::isPanelUrl('test'));
	}

	/**
	 * @covers ::json
	 */
	public function testJson(): void
	{
		$response = Panel::json($data = ['foo' => 'bar']);

		$this->assertSame('application/json', $response->type());
		$this->assertSame('true', $response->header('X-Fiber'));
		$this->assertSame($data, json_decode($response->body(), true));
	}

	/**
	 * @covers ::multilang
	 */
	public function testMultilang()
	{
		$this->app = $this->app->clone([
			'options' => [
				'languages' => true
			]
		]);

		$this->assertTrue($this->app->panel()->multilang());
	}

	/**
	 * @covers ::multilang
	 */
	public function testMultilangWithImplicitLanguageInstallation()
	{
		$this->app = $this->app->clone([
			'languages' => [
				[
					'code' => 'en',
					'default' => true
				],
				[
					'code' => 'de',
				]
			]
		]);

		$this->assertTrue($this->app->panel()->multilang());
	}

	/**
	 * @covers ::multilang
	 */
	public function testMultilangDisabled()
	{
		$this->assertFalse($this->app->panel()->multilang());
	}

	/**
	 * @covers ::path
	 */
	public function testPath()
	{
		$this->assertSame('site', Panel::path('/panel/site'));
		$this->assertSame('pages/test', Panel::path('/panel/pages/test'));
		$this->assertSame('', Panel::path('/test/page'));
	}

	/**
	 * @covers ::router
	 */
	public function testRouterWithDisabledPanel(): void
	{
		$app = $this->app->clone([
			'options' => [
				'panel' => false
			]
		]);

		$result = $app->panel()->router('/');

		$this->assertNull($result);
	}

	/**
	 * @covers ::setLanguage
	 */
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
		$panel    = $this->app->panel();
		$language = $panel->setLanguage();

		$this->assertSame('en', $language);
		$this->assertSame('en', $this->app->language()->code());

		// language is not stored in the session yet
		$this->assertNull($this->app->session()->get('panel.language'));
	}

	/**
	 * @covers ::setLanguage
	 */
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
		$panel    = $this->app->panel();
		$language = $panel->setLanguage();

		$this->assertSame('de', $language);
		$this->assertSame('de', $this->app->language()->code());

		// language is now stored in the session after request query
		$this->assertSame('de', $this->app->session()->get('panel.language'));
	}

	/**
	 * @covers ::setLanguage
	 */
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
		$panel    = $this->app->panel();
		$language = $panel->setLanguage();

		$this->assertSame('de', $language);
		$this->assertSame('de', $this->app->language()->code());
	}

	/**
	 * @covers ::setLanguage
	 */
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
		$panel    = $this->app->panel();
		$language = $panel->setLanguage();

		$this->assertSame('de', $language);
		$this->assertSame('de', $this->app->session()->get('panel.language'));
		$this->assertSame('de', $this->app->language()->code());
	}

	/**
	 * @covers ::setLanguage
	 */
	public function testSetLanguageInSingleLanguageSite(): void
	{
		$panel    = $this->app->panel();
		$language = $panel->setLanguage();

		$this->assertNull($language);
		$this->assertNull($this->app->language());
	}

	/**
	 * @covers ::setTranslation
	 */
	public function testSetTranslation(): void
	{
		$panel       = $this->app->panel();
		$translation = $panel->setTranslation();

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

		$panel       = $this->app->panel();
		$translation = $panel->setTranslation();

		$this->assertSame('de', $translation);
		$this->assertSame('de', $this->app->translation()->code());
	}
}
