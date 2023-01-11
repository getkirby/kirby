<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class LayoutMixinTest extends TestCase
{
	protected $app;
	protected $page;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$this->page = new Page(['slug' => 'test']);

		Section::$types['test'] = Section::$types['pages'] = [
			'mixins' => ['layout'],
			'props'  => $props = [
				'info' => function (string $info = null) {
					return $info;
				},
				'text' => function (string $text = null) {
					return $text;
				}
			]
		];
	}

	public function testColumns()
	{
		$section = new Section('test', [
			'model' => $this->page,
		]);

		$this->assertSame([], $section->columns());
	}

	public function testColumnsWithTableLayout()
	{
		$section = new Section('test', [
			'model'  => $this->page,
			'layout' => 'table'
		]);

		$expected = [
			'image' => [
				'label'  => ' ',
				'mobile' => true,
				'type'   => 'image',
				'width'  => 'var(--table-row-height)'
			]
		];

		$this->assertSame($expected, $section->columns());
	}

	public function testColumnsWithText()
	{
		$section = new Section('test', [
			'model'  => $this->page,
			'text'   => '{{ page.title }}',
			'layout' => 'table'
		]);

		$expected = [
			'image' => [
				'label'  => ' ',
				'mobile' => true,
				'type'   => 'image',
				'width'  => 'var(--table-row-height)'
			],
			'title' => [
				'label'  => 'Title',
				'mobile' => true,
				'type'   => 'url'
			]
		];

		$this->assertSame($expected, $section->columns());
	}

	public function testColumnsWithTextAndInfo()
	{
		$section = new Section('test', [
			'model'  => $this->page,
			'text'   => '{{ page.title }}',
			'info'   => '{{ page.date }}',
			'layout' => 'table'
		]);

		$expected = [
			'image' => [
				'label'  => ' ',
				'mobile' => true,
				'type'   => 'image',
				'width'  => 'var(--table-row-height)',
			],
			'title' => [
				'label'  => 'Title',
				'mobile' => true,
				'type'   => 'url'
			],
			'info' => [
				'label'  => 'Info',
				'type'   => 'text'
			]
		];

		$this->assertSame($expected, $section->columns());
	}

	public function testColumnsWithFlag()
	{
		$section = new Section('pages', [
			'model'  => $this->page,
			'layout' => 'table'
		]);

		$expected = [
			'image' => [
				'label'  => ' ',
				'mobile' => true,
				'type'   => 'image',
				'width'  => 'var(--table-row-height)',
			],
			'flag' => [
				'label'  => ' ',
				'mobile' => true,
				'type'   => 'flag',
				'width'  => 'var(--table-row-height)'
			]
		];

		$this->assertSame($expected, $section->columns());
	}

	public function testColumnsWithCustomColumns()
	{
		$section = new Section('test', [
			'model'   => $this->page,
			'layout'  => 'table',
			'columns' => [
				'date' => [
					'label' => 'Date',
					'type'  => 'date'
				],
				'simple' => true,
				'removed' => false,
				'translated' => [
					'label' => [
						'en' => 'Translated',
						'de' => 'Übersetzt'
					]
				]
			]
		]);

		$expected = [
			'image' => [
				'label' => ' ',
				'mobile' => true,
				'type'  => 'image',
				'width' => 'var(--table-row-height)',
			],
			'dateCell' => [
				'label' => 'Date',
				'type'  => 'date',
				'id'    => 'date'
			],
			'simpleCell' => [
				'label' => 'Simple',
				'id'    => 'simple'
			],
			'translatedCell' => [
				'label' => 'Translated',
				'id'    => 'translated'
			]
		];

		$this->assertSame($expected, $section->columns());
	}

	public function testColumnsValues()
	{
		$model = new Page([
			'slug' => 'test',
			'content' => [
				'title' => 'Test Page',
				'date'  => '2012-12-12',
				'html'  => '<i>Some HTML</i>'
			]
		]);

		$section = new Section('test', [
			'model' => $model,
			'text'  => '{{ page.title }}',
			'info'  => '{{ page.slug }}',
			'layout' => 'table',
			'columns' => [
				'date' => [
					'label' => 'Date',
					'type'  => 'date'
				],
				'html' => [
					'label' => 'HTML',
					'type'  => 'html',
					'value' => '{{ page.html }}'
				],
				'removed' => false
			]
		]);

		$item = [
			'text' => 'Test Page',
			'info' => 'test'
		];

		$expected = [
			'text' => 'Test Page',
			'info' => 'test',
			'title' => [
				'text' => 'Test Page',
				'href' => '/pages/test'
			],
			'image' => null,
			'dateCell' => '2012-12-12',
			'htmlCell' => '<i>Some HTML</i>',
		];

		$this->assertSame($expected, $section->columnsValues($item, $model));
	}


	public function testLayout()
	{
		// default
		$section = new Section('test', [
			'model' => $this->page,
		]);

		$this->assertSame('list', $section->layout());

		// custom
		$section = new Section('test', [
			'model'  => $this->page,
			'layout' => 'cardlets'
		]);

		$this->assertSame('cardlets', $section->layout());

		// invalid with fallback
		$section = new Section('test', [
			'model'  => $this->page,
			'layout' => 'foo'
		]);

		$this->assertSame('list', $section->layout());
	}

	public function testSize()
	{
		// default
		$section = new Section('test', [
			'model' => $this->page,
		]);

		$this->assertSame('auto', $section->size());

		// custom
		$section = new Section('test', [
			'model' => $this->page,
			'size'  => 'large'
		]);

		$this->assertSame('large', $section->size());
	}
}
