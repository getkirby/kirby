<?php

namespace Kirby\Form\Field;

use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(FilterField::class)]
class FilterFieldTest extends TestCase
{
	protected function filterField(array $attr = []): FilterField
	{
		return $this->field('filter', [
			'fields' => [
				'title' => [
					'type'  => 'text',
					'label' => 'Title'
				],
				'status' => [
					'type'    => 'select',
					'options' => ['draft', 'listed']
				],
				'date' => [
					'type' => 'date'
				]
			],
			...$attr
		]);
	}

	public function testDefaultProps(): void
	{
		$field = $this->field('filter');

		$this->assertSame('filter', $field->type());
		$this->assertSame('filter', $field->name());
		$this->assertSame([], $field->fields());
		$this->assertSame([], $field->value());
		$this->assertNull($field->empty());
		$this->assertNull($field->max());
		$this->assertNull($field->min());
		$this->assertCount(12, $field->operators());
		$this->assertTrue($field->isValid());
	}

	public function testEmpty(): void
	{
		$field = $this->field('filter', [
			'empty' => $value = 'Custom empty text'
		]);

		$this->assertSame($value, $field->empty());
	}

	public function testFields(): void
	{
		$field  = $this->filterField();
		$fields = $field->fields();

		$this->assertSame(['title', 'status', 'date'], array_keys($fields));
		$this->assertSame('text', $fields['title']['type']);
		$this->assertSame('Title', $fields['title']['label']);
		$this->assertSame('select', $fields['status']['type']);
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
			['structure', false],
			['textarea', false],
			['toggle', false],
			['writer', false],
		];
	}

	#[DataProvider('supportsProvider')]
	public function testFieldsSupported(string $type, bool $expected): void
	{
		$field = $this->field('filter', [
			'fields' => [
				'test' => ['type' => $type]
			]
		]);

		if ($expected === false) {
			$this->expectException(InvalidArgumentException::class);
			$this->expectExceptionMessage('"' . $type . '" field type is not supported for the filter field');
		}

		$this->assertSame($type, $field->fields()['test']['type']);
	}

	public function testFillNormalizesConditions(): void
	{
		$field = $this->filterField();
		$field->fill([
			['field' => 'title'],
			'invalid',
			['field' => 'status', 'operator' => '!=', 'value' => 'draft'],
		]);

		$this->assertSame([
			['field' => 'title', 'operator' => '==', 'value' => ''],
			['field' => 'status', 'operator' => '!=', 'value' => 'draft'],
		], $field->toFormValue());
	}

	public function testFillWithYaml(): void
	{
		$field = $this->filterField();
		$field->fill("-\n  field: title\n  operator: '*='\n  value: Kirby\n");

		$this->assertSame([
			['field' => 'title', 'operator' => '*=', 'value' => 'Kirby'],
		], $field->toFormValue());
	}

	public function testFillWithDefaultOperatorByType(): void
	{
		$field = $this->filterField([
			'operators' => ['*=', '>', '==']
		]);

		$field->fill([
			['field' => 'title'],
			['field' => 'date'],
			['field' => 'status'],
			['field' => 'unknown'],
		]);

		$this->assertSame(
			['*=', '>', '==', '*='],
			array_column($field->toFormValue(), 'operator')
		);
	}

	public function testOperatorOptions(): void
	{
		$field = $this->filterField([
			'operators' => ['==', '!=', '>=', '*=']
		]);

		$this->assertSame([
			'title' => [
				['value' => '==', 'text' => 'is'],
				['value' => '!=', 'text' => 'is not'],
				['value' => '*=', 'text' => 'contains'],
			],
			'status' => [
				['value' => '==', 'text' => 'is'],
				['value' => '!=', 'text' => 'is not'],
			],
			'date' => [
				['value' => '==', 'text' => 'is'],
				['value' => '!=', 'text' => 'is not'],
				['value' => '>=', 'text' => 'is greater than or equal to'],
			],
		], $field->operatorOptions());

		$this->assertSame($field->operatorOptions(), $field->toArray()['operators']);
	}

	public function testOperatorOptionsWithoutFields(): void
	{
		$field = $this->field('filter');
		$this->assertSame([], $field->operatorOptions());
	}

	public function testOperators(): void
	{
		$field = $this->field('filter');
		$this->assertSame(array_keys($field->supportedOperators()), $field->operators());

		$field = $this->field('filter', [
			'operators' => ['==', '!=']
		]);

		$this->assertSame(['==', '!='], $field->operators());
	}

	public function testOperatorsFor(): void
	{
		$field = $this->field('filter');

		$this->assertSame(['==', '!='], $field->operatorsFor('select'));
		$this->assertSame(['==', '!=', '>', '>=', '<', '<='], $field->operatorsFor('number'));
		$this->assertSame(['==', '!=', '*=', '!*=', '^=', '!^=', '$=', '!$='], $field->operatorsFor('text'));
		$this->assertSame([], $field->operatorsFor('blocks'));

		$field = $this->field('filter', [
			'operators' => ['>', '*=', '==']
		]);

		$this->assertSame(['>', '=='], $field->operatorsFor('date'));
		$this->assertSame(['*=', '=='], $field->operatorsFor('text'));
		$this->assertSame(['=='], $field->operatorsFor('color'));
	}

	public function testOperatorsUnsupported(): void
	{
		$field = $this->field('filter', [
			'operators' => ['==', 'foo']
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('"foo" is not a supported operator for the filter field');

		$field->operators();
	}

	public function testToStoredValue(): void
	{
		$field = $this->filterField();
		$field->submit([
			['field' => 'date', 'operator' => '>', 'value' => '2026-11-03 12:34:00'],
			['field' => 'unknown', 'operator' => '==', 'value' => 'test'],
		]);

		$this->assertSame([
			['field' => 'date', 'operator' => '>', 'value' => '2026-11-03'],
			['field' => 'unknown', 'operator' => '==', 'value' => 'test'],
		], $field->toStoredValue());
	}

	public function testValidations(): void
	{
		$field = $this->filterField();
		$field->fill([
			['field' => 'status', 'operator' => '==', 'value' => 'listed'],
		]);

		$this->assertTrue($field->isValid());
	}

	public static function invalidConditionsProvider(): array
	{
		return [
			'unknown field'    => [['field' => 'unknown', 'operator' => '==', 'value' => 'test']],
			'unknown operator' => [['field' => 'title', 'operator' => 'foo', 'value' => 'test']],
			'type operator'    => [['field' => 'title', 'operator' => '>', 'value' => 'test']],
			'invalid value'    => [['field' => 'status', 'operator' => '==', 'value' => 'archived']],
		];
	}

	#[DataProvider('invalidConditionsProvider')]
	public function testValidationsInvalidCondition(array $condition): void
	{
		$field = $this->filterField([
			'label' => 'Filters'
		]);

		$field->fill([
			['field' => 'title', 'operator' => '==', 'value' => 'Kirby'],
			$condition
		]);

		$this->assertSame([
			'filter' => 'There\'s an error on the "Filters" field in condition 2'
		], $field->errors());
	}

	public function testValidationsMax(): void
	{
		$field = $this->filterField(['max' => 1]);
		$field->fill([
			['field' => 'title', 'operator' => '==', 'value' => 'a'],
			['field' => 'title', 'operator' => '==', 'value' => 'b'],
		]);

		$this->assertSame([
			'filter' => 'You must not add more than one condition'
		], $field->errors());
	}

	public function testValidationsMin(): void
	{
		$field = $this->filterField(['min' => 2]);
		$field->fill([
			['field' => 'title', 'operator' => '==', 'value' => 'a'],
		]);

		$this->assertTrue($field->isRequired());
		$this->assertSame([
			'filter' => 'You must add at least 2 conditions'
		], $field->errors());
	}
}
