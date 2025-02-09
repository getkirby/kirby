<?php

namespace Kirby\Cms;

use Closure;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Loader::class)]
class LoaderTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';

	public Loader $loader;

	public function setUp(): void
	{
		parent::setUp();
		$this->loader = new Loader($this->app);
	}

	public function testArea()
	{
		$area = $this->loader->area('site');

		$this->assertSame('Site', $area['label']);
	}

	public function testAreaPlugin()
	{
		$this->app = $this->app->clone([
			'areas' => [
				'todos' => [
					'label' => 'Todos'
				]
			]
		]);

		$area = $this->app->load()->area('todos');

		$this->assertSame('Todos', $area['label']);
	}

	public function testAreaCorePlugin()
	{
		$this->app = $this->app->clone([
			'areas' => [
				'site' => [
					'label' => 'Seite'
				]
			]
		]);

		$area = $this->app->load()->area('site');

		$this->assertSame('Seite', $area['label']);
	}

	public function testAreaDropdownPlugin()
	{
		$this->app = $this->app->clone([
			'areas' => [
				'site' => [
					'dropdowns' => [
						'page' => fn () => 'foo'
					]
				]
			]
		]);

		$area = $this->app->load()->area('site');
		$dropdown = $area['dropdowns']['page'];

		$this->assertSame('pages/(:any)', $dropdown['pattern']);
		$this->assertSame('foo', $dropdown['options']());
	}

	public function testAreas()
	{
		$areas = $this->loader->areas();

		$this->assertSame('Your account', $areas['account']['label']);
		$this->assertSame('Installation', $areas['installation']['label']);
		$this->assertSame('Log in', $areas['login']['label']);
		$this->assertSame('System', $areas['system']['label']);
		$this->assertSame('Site', $areas['site']['label']);
		$this->assertSame('Users', $areas['users']['label']);
	}

	public function testComponent()
	{
		$component = $this->loader->component('url');

		$this->assertInstanceOf('Closure', $component);
	}

	public function testComponents()
	{
		$components = $this->loader->components();

		$this->assertArrayHasKey('url', $components);
	}

	public function testExtension()
	{
		$extension = $this->loader->extension('tags', 'video');

		$this->assertArrayHasKey('attr', $extension);
		$this->assertInstanceOf('Closure', $extension['html']);
	}

	public function testExtensions()
	{
		$extensions = $this->loader->extensions('tags');

		$this->assertArrayHasKey('image', $extensions);
		$this->assertArrayHasKey('video', $extensions);
	}

	public function testResolveArray()
	{
		$resolved = $this->loader->resolve([
			'test' => 'Test'
		]);

		$this->assertSame('Test', $resolved['test']);
	}

	public function testResolveArea()
	{
		$resolved = $this->loader->resolveArea([
			'dropdowns' => [
				'test' => function () {
				}
			]
		]);

		$this->assertInstanceOf(
			Closure::class,
			$resolved['dropdowns']['test']['options']
		);
	}

	public function testResolveClosure()
	{
		$resolved = $this->loader->resolve(fn () => [
			'test' => 'Test'
		]);

		$this->assertSame('Test', $resolved['test']);
	}

	public function testResolvePHPFile()
	{
		$resolved = $this->loader->resolve(static::FIXTURES . '/resolve.php');

		$this->assertSame('Test', $resolved['test']);
	}

	public function testResolveYamlFile()
	{
		$resolved = $this->loader->resolve(static::FIXTURES . '/resolve.yml');

		$this->assertSame('Test', $resolved['test']);
	}

	public function testResolveAll()
	{
		$resolved = $this->loader->resolveAll([
			'test' => static::FIXTURES . '/resolve.php'
		]);

		$this->assertSame('Test', $resolved['test']['test']);
	}

	public function testSection()
	{
		$section = $this->loader->section('pages');

		$this->assertArrayHasKey('props', $section);
		$this->assertArrayHasKey('computed', $section);
		$this->assertArrayHasKey('methods', $section);
	}

	public function testSections()
	{
		$sections = $this->loader->sections();

		$this->assertArrayHasKey('pages', $sections);
		$this->assertArrayHasKey('info', $sections);
	}

	public function testWithPlugins()
	{
		$loader = new Loader($this->app);
		$this->assertTrue($loader->withPlugins());

		$loader = new Loader($this->app, false);
		$this->assertFalse($loader->withPlugins());
	}
}
