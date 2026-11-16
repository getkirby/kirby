<?php

namespace Kirby\Form\Fields;

use Kirby\Cms\App;

class CheckboxesFieldTest extends TestCase
{
	public function testDefaultProps()
	{
		$field = $this->field('checkboxes');

		$this->assertSame('checkboxes', $field->type());
		$this->assertSame('checkboxes', $field->name());
		$this->assertSame([], $field->value());
		$this->assertSame([], $field->options());
		$this->assertTrue($field->save());
	}

	public function testValue()
	{
		$field = $this->field('checkboxes', [
			'value'   => 'a,b,c',
			'options' => $expected = [
				'a',
				'b',
				'c'
			]
		]);

		$this->assertSame($expected, $field->value());
	}

	public function testEmptyValue()
	{
		$field = $this->field('checkboxes');

		$this->assertSame([], $field->value());
	}

	public function testDefaultValueWithInvalidOptions()
	{
		$field = $this->field('checkboxes', [
			'default' => 'a,b,d',
			'options' => [
				'a',
				'b',
				'c'
			],
		]);

		$this->assertSame(['a', 'b'], $field->default());
		$this->assertSame('a, b', $field->data(true));
	}

	public function testStringConversion()
	{
		$field = $this->field('checkboxes', [
			'options' => [
				'a',
				'b',
				'c'
			],
			'value' => 'a,b,c,d'
		]);

		$this->assertSame('a, b, c', $field->data());
	}

	public function testIgnoreInvalidOptions()
	{
		$field = $this->field('checkboxes', [
			'options' => [
				'a',
				'b',
				'c'
			],
			'value' => 'a, b, d'
		]);

		$this->assertSame(['a', 'b'], $field->value());
	}

	public function testIgnoreProtectedOptions(): void
	{
		// the permission cache is keyed by template and role,
		// so both need to be unique for this test
		$uuid = uuid();

		$app = new App([
			'blueprints' => [
				'pages/secret-' . $uuid => [
					'options' => ['list' => false]
				]
			],
			'roles' => [
				['name' => 'editor-' . $uuid]
			],
			'site' => [
				'children' => [
					['slug' => 'a'],
					[
						'slug'     => 'b',
						'template' => 'secret-' . $uuid
					],
					[
						'slug'     => 'c',
						'template' => 'secret-' . $uuid
					]
				]
			],
			'users' => [
				[
					'email' => 'editor@getkirby.com',
					'role'  => 'editor-' . $uuid
				]
			],
			'user' => 'editor@getkirby.com'
		]);

		$field = $this->field('checkboxes', [
			'model'   => $app->site(),
			'options' => [
				'type'  => 'query',
				'query' => 'site.children'
			],
			'value' => 'a, b'
		]);

		// the stored page that must not be listed is kept
		// as placeholder, the other one is hidden
		$this->assertSame(['a', 'b'], $field->value());
		$this->assertSame(['a', 'b'], array_column($field->options(), 'value'));
		$this->assertSame('[unknown]', $field->options()[1]['text']);
		$this->assertSame('a, b', $field->data());
	}

	public function testMin()
	{
		$field = $this->field('checkboxes', [
			'value'   => 'a',
			'options' => ['a', 'b', 'c'],
			'min'     => 2
		]);

		$this->assertTrue($field->required());
		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('min', $field->errors());
	}

	public function testMax()
	{
		$field = $this->field('checkboxes', [
			'value'   => 'a, b',
			'options' => ['a', 'b', 'c'],
			'max'     => 1
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('max', $field->errors());
	}

	public function testRequiredProps()
	{
		$field = $this->field('checkboxes', [
			'options'  => ['a', 'b', 'c'],
			'required' => true
		]);

		$this->assertTrue($field->required());
		$this->assertSame(1, $field->min());
	}

	public function testRequiredInvalid()
	{
		$field = $this->field('checkboxes', [
			'options'  => ['a', 'b', 'c'],
			'value'    => null,
			'required' => true
		]);

		$this->assertFalse($field->isValid());
	}

	public function testRequiredValid()
	{
		$field = $this->field('checkboxes', [
			'options'  => ['a', 'b', 'c'],
			'required' => true,
			'value'    => 'a'
		]);

		$this->assertTrue($field->isValid());
	}
}
