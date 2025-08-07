<?php

namespace Kirby\Form\Field;

use PHPUnit\Framework\Attributes\DataProvider;

class SelectFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('select');

		$this->assertSame('select', $field->type());
		$this->assertSame('select', $field->name());
		$this->assertSame('', $field->value());
		$this->assertNull($field->icon());
		$this->assertSame([], $field->options());
		$this->assertTrue($field->save());
	}

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
			'options' => 'query',
			'query'   => 'page.siblings.pluck("tags", ",", true)',
		]);

		$this->assertSame($expected, $field->options());

		$field = $this->field('select', [
			'model'   => $app->file('a/b.jpg'),
			'options' => 'query',
			'query'   => 'file.siblings.pluck("tags", ",", true)',
		]);

		$this->assertSame($expected, $field->options());
	}

	public function testOptionsQueryAdditionalData(): void
	{
		$this->app()->clone([
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

	public static function valueInputProvider(): array
	{
		return [
			['a', 'a'],
			['b', 'b'],
			['c', 'c'],
			['d', ''],
			['1', '1'],
			['2', '2'],
			['3', '']
		];
	}

	#[DataProvider('valueInputProvider')]
	public function testValue($input, $expected): void
	{
		$field = $this->field('select', [
			'options' => [
				'a',
				'b',
				'c',
				1,
				2
			],
			'value' => $input
		]);

		$this->assertSame($expected, $field->value());
	}
}
