<?php

namespace Kirby\Form\Field;

class TagsFieldTest extends TestCase
{
	public function testDefaultProps(): void
	{
		$field = $this->field('tags');

		$this->assertSame('tags', $field->type());
		$this->assertSame('tags', $field->name());
		$this->assertSame('all', $field->accept());
		$this->assertSame([], $field->value());
		$this->assertSame([], $field->default());
		$this->assertSame([], $field->options());
		$this->assertNull($field->min());
		$this->assertNull($field->max());
		$this->assertSame(',', $field->separator());
		$this->assertSame('tag', $field->icon());
		$this->assertNull($field->counter());
		$this->assertTrue($field->save());
	}

	public function testFillWithEmptyValue(): void
	{
		$field = $this->field('tags');
		$field->fill($value = ['a', 'b', 'c']);

		$this->assertSame($value, $field->toFormValue());

		$field->fillWithEmptyValue();

		$this->assertSame([], $field->toFormValue());
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
				'text' => '&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;',
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

		$field = $this->field('tags', [
			'model'   => $app->page('b'),
			'options' => 'query',
			'query'   => 'page.siblings.pluck("tags", ",", true)',
		]);

		$this->assertSame($expected, $field->options());

		$field = $this->field('tags', [
			'model'   => $app->file('a/b.jpg'),
			'options' => 'query',
			'query'   => 'file.siblings.pluck("tags", ",", true)',
		]);

		$this->assertSame($expected, $field->options());
	}

	public function testMin(): void
	{
		$field = $this->field('tags', [
			'value'   => 'a',
			'options' => ['a', 'b', 'c'],
			'min'     => 2
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('min', $field->errors());
		$this->assertSame(2, $field->min());
		$this->assertTrue($field->required());
	}

	public function testMax(): void
	{
		$field = $this->field('tags', [
			'value'   => 'a, b',
			'options' => ['a', 'b', 'c'],
			'max'     => 1
		]);

		$this->assertFalse($field->isValid());
		$this->assertSame(1, $field->max());
		$this->assertArrayHasKey('max', $field->errors());
	}

	public function testRequiredProps(): void
	{
		$field = $this->field('tags', [
			'options'  => ['a', 'b', 'c'],
			'required' => true
		]);

		$this->assertTrue($field->required());
		$this->assertSame(1, $field->min());
	}

	public function testRequiredInvalid(): void
	{
		$field = $this->field('tags', [
			'options'  => ['a', 'b', 'c'],
			'value'    => null,
			'required' => true
		]);

		$this->assertFalse($field->isValid());
	}

	public function testRequiredValid(): void
	{
		$field = $this->field('tags', [
			'options'  => ['a', 'b', 'c'],
			'required' => true,
			'value'    => 'a'
		]);

		$this->assertTrue($field->isValid());
	}
}
