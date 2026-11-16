<?php

namespace Kirby\Form\Fields;

use Kirby\Cms\App;

class MultiselectFieldTest extends TestCase
{
	public function testDefaultProps()
	{
		$field = $this->field('multiselect');

		$this->assertSame('multiselect', $field->type());
		$this->assertSame('multiselect', $field->name());
		$this->assertSame([], $field->value());
		$this->assertSame([], $field->default());
		$this->assertSame([], $field->options());
		$this->assertNull($field->min());
		$this->assertNull($field->max());
		$this->assertSame(',', $field->separator());
		$this->assertSame('checklist', $field->icon());
		$this->assertNull($field->counter());
		$this->assertTrue($field->search());
		$this->assertFalse($field->sort());
		$this->assertTrue($field->save());
	}

	public function testMin()
	{
		$field = $this->field('multiselect', [
			'value'   => 'a',
			'options' => ['a', 'b', 'c'],
			'min'     => 2
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('min', $field->errors());
	}

	public function testMax()
	{
		$field = $this->field('multiselect', [
			'value'   => 'a, b',
			'options' => ['a', 'b', 'c'],
			'max'     => 1
		]);

		$this->assertFalse($field->isValid());
		$this->assertArrayHasKey('max', $field->errors());
	}

	public function testSanitizeOptions()
	{
		$field = $this->field('multiselect', [
			'value'   => 'a, b',
			'options' => ['b', 'c'],
		]);

		$this->assertCount(1, $field->value());
		$this->assertArrayHasKey(0, $field->value());
	}

	public function testSanitizeProtectedOptions(): void
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

		$field = $this->field('multiselect', [
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
}
