<?php

namespace Kirby\Cms;

/**
 * @coversDefaultClass \Kirby\Cms\Loader
 */
class LoaderTest extends TestCase
{
    public $loader;

    public function setUp(): void
    {
        parent::setUp();
        $this->loader = new Loader($this->app);
    }

    /**
     * @covers ::area
     */
    public function testArea()
    {
        $area = $this->loader->area('site');

        $this->assertSame('Site', $area['label']);
    }

    /**
     * @covers ::area
     * @covers ::areas
     */
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

    /**
     * @covers ::area
     * @covers ::areas
     */
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

    /**
     * @covers ::areas
     */
    public function testAreaDropdownPlugin()
    {
        $this->app = $this->app->clone([
            'areas' => [
                'site' => [
                    'dropdowns' => [
                        'page' => function () {
                            return 'foo';
                        }
                    ]
                ]
            ]
        ]);

        $area = $this->app->load()->area('site');
        $dropdown = $area['dropdowns']['page'];

        $this->assertSame('pages/(:any)', $dropdown['pattern']);
        $this->assertSame('foo', $dropdown['options']());
    }

    /**
     * @covers ::areas
     */
    public function testAreas()
    {
        $areas = $this->loader->areas();

        $this->assertSame('Your account', $areas['account']['label']);
        $this->assertSame('Installation', $areas['installation']['label']);
        $this->assertSame('Login', $areas['login']['label']);
        $this->assertSame('System', $areas['system']['label']);
        $this->assertSame('Site', $areas['site']['label']);
        $this->assertSame('Users', $areas['users']['label']);
    }

    /**
     * @covers ::component
     */
    public function testComponent()
    {
        $component = $this->loader->component('url');

        $this->assertInstanceOf('Closure', $component);
    }

    /**
     * @covers ::components
     */
    public function testComponents()
    {
        $components = $this->loader->components();

        $this->assertArrayHasKey('url', $components);
    }

    /**
     * @covers ::extension
     */
    public function testExtension()
    {
        $extension = $this->loader->extension('tags', 'video');

        $this->assertArrayHasKey('attr', $extension);
        $this->assertInstanceOf('Closure', $extension['html']);
    }

    /**
     * @covers ::extensions
     */
    public function testExtensions()
    {
        $extensions = $this->loader->extensions('tags');

        $this->assertArrayHasKey('image', $extensions);
        $this->assertArrayHasKey('video', $extensions);
    }

    /**
     * @covers ::resolve
     */
    public function testResolveArray()
    {
        $resolved = $this->loader->resolve([
            'test' => 'Test'
        ]);

        $this->assertSame('Test', $resolved['test']);
    }

    /**
     * @covers ::resolveArea
     */
    public function testResolveArea()
    {
        $resolved = $this->loader->resolveArea([
            'dropdowns' => [
                'test' => function () {
                }
            ]
        ]);

        $this->assertInstanceOf('Closure', $resolved['dropdowns']['test']['options']);
    }

    /**
     * @covers ::resolve
     */
    public function testResolveClosure()
    {
        $resolved = $this->loader->resolve(function () {
            return [
                'test' => 'Test'
            ];
        });

        $this->assertSame('Test', $resolved['test']);
    }

    /**
     * @covers ::resolve
     */
    public function testResolvePHPFile()
    {
        $resolved = $this->loader->resolve(__DIR__ . '/fixtures/resolve.php');

        $this->assertSame('Test', $resolved['test']);
    }

    /**
     * @covers ::resolve
     */
    public function testResolveYamlFile()
    {
        $resolved = $this->loader->resolve(__DIR__ . '/fixtures/resolve.yml');

        $this->assertSame('Test', $resolved['test']);
    }

    /**
     * @covers ::resolveAll
     */
    public function testResolveAll()
    {
        $resolved = $this->loader->resolveAll([
            'test' => __DIR__ . '/fixtures/resolve.php'
        ]);

        $this->assertSame('Test', $resolved['test']['test']);
    }

    /**
     * @covers ::section
     */
    public function testSection()
    {
        $section = $this->loader->section('pages');

        $this->assertArrayHasKey('props', $section);
        $this->assertArrayHasKey('computed', $section);
        $this->assertArrayHasKey('methods', $section);
    }

    /**
     * @covers ::sections
     */
    public function testSections()
    {
        $sections = $this->loader->sections();

        $this->assertArrayHasKey('pages', $sections);
        $this->assertArrayHasKey('info', $sections);
    }

    /**
     * @covers ::withPlugins
     */
    public function testWithPlugins()
    {
        $loader = new Loader($this->app);
        $this->assertTrue($loader->withPlugins());

        $loader = new Loader($this->app, false);
        $this->assertFalse($loader->withPlugins());
    }
}
