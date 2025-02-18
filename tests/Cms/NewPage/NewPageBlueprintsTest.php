<?php

namespace Kirby\Cms;

use Kirby\Cms\NewPage as Page;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class NewPageBlueprintsTest extends NewPageTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewPageBlueprintsTest';

	public function testBlueprints()
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

	public function testBlueprintsInSection()
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/a' => [
					'title' => 'A',
					'sections' => [
						'my-pages' => [
							'type'   => 'pages',
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
}
