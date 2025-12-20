<?php

namespace Kirby\Form;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Validations::class)]
class ValidationsTest extends TestCase
{
	public function setUp(): void
	{
		new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		Field::$types = [
			'test' => [
				'props' => [
					'options' => fn (array $options = []) => $options
				]
			]
		];
	}

	public function tearDown(): void
	{
		Field::$types = [];
	}

	public function testBooleanValid(): void
	{
		$page  = new Page(['slug' => 'test']);
		$field = new Field('test', ['model' => $page]);
		$this->assertTrue(Validations::boolean($field, true));
	}

	public function testBooleanInvalid(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please confirm or deny');

		$page  = new Page(['slug' => 'test']);
		$field = new Field('test', ['model' => $page]);
		Validations::boolean($field, 'nope');
	}

	public function testDateValid(): void
	{
		$page  = new Page(['slug' => 'test']);
		$field = new Field('test', ['model' => $page]);
		$this->assertTrue(Validations::date($field, '2012-12-12'));
	}

	public function testDateInvalid(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid date');

		$page  = new Page(['slug' => 'test']);
		$field = new Field('test', ['model' => $page]);
		Validations::date($field, 'somewhen');
	}

	public function testEmailValid(): void
	{
		$page  = new Page(['slug' => 'test']);
		$field = new Field('test', ['model' => $page]);
		$this->assertTrue(Validations::email($field, 'test@getkirby.com'));
	}

	public function testEmailInvalid(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid email address');

		$page  = new Page(['slug' => 'test']);
		$field = new Field('test', ['model' => $page]);
		Validations::email($field, 'test[at]getkirby.com');
	}

	public function testMaxValid(): void
	{
		$page  = new Page(['slug' => 'test']);
		$field = new Field('test', [
			'model' => $page,
			'max' => 5
		]);

		$this->assertTrue(Validations::max($field, 4));
	}

	public function testMaxInvalid(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a value equal to or lower than 5');

		$page  = new Page(['slug' => 'test']);
		$field = new Field('test', [
			'max' => 5,
			'model' => $page
		]);

		Validations::max($field, 6);
	}

	public function testMaxLengthValid(): void
	{
		$page  = new Page(['slug' => 'test']);
		$field = new Field('test', [
			'maxlength' => 5,
			'model' => $page
		]);

		$this->assertTrue(Validations::maxlength($field, 'test'));
	}

	public function testMaxLengthInvalid(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a shorter value. (max. 5 characters)');

		$page  = new Page(['slug' => 'test']);
		$field = new Field('test', [
			'maxlength' => 5,
			'model' => $page
		]);

		Validations::maxlength($field, 'testest');
	}

	public function testMinValid(): void
	{
		$page  = new Page(['slug' => 'test']);
		$field = new Field('test', [
			'min' => 5,
			'model' => $page
		]);

		$this->assertTrue(Validations::min($field, 6));
	}

	public function testMinInvalid(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a value equal to or greater than 5');

		$page  = new Page(['slug' => 'test']);
		$field = new Field('test', [
			'min' => 5,
			'model' => $page
		]);

		Validations::min($field, 4);
	}

	public function testMinLengthValid(): void
	{
		$page  = new Page(['slug' => 'test']);
		$field = new Field('test', [
			'minlength' => 5,
			'model' => $page
		]);

		$this->assertTrue(Validations::minlength($field, 'testest'));
	}

	public function testMinLengthInvalid(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a longer value. (min. 5 characters)');

		$page  = new Page(['slug' => 'test']);
		$field = new Field('test', [
			'minlength' => 5,
			'model' => $page
		]);

		Validations::minlength($field, 'test');
	}

	public function testPatternValid(): void
	{
		$page  = new Page(['slug' => 'test']);
		$field = new Field('test', [
			'pattern' => '^#?([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$',
			'model' => $page
		]);

		$this->assertTrue(Validations::pattern($field, '#fff'));
		$this->assertTrue(Validations::pattern($field, '#222'));
		$this->assertTrue(Validations::pattern($field, '#afafaf'));
		$this->assertTrue(Validations::pattern($field, '#34b3cd'));
	}

	public function testPatternInvalid(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The value does not match the expected pattern');

		$page  = new Page(['slug' => 'test']);
		$field = new Field('test', [
			'pattern' => '^#?([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$',
			'model' => $page
		]);

		Validations::pattern($field, '#MMM');
	}

	public function testPatternInvalidMatchButMore(): void
	{
		$page  = new Page(['slug' => 'test']);
		$field = new Field('test', [
			'pattern' => '\d{3,4}',
			'model'   => $page
		]);

		$this->assertTrue(Validations::pattern($field, '123'));
		$this->assertTrue(Validations::pattern($field, '1234'));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The value does not match the expected pattern');

		Validations::pattern($field, '12345');
	}

	public function testRequiredValid(): void
	{
		$page  = new Page(['slug' => 'test']);
		$field = new Field('test', [
			'required' => true,
			'model' => $page
		]);

		$this->assertTrue(Validations::required($field, 'something'));
	}

	public function testRequiredInvalid(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter something');

		$page  = new Page(['slug' => 'test']);
		$field = new Field('test', [
			'required' => true,
			'model' => $page
		]);

		Validations::required($field, '');
	}

	public function testOptionValid(): void
	{
		$page  = new Page(['slug' => 'test']);
		$field = new Field('test', [
			'options' => [
				['value' => 'a'],
				['value' => 'b']
			],
			'model' => $page
		]);

		$this->assertTrue(Validations::option($field, 'a'));
	}

	public function testOptionInvalid(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please select a valid option');

		$page  = new Page(['slug' => 'test']);
		$field = new Field('test', [
			'options' => [
				['value' => 'a'],
				['value' => 'b']
			],
			'model' => $page
		]);

		Validations::option($field, 'c');
	}

	public function testOptionsValid(): void
	{
		$page  = new Page(['slug' => 'test']);
		$field = new Field('test', [
			'options' => [
				['value' => 'a'],
				['value' => 'b']
			],
			'model' => $page
		]);

		$this->assertTrue(Validations::options($field, ['a', 'b']));
	}

	public function testOptionsInvalid(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please select a valid option');

		$page  = new Page(['slug' => 'test']);
		$field = new Field('test', [
			'options' => [
				['value' => 'a'],
				['value' => 'b']
			],
			'model' => $page
		]);

		Validations::options($field, ['a', 'c']);
	}

	public function testTimeValid(): void
	{
		$page  = new Page(['slug' => 'test']);
		$field = new Field('test', ['model' => $page]);
		$this->assertTrue(Validations::time($field, '10:12'));
	}

	public function testTimeInvalid(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid time');

		$page  = new Page(['slug' => 'test']);
		$field = new Field('test', ['model' => $page]);
		Validations::time($field, '99:99');
	}
}
