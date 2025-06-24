<?php

namespace Kirby\Cms;

class BlueprintPresetsTest extends TestCase
{
	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);
	}

	public function load($preset)
	{
		return include $this->app->root('kirby') . '/config/presets/' . $preset . '.php';
	}

	/**
	 * Page
	 */
	public function testPagePresetDefault()
	{
		$preset = $this->load('page');

		// default setup
		$props = $preset([]);

		$expected = [
			'columns' => [
				[
					'width'  => '2/3',
					'fields' => []
				],
				[
					'width' => '1/3',
					'sections' => [
						'pages' => [
							'label'  => 'Pages',
							'type'   => 'pages',
							'status' => 'all',
							'layout' => 'list'
						],
						'files' => [
							'label' => 'Files',
							'type'  => 'files',
							'layout' => 'list'
						]
					]
				]
			]
		];

		$this->assertSame($expected, $props);
	}

	public function testPagePresetNoFiles()
	{
		$preset = $this->load('page');

		// default setup
		$props = $preset([
			'files' => false
		]);

		$expected = [
			'columns' => [
				[
					'width'  => '2/3',
					'fields' => []
				],
				[
					'width' => '1/3',
					'sections' => [
						'pages' => [
							'label'  => 'Pages',
							'type'   => 'pages',
							'status' => 'all',
							'layout' => 'list'
						]
					]
				]
			]
		];

		$this->assertSame($expected, $props);
	}

	public function testPagePresetNoPages()
	{
		$preset = $this->load('page');

		// default setup
		$props = $preset([
			'pages' => false
		]);

		$expected = [
			'columns' => [
				[
					'width'  => '2/3',
					'fields' => []
				],
				[
					'width' => '1/3',
					'sections' => [
						'files' => [
							'label'  => 'Files',
							'type'   => 'files',
							'layout' => 'list'
						]
					]
				]
			]
		];

		$this->assertSame($expected, $props);
	}

	public function testPagePresetNoSidebar()
	{
		$preset = $this->load('page');

		// default setup
		$props = $preset([
			'pages' => false,
			'files' => false
		]);

		$expected = [
			'fields' => [],
		];

		$this->assertSame($expected, $props);
	}

	public function testPagePresetCustomSidebar()
	{
		$preset = $this->load('page');

		// default setup
		$props = $preset([
			'sidebar' => [
				'test' => [
					'label' => 'Test',
					'type'  => 'pages'
				]
			]
		]);

		$expected = [
			'columns' => [
				[
					'width'  => '2/3',
					'fields' => []
				],
				[
					'width' => '1/3',
					'sections' => [
						'test' => [
							'label' => 'Test',
							'type'  => 'pages',
						]
					]
				]
			]
		];

		$this->assertSame($expected, $props);
	}

	/**
	 * Pages
	 */
	public function testPagesPresetDefault()
	{
		$preset = $this->load('pages');

		// default setup
		$props = $preset([]);

		$expected = [
			'sections' => [
				'drafts' => [
					'label'  => 'Drafts',
					'type'   => 'pages',
					'layout' => 'list',
					'status' => 'drafts',
				],
				'listed' => [
					'label'  => 'Published',
					'type'   => 'pages',
					'layout' => 'list',
					'status' => 'listed',
				]
			]
		];

		$this->assertSame($expected, $props);
	}

	public function testPagesPresetWithUnlisted()
	{
		$preset = $this->load('pages');

		// default setup
		$props = $preset([
			'unlisted' => true
		]);

		$expected = [
			'sections' => [
				'drafts' => [
					'label'  => 'Drafts',
					'type'   => 'pages',
					'layout' => 'list',
					'status' => 'drafts',
				],
				'unlisted' => [
					'label'  => 'Unlisted',
					'type'   => 'pages',
					'layout' => 'list',
					'status' => 'unlisted',
				],
				'listed' => [
					'label'  => 'Published',
					'type'   => 'pages',
					'layout' => 'list',
					'status' => 'listed',
				]
			]
		];

		$this->assertSame($expected, $props);
	}

	/**
	 * Files
	 */
	public function testFilesPresetDefault()
	{
		$preset = $this->load('files');

		// default setup
		$props = $preset([]);

		$expected = [
			'sections' => [
				'files' => [
					'label'    => 'Files',
					'type'     => 'files',
					'layout'   => 'cards',
					'info'     => '{{ file.dimensions }}',
					'template' => null,
					'image'    => null
				]
			]
		];

		$this->assertEquals($expected, $props); // cannot use strict assertion (array order)
	}

	public function testFilesPresetWithLabel()
	{
		$preset = $this->load('files');

		// default setup
		$props = $preset([
			'label' => 'Images'
		]);

		$expected = [
			'sections' => [
				'files' => [
					'label'    => 'Images',
					'type'     => 'files',
					'layout'   => 'cards',
					'info'     => '{{ file.dimensions }}',
					'template' => null,
					'image'    => null
				]
			]
		];

		$this->assertEquals($expected, $props); // cannot use strict assertion (array order)
	}

	public function testFilesPresetWithLayout()
	{
		$preset = $this->load('files');

		// default setup
		$props = $preset([
			'layout' => 'list'
		]);

		$expected = [
			'sections' => [
				'files' => [
					'label'    => 'Files',
					'type'     => 'files',
					'layout'   => 'list',
					'info'     => '{{ file.dimensions }}',
					'template' => null,
					'image'    => null
				]
			]
		];

		$this->assertEquals($expected, $props); // cannot use strict assertion (array order)
	}

	public function testFilesPresetWithTemplate()
	{
		$preset = $this->load('files');

		// default setup
		$props = $preset([
			'template' => 'image'
		]);

		$expected = [
			'sections' => [
				'files' => [
					'label'    => 'Files',
					'type'     => 'files',
					'layout'   => 'cards',
					'info'     => '{{ file.dimensions }}',
					'template' => 'image',
					'image'    => null
				]
			]
		];

		$this->assertEquals($expected, $props); // cannot use strict assertion (array order)
	}

	public function testFilesPresetWithImage()
	{
		$preset = $this->load('files');

		// default setup
		$props = $preset([
			'image' => 'icon'
		]);

		$expected = [
			'sections' => [
				'files' => [
					'label'    => 'Files',
					'type'     => 'files',
					'layout'   => 'cards',
					'info'     => '{{ file.dimensions }}',
					'template' => null,
					'image'    => 'icon'
				]
			]
		];

		$this->assertEquals($expected, $props); // cannot use strict assertion (array order)
	}
}
