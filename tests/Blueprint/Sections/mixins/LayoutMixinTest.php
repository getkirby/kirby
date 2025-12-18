<?php

namespace Kirby\Blueprint;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\TestCase;

class LayoutMixinTest extends TestCase
{
	protected Page $page;

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
			'props'  => [
				'info' => fn (string|null $info = null) => $info,
				'text' => fn (string|null $text = null) => $text
			]
		];
	}

	public function testColumns(): void
	{
		$section = new Section('test', [
			'model' => $this->page,
		]);

		$this->assertSame([], $section->columns());
	}

	public function testColumnsWithTableLayout(): void
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

	public function testColumnsWithText(): void
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

	public function testColumnsWithTextAndInfo(): void
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

	public function testColumnsWithFlag(): void
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

	public function testColumnsWithCustomColumns(): void
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
			'date' => [
				'label' => 'Date',
				'type'  => 'date',
				'id'    => 'date'
			],
			'simple' => [
				'label' => 'Simple',
				'id'    => 'simple'
			],
			'translated' => [
				'label' => 'Translated',
				'id'    => 'translated'
			]
		];

		$this->assertSame($expected, $section->columns());
	}

	public function testColumnsValues(): void
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
			'date' => '2012-12-12',
			'html' => '<i>Some HTML</i>',
		];

		$this->assertSame($expected, $section->columnsValues($item, $model));
	}


	public function testLayout(): void
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

	public function testSize(): void
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
