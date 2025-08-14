<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Cms\Site;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Button\ViewButtons;
use Kirby\Panel\Ui\View;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ModelViewController::class)]
#[CoversClass(SiteViewController::class)]
class SiteViewControllerTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Controller.View.SiteViewController';

	protected Site $site;

	public function setUp(): void
	{
		parent::setUp();

		$this->app->impersonate('kirby');
		$this->site = $this->app->site();
	}

	public function testButtons(): void
	{
		$controller = new SiteViewController($this->site);
		$buttons    = $controller->buttons();
		$this->assertInstanceOf(ViewButtons::class, $buttons);
		$this->assertCount(3, $buttons->render());
	}

	public function testComponent(): void
	{
		$controller = new SiteViewController($this->site);
		$this->assertSame('k-site-view', $controller->component());
	}

	public function testFactory(): void
	{
		$controller = SiteViewController::factory();
		$this->assertInstanceOf(SiteViewController::class, $controller);
		$this->assertSame($this->site, $controller->model());
	}

	public function testLoad(): void
	{
		$controller = new SiteViewController($this->site);
		$view       = $controller->load();
		$this->assertInstanceOf(View::class, $view);
		$this->assertSame('k-site-view', $view->component);

		$props = $view->props();
		$this->assertSame('site', $props['blueprint']);
		$this->assertSame('/site', $props['link']);

		// inherited props
		$this->assertArrayHasKey('blueprint', $props);
		$this->assertArrayHasKey('lock', $props);
		$this->assertArrayHasKey('permissions', $props);
		$this->assertArrayHasKey('tab', $props);
		$this->assertArrayHasKey('tabs', $props);
		$this->assertArrayHasKey('title', $props);
		$this->assertArrayHasKey('versions', $props);
	}

	public function testTitle(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'site' => [
					'title' => 'My Blog'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new SiteViewController($this->app->site());
		$this->assertSame('My Blog', $controller->title());
	}

	public function testTitleMultilang(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'site' => [
					'title' => [
						'de' => 'Mein Blog',
						'en' => 'My Blog',
					],
				]
			],
			'languages' => [
				[
					'code'    => 'en',
					'name'    => 'English',
				],
				[
					'default' => true,
					'code'    => 'de',
					'name'    => 'Deutsch'
				]
			],
		]);

		$this->app->impersonate('kirby');

		$controller = new SiteViewController($this->app->site());
		$this->assertSame('Mein Blog', $controller->title());
	}
}
