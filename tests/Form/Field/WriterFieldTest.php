<?php

namespace Kirby\Form\Field;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(WriterField::class)]
class WriterFieldTest extends TestCase
{
	public function testProps(): void
	{
		$field = $this->field('writer');
		$props = $field->props();

		ksort($props);

		$expected = [
			'autofocus'    => false,
			'counter'      => true,
			'disabled'     => false,
			'headings'     => [1, 2, 3, 4, 5, 6],
			'help'         => null,
			'hidden'       => false,
			'icon'         => null,
			'inline'       => false,
			'label'        => 'Writer',
			'marks'        => null,
			'maxlength'    => null,
			'minlength'    => null,
			'name'         => 'writer',
			'nodes'        => null,
			'placeholder'  => null,
			'required'     => false,
			'saveable'     => true,
			'spellcheck'   => null,
			'toolbar'      => null,
			'translate'    => true,
			'type'         => 'writer',
			'when'         => null,
			'width'        => '1/1',
		];

		$this->assertSame($expected, $props);
	}

	public function testReset(): void
	{
		$field = $this->field('writer');
		$field->fill('test');
		$this->assertSame('test', $field->toFormValue());

		$field->reset();
		$this->assertSame('', $field->toFormValue());
	}

	public function testToFormValueTrimmed(): void
	{
		$field = $this->field('writer', [
			'value' => 'test '
		]);

		$this->assertSame('test', $field->toFormValue());
	}

	public function testToFormValueSanitized(): void
	{
		$field = $this->field('writer', [
			'value' => 'This is a <strong>test</strong><script>alert("Hacked")</script> with <em>formatting</em> and a <a href="/@/page/abcde">UUID link</a>'
		]);

		$this->assertSame('This is a <strong>test</strong> with <em>formatting</em> and a <a href="/@/page/abcde">UUID link</a>', $field->toFormValue());
	}

	public function testValidateMaxlength(): void
	{
		$field = $this->field('writer', [
			'maxlength' => 5
		]);

		$this->assertTrue($field->isValid());

		$field->fill('Test');

		$this->assertTrue($field->isValid());

		$field->fill('<p>Test</p>');

		$this->assertTrue($field->isValid());

		$field->fill('Test is too long');

		$this->assertFalse($field->isValid());

		$field->fill('<p>Test is too long</p>');

		$this->assertFalse($field->isValid());
	}

	public function testValidateMinlength(): void
	{
		$field = $this->field('writer', [
			'minlength' => 5
		]);

		$this->assertTrue($field->isValid());

		$field->fill('Test is long enough');

		$this->assertTrue($field->isValid());

		$field->fill('<p>Test is long enough</p>');

		$this->assertTrue($field->isValid());

		$field->fill('Test');

		$this->assertFalse($field->isValid());

		$field->fill('<p>Test</p>');

		$this->assertFalse($field->isValid());
	}
}
