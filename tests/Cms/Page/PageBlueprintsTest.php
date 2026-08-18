<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class PageBlueprintsTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.PageBlueprints';

	public function testBlueprints(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/a' => [
					'title' => 'A'
				],
				'pages/b' => [
					'title' => 'B'
				],
				'pages/c' => [
					'title' => 'C'
				]
			],
			'templates' => [
				// the files just need to exist
				'a' => __FILE__,
				'c' => __FILE__
			]
		]);

		// no blueprints
		$page = new Page([
			'slug'     => 'test',
			'template' => 'a'
		]);

		$this->assertSame(['A'], array_column($page->blueprints(), 'title'));

		// two different blueprints
		$page = new Page([
			'slug' => 'test',
			'template' => 'c',
			'blueprint' => [
				'options' => [
					'template' => [
						'a',
						'b'
					]
				]
			]
		]);

		$this->assertSame(['C', 'A', 'B'], array_column($page->blueprints(), 'title'));

		// including the same blueprint
		$page = new Page([
			'slug' => 'test',
			'template' => 'a',
			'blueprint' => [
				'options' => [
					'template' => [
						'a',
						'b'
					]
				]
			]
		]);

		$this->assertSame(['A', 'B'], array_column($page->blueprints(), 'title'));

		// template option is simply true
		$page = new Page([
			'slug' => 'test',
			'template' => 'a',
			'blueprint' => [
				'options' => [
					'template' => true
				]
			]
		]);

		$this->assertSame(['A'], array_column($page->blueprints(), 'title'));
	}

	public function testBlueprintsInField(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/a' => [
					'title'  => 'A',
					'fields' => [
						'my-pages' => [
							'type'   => 'pagelist',
							'create' => 'b'
						]
					]
				],
				'pages/b' => [
					'title' => 'B'
				]
			],
			'templates' => [
				// the files just need to exist
				'a' => __FILE__
			]
		]);

		// no blueprints
		$page = new Page([
			'slug'     => 'test',
			'template' => 'a'
		]);

		$this->assertSame(['B'], array_column($page->blueprints('my-pages'), 'title'));
	}

	public function testBlueprintsWithInvalidBlueprint(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/a' => [
					'title' => 'A'
				],
				'pages/broken' => [
					'title'  => 'Not used',
					// a scalar instead of a list of fields
					// breaks the blueprint
					'fields' => 'text'
				]
			]
		]);

		$page = new Page([
			'slug'      => 'test',
			'template'  => 'a',
			'blueprint' => [
				'options' => [
					'template' => [
						'a',
						'broken'
					]
				]
			]
		]);

		// a blueprint that cannot be created still needs an entry,
		// based on its name, so the template stays selectable
		$this->assertSame(
			[
				['name' => 'a', 'title' => 'A'],
				['name' => 'broken', 'title' => 'Broken']
			],
			$page->blueprints()
		);
	}

	public function testBlueprintsWithMissingBlueprint(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/a' => [
					'title' => 'A'
				]
			]
		]);

		$page = new Page([
			'slug'      => 'test',
			'template'  => 'a',
			'blueprint' => [
				'options' => [
					'template' => [
						'a',
						'does-not-exist'
					]
				]
			]
		]);

		$this->assertSame(['A'], array_column($page->blueprints(), 'title'));
	}
}
