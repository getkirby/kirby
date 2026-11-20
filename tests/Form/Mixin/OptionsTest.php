<?php

namespace Kirby\Form\Mixin;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Form\Field\InputField;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversTrait;

class OptionsField extends InputField
{
	use Options;

	protected mixed $value = null;

	public function __construct(
		array|string|null $options = null,
		mixed ...$args
	) {
		parent::__construct(...$args);

		$this->options = $options;
	}
}

#[CoversTrait(Options::class)]
class OptionsTest extends TestCase
{
	protected function field(array|string|null $options = null): OptionsField
	{
		return new OptionsField(
			options: $options,
			model: new Page(['slug' => 'test'])
		);
	}

	protected function setUp(): void
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);
	}

	public function testHasOptionValue(): void
	{
		$field = $this->field(options: [
			['value' => 'a'],
			['value' => 'b']
		]);

		$this->assertTrue($field->hasOptionValue('a'));
		$this->assertTrue($field->hasOptionValue('b'));
		$this->assertFalse($field->hasOptionValue('c'));
		$this->assertFalse($field->hasOptionValue('A'));
		$this->assertFalse($field->hasOptionValue(''));
		$this->assertFalse($field->hasOptionValue(null));
	}

	public function testHasOptionValueWithNumericOptions(): void
	{
		$field = $this->field(options: [
			['value' => 36],
			['value' => 38.5]
		]);

		$this->assertTrue($field->hasOptionValue(36));
		$this->assertTrue($field->hasOptionValue('36'));
		$this->assertTrue($field->hasOptionValue(38.5));
		$this->assertTrue($field->hasOptionValue('38.5'));
		$this->assertFalse($field->hasOptionValue(40));
		$this->assertFalse($field->hasOptionValue('40'));
		$this->assertFalse($field->hasOptionValue('36.0'));
	}

	public function testHasOptionValueWithNumericStringOptions(): void
	{
		$field = $this->field(options: [
			['value' => '1'],
			['value' => '2']
		]);

		$this->assertTrue($field->hasOptionValue(1));
		$this->assertTrue($field->hasOptionValue('1'));
		$this->assertFalse($field->hasOptionValue(3));
	}

	public function testHasOptionValueWithoutOptions(): void
	{
		$field = $this->field();

		$this->assertFalse($field->hasOptionValue('a'));
		$this->assertFalse($field->hasOptionValue(''));
		$this->assertFalse($field->hasOptionValue(null));
	}

	public function testHasOptionValueWithSimpleOptions(): void
	{
		$field = $this->field(options: [1, 2, 3]);

		$this->assertTrue($field->hasOptionValue(2));
		$this->assertTrue($field->hasOptionValue('2'));
		$this->assertFalse($field->hasOptionValue(4));
		$this->assertFalse($field->hasOptionValue('4'));
	}

	public function testOptionValues(): void
	{
		$field = $this->field(options: [
			['value' => 'a', 'text' => 'A'],
			['value' => 'b', 'text' => 'B']
		]);

		$this->assertSame(['a', 'b'], $field->optionValues());
	}

	public function testOptionValuesWithAssociativeOptions(): void
	{
		$field = $this->field(options: [
			'a' => 'Option A',
			'b' => 'Option B'
		]);

		$this->assertSame(['a', 'b'], $field->optionValues());
	}

	public function testOptionValuesWithNumericOptions(): void
	{
		$field = $this->field(options: [
			['value' => 36],
			['value' => 38.5]
		]);

		$this->assertSame([36, 38.5], $field->optionValues());
	}

	public function testOptionValuesWithoutOptions(): void
	{
		$field = $this->field();

		$this->assertSame([], $field->optionValues());
	}
}
