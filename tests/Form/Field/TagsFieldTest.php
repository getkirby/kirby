<?php

namespace Kirby\Form\Field;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TagsField::class)]
class TagsFieldTest extends TestCase
{
	public function testDefault(): void
	{
		$field = $this->field('tags');
		$this->assertSame([], $field->default());

		$field = $this->field('tags', [
			'default' => ['a', 'b', 'c']
		]);
		$this->assertSame(['a', 'b', 'c'], $field->default());
	}

	public function testIsValid(): void
	{
		$field = $this->field('tags', [
			'options'  => ['a', 'b', 'c'],
			'value'    => null,
			'required' => true
		]);
		$this->assertFalse($field->isValid());

		$field = $this->field('tags', [
			'options'  => ['a', 'b', 'c'],
			'required' => true,
			'value'    => 'a'
		]);
		$this->assertTrue($field->isValid());
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
		$this->assertTrue($field->isRequired());
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
			'options' => [
				'type'  => 'query',
				'query' => 'page.siblings.pluck("tags", ",", true)',
			]
		]);

		$this->assertSame($expected, $field->options());

		$field = $this->field('tags', [
			'model'   => $app->file('a/b.jpg'),
			'options' => [
				'type'  => 'query',
				'query' => 'file.siblings.pluck("tags", ",", true)',
			],
		]);

		$this->assertSame($expected, $field->options());
	}

	public function testProps(): void
	{
		$field = $this->field('tags');
		$props = $field->props();

		ksort($props);

		$expected = [
			'accept'    => 'all',
			'autofocus' => false,
			'disabled'  => false,
			'help'      => null,
			'hidden'    => false,
			'icon'      => 'tag',
			'label'     => 'Tags',
			'layout'    => null,
			'max'       => null,
			'min'       => null,
			'name'      => 'tags',
			'options'   => [],
			'required'  => false,
			'saveable'  => true,
			'search'    => true,
			'separator' => ',',
			'sort'      => false,
			'required'  => false,
			'translate' => true,
			'type'      => 'tags',
			'when'      => null,
			'width'     => '1/1',
		];

		$this->assertSame($expected, $props);
	}

	public function testRequired(): void
	{
		$field = $this->field('tags', [
			'options'  => ['a', 'b', 'c'],
			'required' => true
		]);

		$this->assertTrue($field->required());
		$this->assertSame(1, $field->min());
	}

	public function testReset(): void
	{
		$field = $this->field('tags');
		$field->fill($value = ['a', 'b', 'c']);

		$this->assertSame($value, $field->toFormValue());

		$field->reset();

		$this->assertSame([], $field->toFormValue());
	}

	public function testToFormValue(): void
	{
		$field = $this->field('tags', [
			'value' => ['a', 'b', 'c'],
		]);

		$this->assertSame(['a', 'b', 'c'], $field->toFormValue());
	}

	public function testToStoredValue(): void
	{
		$field = $this->field('tags', [
			'value' => ['a', 'b', 'c'],
		]);

		$this->assertSame('a, b, c', $field->toStoredValue());
	}
}
