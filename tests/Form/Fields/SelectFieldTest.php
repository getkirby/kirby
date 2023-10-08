<?php

namespace Kirby\Form\Fields;

class SelectFieldTest extends TestCase
{
	public function testDefaultProps()
	{
		$field = $this->field('select');

		$this->assertSame('select', $field->type());
		$this->assertSame('select', $field->name());
		$this->assertSame('', $field->value());
		$this->assertNull($field->icon());
		$this->assertSame([], $field->options());
		$this->assertTrue($field->save());
	}

	public function testOptionsQuery()
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

	public function valueInputProvider()
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

	/**
	 * @dataProvider valueInputProvider
	 */
	public function testValue($input, $expected)
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
