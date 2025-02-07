<?php

namespace Kirby\Form\Field;

use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;

/**
 * @coversDefaultClass \Kirby\Form\Field\EntriesField
 */
class EntriesFieldTest extends TestCase
{
	public function testDefaultProps()
	{
		$field = $this->field('entries');

		$this->assertSame('entries', $field->type());
		$this->assertSame('entries', $field->name());
		$this->assertNull($field->max());
		$this->assertTrue($field->sortable());
		$this->assertSame([], $field->value());
	}

	public function testProps()
	{
		$field = $this->field('entries');
		$props = $field->props();

		$this->assertIsArray($props);
		$this->assertNull($props['empty']);
		$this->assertNull($props['max']);
		$this->assertNull($props['min']);
		$this->assertNull($props['after']);
		$this->assertFalse($props['autofocus']);
		$this->assertNull($props['before']);
		$this->assertNull($props['default']);
		$this->assertFalse($props['disabled']);
		$this->assertNull($props['help']);
		$this->assertNull($props['icon']);
		$this->assertSame('Entries', $props['label']);
		$this->assertSame('entries', $props['name']);
		$this->assertNull($props['placeholder']);
		$this->assertFalse($props['required']);
		$this->assertTrue($props['saveable']);
		$this->assertTrue($props['sortable']);
		$this->assertTrue($props['translate']);
		$this->assertSame('entries', $props['type']);
		$this->assertSame('1/1', $props['width']);

		$fieldProps = $props['field'];
		$this->assertIsArray($fieldProps);
		$this->assertFalse($fieldProps['autofocus']);
		$this->assertTrue($fieldProps['counter']);
		$this->assertFalse($fieldProps['disabled']);
		$this->assertSame('sans-serif', $fieldProps['font']);
		$this->assertFalse($fieldProps['hidden']);
		$this->assertSame('0', $fieldProps['name']);
		$this->assertFalse($fieldProps['required']);
		$this->assertTrue($fieldProps['saveable']);
		$this->assertFalse($fieldProps['spellcheck']);
		$this->assertTrue($fieldProps['translate']);
		$this->assertSame('text', $fieldProps['type']);
		$this->assertSame('1/1', $fieldProps['width']);
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
		$this->assertSame($field->errors()['entries'], 'You must not add more than one entry');
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
		$this->assertSame($field->errors()['entries'], 'You must add at least 3 entries');
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
			['color', '#000000', true],
			['color', 'invalid', false],

			['date', '2025-02-01', true],

			['email', 'support@getkirby.com', true],
			['email', 'invalid@host', false],

			['number', 1, true],

			['select', 'web', true],

			['slug', 'page-slug', true],

			['tel', '+49123456789', true],

			['text', 'some text', true],

			['time', '20:00', true],

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

	public function testToStoredValue()
	{
		$value = [
			'Text',
			'Some text',
			'Another text',
		];

		$field = $this->field('entries', [
			'value' => $value
		]);

		$this->assertSame(
			Data::encode($value, 'yaml'),
			$field->toStoredValue()
		);

		// empty tests
		$field->fill(null);
		$this->assertSame('', $field->toStoredValue());

		$field->fill('');
		$this->assertSame('', $field->toStoredValue());

		$field->fill([]);
		$this->assertSame('', $field->toStoredValue());
	}
}
