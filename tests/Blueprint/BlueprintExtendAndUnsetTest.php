<?php

namespace Kirby\Blueprint;

use Exception;
use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\TestCase;

class BlueprintExtendAndUnsetTest extends TestCase
{
	protected function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'blueprints' => [
				'pages/base' => [
					'title'  => 'base',
					'model'  => 'page',
					'tabs'   => [
						'content' => [
							'fields' => [
								'pages' => [
									'type' => 'pagelist'
								],
								'files' => [
									'type' => 'filelist'
								]
							]
						],
						'additional' => [
							'columns' => [
								'left' => [
									'width'  => '1/2',
									'fields' => [
										'headline' => [
											'label' => 'Headline',
											'type' => 'text'
										],
									]
								],
								'right' => [
									'width'  => '1/2',
									'fields' => [
										'text' => [
											'label' => 'Text',
											'type' => 'text'
										]
									]
								]
							]
						],
						'seo' => [
							'fields' => [
								'seoTitle' => [
									'label' => 'SEO Title',
									'type' => 'text'
								],
								'seoDescription' => [
									'label' => 'SEO Description',
									'type' => 'text'
								]
							]
						]
					]
				]
			]
		]);
	}

	public function testExtendAndUnsetTab(): void
	{
		$blueprint = new Blueprint([
			'title'  => 'extended',
			'model'  => new Page(['slug' => 'test']),
			'extends' => 'pages/base',
			'tabs'  => [
				'seo'  => false
			]
		]);

		$this->assertSame('extended', $blueprint->title());
		$this->assertCount(2, $blueprint->tabs());
		$this->assertInstanceOf(Tab::class, $blueprint->tab('content'));
		$this->assertNull($blueprint->tab('seo'));
	}

	public function testExtendAndUnsetFieldInTab(): void
	{
		$blueprint = new Blueprint([
			'title'  => 'extended',
			'model'  => new Page(['slug' => 'test']),
			'extends' => 'pages/base',
			'tabs'  => [
				'content' => [
					'fields' => [
						'files' => false
					]
				]
			]
		]);

		try {
			$fields = $blueprint->tab('content')->columns()[0]['fields'];
		} catch (Exception $e) {
			$this->assertNull($e->getMessage(), 'Failed to get fields.');
		}

		$this->assertSame('extended', $blueprint->title());
		$this->assertIsArray($fields);
		$this->assertCount(1, $fields);
		$this->assertArrayHasKey('pages', $fields);
		$this->assertArrayNotHasKey('files', $fields);
	}

	public function testExtendAndUnsetFields(): void
	{
		$blueprint = new Blueprint([
			'title'  => 'extended',
			'model'  => new Page(['slug' => 'test']),
			'extends' => 'pages/base',
			'tabs'  => [
				'seo' => [
					'fields' => [
						'seoDescription' => false
					]
				]
			]
		]);

		try {
			$fields = $blueprint->tab('seo')->columns()[0]['fields'];
		} catch (Exception $e) {
			$this->assertNull($e->getMessage(), 'Failed to get fields.');
		}

		$this->assertSame('extended', $blueprint->title());
		$this->assertIsArray($fields);
		$this->assertCount(1, $fields);
		$this->assertArrayHasKey('seoTitle', $fields);
		$this->assertArrayNotHasKey('seoDescription', $fields);
	}

	public function testExtendAndUnsetColumns(): void
	{
		$blueprint = new Blueprint([
			'title'   => 'extended',
			'model'   => new Page(['slug' => 'test']),
			'extends' => 'pages/base',
			'tabs'    => [
				'additional' => [
					'columns' => [
						'left' => [
							'width' => '1/1'
						],
						'right' => false
					]
				]
			]
		]);

		$columns = $blueprint->tab('additional')->columns();

		$this->assertCount(1, $columns);
		$this->assertArrayHasKey('left', $columns);
		$this->assertArrayNotHasKey('right', $columns);
		$this->assertSame('1/1', $columns['left']['width']);
	}
}
