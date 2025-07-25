<?php

namespace Kirby\Panel;

use Kirby\Filesystem\Dir;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Panel::class)]
class PanelTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Panel';

	public function setUp(): void
	{
		parent::setUp();

		// fix installation issues by creating directories
		Dir::make(static::TMP . '/content');
		Dir::make(static::TMP . '/media');
		Dir::make(static::TMP . '/site/accounts');
		Dir::make(static::TMP . '/site/sessions');

		// let's pretend we are on a supported server
		$_SERVER['SERVER_SOFTWARE'] = 'php';
	}

	public function testAccess(): void
	{
		$panel = $this->app->panel();
		$this->assertInstanceOf(Access::class, $panel->access());
	}

	public function testAreas(): void
	{
		$panel = $this->app->panel();
		$this->assertInstanceOf(Areas::class, $panel->areas());
	}

	public function testGo(): void
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

	public function testGoWithCustomCode(): void
	{
		try {
			Panel::go('test', 301);
		} catch (Redirect $r) {
			$this->assertSame(301, $r->getCode());
		}
	}

	public function testGoWithCustomSlug(): void
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

	public function testHome(): void
	{
		$panel = $this->app->panel();
		$this->assertInstanceOf(Home::class, $panel->home());
	}

	public function testIsStateRequest(): void
	{
		// standard request
		$result = $this->app->panel()->isStateRequest();
		$this->assertFalse($result);

		// state request via get
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'_json' => true
				]
			]
		]);

		$result = $this->app->panel()->isStateRequest();
		$this->assertTrue($result);

		// state request via header
		$this->app = $this->app->clone([
			'request' => [
				'headers' => [
					'X-Panel' => true
				]
			]
		]);

		$result = $this->app->panel()->isStateRequest();
		$this->assertTrue($result);

		// other request than GET
		$this->app = $this->app->clone([
			'request' => [
				'method' => 'POST'
			]
		]);

		$result = $this->app->panel()->isStateRequest();
		$this->assertFalse($result);
	}

	public function testIsPanelUrl(): void
	{
		$panel = $this->app->panel();
		$this->assertTrue($panel->isPanelUrl('/panel'));
		$this->assertTrue($panel->isPanelUrl('/panel/pages/test'));
		$this->assertFalse($panel->isPanelUrl('test'));
	}

	public function testJson(): void
	{
		$response = Panel::json($data = ['foo' => 'bar']);

		$this->assertSame('application/json', $response->type());
		$this->assertSame('true', $response->header('X-Panel'));
		$this->assertSame($data, json_decode($response->body(), true));
	}

	public function testMultilang(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'languages' => true
			]
		]);

		$panel = $this->app->panel();
		$this->assertTrue($panel->multilang());
	}

	public function testMultilangWithImplicitLanguageInstallation(): void
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

		$panel = $this->app->panel();
		$this->assertTrue($panel->multilang());
	}

	public function testMultilangDisabled(): void
	{
		$panel = $this->app->panel();
		$this->assertFalse($panel->multilang());
	}

	public function testPath(): void
	{
		$panel = $this->app->panel();
		$this->assertSame('site', $panel->path('/panel/site'));
		$this->assertSame('pages/test', $panel->path('/panel/pages/test'));
		$this->assertSame('', $panel->path('/test/page'));
	}

	public function testRouter(): void
	{
		$panel = $this->app->panel();
		$this->assertInstanceOf(Router::class, $panel->router());
	}

	public function testRouterWithDisabledPanel(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'panel' => false
			]
		]);

		$panel = $this->app->panel();
		$this->assertNull($panel->router());
	}
}
