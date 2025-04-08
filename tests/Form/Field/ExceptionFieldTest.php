<?php

namespace Kirby\Form\Field;

use Exception;
use Kirby\Cms\App;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ExceptionField::class)]
class ExceptionFieldTest extends TestCase
{
	public function setUp(): void
	{
		$this->app = new App();
	}

	public function testHasValue(): void
	{
		$field = new ExceptionField('test', new Exception());
		$this->assertFalse($field->hasValue());
	}

	public function testLabel(): void
	{
		$field = new ExceptionField('test', new Exception());
		$this->assertSame('Error in "test" field.', $field->label());
	}

	public function testProps(): void
	{
		$field = new ExceptionField('test', new Exception('Something went wrong'));
		$props = $field->props();

		$this->assertSame([
			'label' => 'Error in "test" field.',
			'name'  => 'test',
			'text'  => 'Something went wrong',
			'theme' => 'negative',
			'type'  => 'info'
		], $props);
	}

	public function testTextWithoutDebug(): void
	{
		$field = new ExceptionField('test', new Exception('Something went wrong'));
		$this->assertSame('Something went wrong', $field->text());
	}

	public function testTextWithDebug(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'debug' => true
			]
		]);

		$exception = new Exception('Something went wrong');
		$field = new ExceptionField('test', $exception);

		$expected = 'Something went wrong in file: ' . $exception->getFile() . ' line: ' . $exception->getLine();
		$this->assertSame($expected, $field->text());
	}

	public function testTextStripsHtml(): void
	{
		$field = new ExceptionField('test', new Exception('<p>Something went wrong</p>'));
		$this->assertSame('Something went wrong', $field->text());
	}

	public function testTheme(): void
	{
		$field = new ExceptionField('test', new Exception());
		$this->assertSame('negative', $field->theme());
	}

	public function testType(): void
	{
		$field = new ExceptionField('test', new Exception());
		$this->assertSame('info', $field->type());
	}
}
