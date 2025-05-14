<?php

namespace Kirby\Panel\Areas;

class SiteTest extends AreaTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		$this->install();
	}

	public function testPageWithoutAuthentication(): void
	{
		$this->assertRedirect('pages/home', 'login');
	}

	public function testPageWithMissingModel(): void
	{
		$this->login();
		$this->assertErrorView('pages/does-not-exist', 'The page "does-not-exist" cannot be found');
	}

	public function testPage(): void
	{
		$this->login();

		$this->app->site()->createChild([
			'slug' => 'test',
			'content' => [
				'title' => 'Test'
			]
		]);

		$view  = $this->view('pages/test');
		$props = $view['props'];
		$model = $props['model'];

		$this->assertSame('default', $props['blueprint']);
		$this->assertSame([
			'isLegacy' => false,
			'isLocked' => false,
			'modified' => null,
			'user'     => [
				'id'    => 'test',
				'email' => 'test@getkirby.com'
			]
		], $props['lock']);
		$this->assertArrayNotHasKey('tab', $props);
		$this->assertSame([], $props['tabs']);

		$this->assertSame('Test', $props['versions']['latest']['title']);
		$this->assertSame('Test', $props['versions']['changes']['title']);

		// model
		$this->assertSame('test', $model['id']);
		$this->assertSame('draft', $model['status']);
		$this->assertSame('Test', $model['title']);

		$this->assertNull($props['next']);
		$this->assertNull($props['prev']);
	}

	public function testPageFileWithoutModel(): void
	{
		$this->login();

		$this->app->site()->createChild([
			'slug' => 'test',
			'content' => [
				'title' => 'Test'
			]
		]);

		$this->assertErrorView('pages/test/files/does-not-exist.jpg', 'The file "does-not-exist.jpg" cannot be found');
	}

	public function testPageFile(): void
	{
		$this->login();

		$this->app->site()->createChild([
			'slug' => 'test',
			'content' => [
				'title' => 'Test'
			],
			'files' => [
				[
					'filename' => 'test.jpg',
					'template' => 'image'
				]
			]
		]);

		$view  = $this->view('pages/test/files/test.jpg');
		$props = $view['props'];
		$model = $props['model'];

		$this->assertSame('image', $props['blueprint']);
		$this->assertSame([
			'isLegacy' => false,
			'isLocked' => false,
			'modified' => null,
			'user'     => [
				'id'    => 'test',
				'email' => 'test@getkirby.com'
			]
		], $props['lock']);
		$this->assertArrayNotHasKey('tab', $props);
		$this->assertSame([], $props['tabs']);
		$this->assertSame([], $props['versions']['changes']);
		$this->assertSame([], $props['versions']['latest']);

		// model
		$this->assertSame('jpg', $model['extension']);
		$this->assertSame('test.jpg', $model['filename']);
		$this->assertSame('test/test.jpg', $model['id']);
		$this->assertSame('image/jpeg', $model['mime']);
		$this->assertSame('0 KB', $model['niceSize']);
		$this->assertSame('pages/test', $model['parent']);
		$this->assertSame('image', $model['type']);

		$this->assertNull($props['next']);
		$this->assertNull($props['prev']);
	}

	public function testPagePreview(): void
	{
		$this->login();

		$page = $this->app->site()->createChild([
			'slug'    => 'test',
			'isDraft' => false,
			'content' => [
				'title' => 'Test'
			]
		]);

		$view  = $this->view('pages/test/preview/changes');
		$props = $view['props'];

		$token = $page->version('changes')->previewToken();

		$this->assertSame('k-preview-view', $view['component']);
		$this->assertSame('Test | Preview', $view['title']);
		$this->assertSame('/test?_token=' . $token . '&_version=changes', $props['src']);
	}

	public function testSiteWithoutAuthentication(): void
	{
		$this->assertRedirect('site', 'login');
	}

	public function testSiteRedirectFromHome(): void
	{
		$this->login();
		$this->assertRedirect('/', 'site');
	}

	public function testSite(): void
	{
		$this->login();

		$site = $this->app->site();

		$site->createChild([
			'slug'    => 'home',
			'isDraft' => false
		]);

		$view  = $this->view('site');
		$props = $view['props'];

		$this->assertSame('site', $view['id']);
		$this->assertSame('Site', $view['title']);
		$this->assertSame('k-site-view', $view['component']);

		$this->assertSame('site', $props['blueprint']);
		$this->assertSame('/site', $props['model']['link']);
		$this->assertSame('/', $props['model']['previewUrl']);
		$this->assertSame('', $props['model']['title']);
	}

	public function testSiteFile(): void
	{
		$this->app([
			'site' => [
				'files' => [
					[
						'filename' => 'test.jpg',
						'template' => 'image'
					]
				]
			]
		]);

		$this->login();

		$view  = $this->view('site/files/test.jpg');
		$props = $view['props'];
		$model = $props['model'];

		$this->assertSame('image', $props['blueprint']);
		$this->assertSame([
			'isLegacy' => false,
			'isLocked' => false,
			'modified' => null,
			'user'     => [
				'id'    => 'test',
				'email' => 'test@getkirby.com'
			]
		], $props['lock']);
		$this->assertArrayNotHasKey('tab', $props);
		$this->assertSame([], $props['tabs']);

		// model
		$this->assertSame([], $props['versions']['changes']);
		$this->assertSame([], $props['versions']['latest']);
		$this->assertSame('jpg', $model['extension']);
		$this->assertSame('test.jpg', $model['filename']);
		$this->assertSame('test.jpg', $model['id']);
		$this->assertSame('image/jpeg', $model['mime']);
		$this->assertSame('0 KB', $model['niceSize']);
		$this->assertSame('site', $model['parent']);
		$this->assertSame('image', $model['type']);

		$this->assertNull($props['next']);
		$this->assertNull($props['prev']);
	}

	public function testSitePreview(): void
	{
		$this->login();

		$site = $this->app->site();

		$site->createChild([
			'slug'    => 'home',
			'isDraft' => false
		]);

		$view  = $this->view('site/preview/changes');
		$props = $view['props'];

		$token = $site->version('changes')->previewToken();

		$this->assertSame('k-preview-view', $view['component']);
		$this->assertSame('Site | Preview', $view['title']);
		$this->assertSame('/?_token=' . $token . '&_version=changes', $props['src']);
	}

	public function testSiteTitle(): void
	{
		$this->app([
			'blueprints' => [
				'site' => [
					'title' => 'My Blog',
				]
			]
		]);

		$this->login();

		$view  = $this->view('site');

		$this->assertSame('site', $view['id']);
		$this->assertSame('My Blog', $view['title']);
	}

	public function testSiteTitleMultilang(): void
	{
		$this->app([
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

		$this->login();

		$view  = $this->view('site');

		$this->assertSame('site', $view['id']);
		$this->assertSame('Mein Blog', $view['title']);
	}
}
