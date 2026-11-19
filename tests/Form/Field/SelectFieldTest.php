<?php

namespace Kirby\Form\Field;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SelectField::class)]
class SelectFieldTest extends TestCase
{
	public function testOptionsQuery(): void
	{
		$app = $this->app()->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'content'  => [
							'tags' => 'design'
						],
						'files' => [
							[
								'filename' => 'a.jpg',
								'content'  => [
									'tags' => 'design'
								]
							],
							[
								'filename' => 'b.jpg',
								'content'  => [
									'tags' => 'photography, <script>alert("XSS")</script>'
								]
							],
							[
								'filename' => 'c.jpg',
								'content'  => [
									'tags' => 'design, architecture'
								]
							]
						]
					],
					[
						'slug' => 'b',
						'content'  => [
							'tags' => 'photography, <script>alert("XSS")</script>'
						],
					],
					[
						'slug' => 'c',
						'content'  => [
							'tags' => 'design, architecture'
						],
					]
				]
			]
		]);

		$expected = [
			[
				'disabled' => false,
				'icon' => null,
				'info' => null,
				'text' => 'design',
				'value' => 'design'
			],
			[
				'disabled' => false,
				'icon' => null,
				'info' => null,
				'text' => 'photography',
				'value' => 'photography'
			],
			[
				'disabled' => false,
				'icon' => null,
				'info' => null,
				// safe because the select field does not render HTML
				'text' => '<script>alert("XSS")</script>',
				'value' => '<script>alert("XSS")</script>'
			],
			[
				'disabled' => false,
				'icon' => null,
				'info' => null,
				'text' => 'architecture',
				'value' => 'architecture'
			]
		];

		$field = $this->field('select', [
			'model'   => $app->page('b'),
			'options' => [
				'type'  => 'query',
				'query' => 'page.siblings.pluck("tags", ",", true)',
			],
		]);

		$this->assertSame($expected, $field->options());

		$field = $this->field('select', [
			'model'   => $app->file('a/b.jpg'),
			'options' => [
				'type'  => 'query',
				'query' => 'file.siblings.pluck("tags", ",", true)',
			],
		]);

		$this->assertSame($expected, $field->options());
	}

	public function testOptionsQueryAdditionalData(): void
	{
		$app = $this->app()->clone([
			'site' => [
				'children' => [
					[
						'slug'    => 'a',
						'content' => [
							'title'    => 'Title A',
							'icon'     => 'page',
							'headline' => 'some a headline'
						]
					],
					[
						'slug'    => 'b',
						'content' => [
							'title'    => 'Title B',
							'icon'     => 'user',
							'headline' => 'some b headline'
						],
					],
					[
						'slug'    => 'c',
						'content' => [
							'title'    => 'Title C',
							'icon'     => 'file',
							'headline' => 'some c headline'
						],
					]
				]
			]
		]);

		$app->impersonate('kirby');

		$expected = [
			[
				'disabled' => false,
				'icon'     => 'page',
				'info'     => 'some a headline',
				'text'     => 'Title A',
				'value'    => 'a'
			],
			[
				'disabled' => false,
				'icon'     => 'user',
				'info'     => 'some b headline',
				'text'     => 'Title B',
				'value'    => 'b'
			],
			[
				'disabled' => false,
				'icon'     => 'file',
				'info'     => 'some c headline',
				'text'     => 'Title C',
				'value'    => 'c'
			]
		];

		$field = $this->field('select', [
			'options' => [
				'type'  => 'query',
				'query' => 'site.children',
				'info'  => '{{ item.headline }}',
				'icon'  => '{{ item.icon }}',
				'text'  => '{{ item.title }}',
				'value' => '{{ item.slug }}',
			]
		]);

		$this->assertSame($expected, $field->options());
	}

	public function testOptionsQueryWithProtectedPages(): void
	{
		$app   = $this->appWithProtectedPages();
		$field = $this->field('select', [
			'model'   => $app->site(),
			'options' => 'site.children'
		]);

		// pages that must not be listed are hidden
		$this->assertSame(['a'], array_column($field->options(), 'value'));

		// ... unless they are in the value, then they are kept as placeholder
		$field->fill('b');

		$this->assertSame(['a', 'b'], array_column($field->options(), 'value'));
		$this->assertSame([
			'disabled' => false,
			'icon'     => null,
			'info'     => null,
			'text'     => '[unknown]',
			'value'    => 'b'
		], $field->options()[1]);
		$this->assertTrue($field->isValid());
		$this->assertSame('b', $field->toStoredValue());

		// the placeholder follows the value
		$field->fill('a');
		$this->assertSame(['a'], array_column($field->options(), 'value'));

		// unknown values stay invalid
		$field->fill('x');
		$this->assertFalse($field->isValid());

		// the default value keeps its placeholder as well
		$field = $this->field('select', [
			'model'   => $app->site(),
			'options' => 'site.children',
			'default' => 'c'
		]);

		$this->assertSame(['a', 'c'], array_column($field->options(), 'value'));
	}

	public function testProps(): void
	{
		$field = $this->field('select');
		$props = $field->props();

		ksort($props);

		$expected = [
			'autofocus'   => false,
			'disabled'    => false,
			'help'        => null,
			'hidden'      => false,
			'icon'        => null,
			'label'       => 'Select',
			'name'        => 'select',
			'options'     => [],
			'placeholder' => '—',
			'required'    => false,
			'saveable'    => true,
			'translate'   => true,
			'type'        => 'select',
			'when'	      => null,
			'width'	      => '1/1',
		];

		$this->assertSame($expected, $props);
	}
}
