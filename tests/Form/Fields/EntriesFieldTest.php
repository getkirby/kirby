<?php

namespace Kirby\Form\Fields;

use Kirby\Cms\App;
use Kirby\Cms\Fieldsets;
use Kirby\Cms\Page;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Form\Fields;

/**
 * @coversDefaultClass \Kirby\Form\Field\EntriesField
 */
class EntriesFieldTest extends TestCase
{
	public function testDefaultProps()
	{
		$field = $this->field('entries', []);

		$this->assertSame('entries', $field->type());
		$this->assertSame('entries', $field->name());
		$this->assertNull($field->max());
		$this->assertTrue($field->sortable());
		$this->assertNull($field->value());
	}

	public function testField()
	{
		$field = $this->field('entries');
		$this->assertSame(['type' => 'text'], $field->field());
	}

	public function testFieldString()
	{
		$field = $this->field('entries', [
			'field' => 'url'
		]);

		$this->assertSame(['type' => 'url'], $field->field());
	}

	public function testFieldArrayOne()
	{
		$field = $this->field('entries', [
			'field' => $props = [
				'type'   => 'color',
				'format' => 'hsl'
			]
		]);

		$this->assertSame($props, $field->field());
	}

	public function testFieldArrayTwo()
	{
		$field = $this->field('entries', [
			'field' => $props = [
				'type'      => 'text',
				'minlength' => '100',
				'maxlength' => '1000'
			]
		]);

		$this->assertSame($props, $field->field());
	}

	public function testDefaultValue()
	{
		$field = $this->field('entries', [
			'default' => $defaults = [
				'Some text 1',
				'Some text 2',
				'Some text 3',
			]
		]);

		$this->assertSame($defaults, $field->default());
	}

	public function testSortable()
	{
		$field = $this->field('entries', [
			'sortable' => false
		]);

		$this->assertFalse($field->sortable());
	}

	public function testMax()
	{
		$field = $this->field('entries', [
			'max'   => 1,
			'value' => [
				'https://getkirby.com',
				'https://forum.getkirby.com',
				'https://plugins.getkirby.com',
			],
		]);

		$this->assertSame(1, $field->max());
		$this->assertFalse($field->isValid());
		$this->assertSame($field->errors()['max'], 'Please enter a value equal to or lower than 1');
	}

	public function testMin()
	{
		$field = $this->field('entries', [
			'min'   => 3,
			'value' => [
				'https://getkirby.com'
			],
		]);

		$this->assertSame(3, $field->min());
		$this->assertFalse($field->isValid());
		$this->assertSame($field->errors()['min'], 'Please enter a value equal to or greater than 3');
	}

	public function testRequiredValid()
	{
		$field = $this->field('entries', [
			'value'    => [
				'https://getkirby.com'
			],
			'required' => true
		]);

		$this->assertTrue($field->isValid());
	}

	public function testRequiredInvalid()
	{
		$field = $this->field('entries', [
			'required' => true
		]);

		$this->assertFalse($field->isValid());
	}

	public static function supportsProvider(): array
	{
		return [
			['color', true],
			['date', true],
			['email', true],
			['number', true],
			['select', true],
			['slug', true],
			['tel', true],
			['text', true],
			['time', true],
			['url', true],
			['blocks', false],
			['layout', false],
			['writer', false],
			['structure', false],
			['checkboxes', false],
			['files', false],
			['gap', false],
			['headline', false],
			['hidden', false],
			['info', false],
			['line', false],
			['link', false],
			['list', false],
			['multiselect', false],
			['object', false],
			['pages', false],
			['radio', false],
			['range', false],
			['tags', false],
			['textarea', false],
			['toggle', false],
			['toggles', false],
			['users', false],
		];
	}

	/**
	 * @dataProvider supportsProvider
	 */
	public function testSupportedFields($type, $expected)
	{
		if ($expected === false) {
			$this->expectException(InvalidArgumentException::class);
			$this->expectExceptionMessage('"' . $type . '" field type is not supported for the entries field');
		}

		$field = $this->field('entries', [
			'field' => $type,
		]);

		if ($expected === true) {
			$this->assertTrue($field->isValid());
		}
	}

	public static function validationsProvider(): array
	{
		return [
			['color', '#000000', '#000000'],
			['color', 'invalid', false],

			['date', '2025-02-01', true],
			['date', 'invalid', false],

			['email', 'support@getkirby.com', true],
			['email', 'invalid@host', false],

			['number', 1, true],
			['number', 'invalid', false],

			['select', 'web', true],

			['slug', 'page-slug', true],

			['tel', '+49123456789', true],
			['tel', 'invalid@phone.number', false],

			['text', 'some text', true],

			['time', '20:00', true],
			['time', 'invalid', false],

			['url', 'https://getkirby.com', true],
			['url', 'invalid.host', false],
		];
	}

	/**
	 * @dataProvider validationsProvider
	 */
	public function testValidations($type, $value, $expected)
	{
		$field = $this->field('entries', [
			'value'    => [
				$value
			],
			'field'    => $type,
			'required' => true
		]);

		$field->validate();
		$this->assertSame($expected, $field->isValid());

		if ($expected === false) {
			$this->assertSame([
				'entries' => 'There\'s an error on the "Entries" field in row 1'
			], $field->errors());
		}
	}

	public function testEmpty()
	{
		$field = $this->field('entries', [
			'empty' => $value = 'Custom empty text'
		]);

		$this->assertSame($value, $field->empty());
	}
}
