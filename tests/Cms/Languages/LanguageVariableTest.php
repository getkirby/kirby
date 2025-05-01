<?php

namespace Kirby\Cms;

use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageVariable::class)]
class LanguageVariableTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.LanguageVariable';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			],
			'languages' => [
				[
					'code'    => 'en',
					'default' => true,
				]
			]
		]);

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testCreate(): void
	{
		// impersonate kirby to make sure the translation
		// is created in the default language
		$this->app->impersonate('kirby');

		$variable = LanguageVariable::create('foo', 'bar');
		$this->assertSame('bar', $variable->value());
	}

	public function testCreateWithMultipleValues(): void
	{
		// impersonate kirby to make sure the translation
		// is created in the default language
		$this->app->impersonate('kirby');

		$variable = LanguageVariable::create('foo', ['bar', 'baz']);
		$this->assertSame(['bar', 'baz'], $variable->value());
	}

	public function testCreateEmptyKey(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The variable needs a valid key');

		LanguageVariable::create('');
	}

	public function testCreateInvalidKey(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The variable key must not be numeric');

		LanguageVariable::create('0');
	}

	public function testCreateWithDuplicateKey(): void
	{
		$this->app->impersonate('kirby');

		$this->expectException(DuplicateException::class);
		$this->expectExceptionMessage('The variable already exists');

		LanguageVariable::create('foo', 'bar');
		LanguageVariable::create('foo', 'baz');
	}

	public function testCreateWithCoreKey(): void
	{
		$this->app->impersonate('kirby');

		$this->expectException(DuplicateException::class);
		$this->expectExceptionMessage('The variable is part of the core translation and cannot be overwritten');

		LanguageVariable::create('date', 'bar');
	}

	public function testDelete(): void
	{
		$this->app->impersonate('kirby');

		$variable = LanguageVariable::create('foo', 'bar');
		$this->assertTrue($variable->exists());
		$variable->delete();
		$this->assertFalse($variable->exists());
	}

	public function testExists(): void
	{
		$this->app->impersonate('kirby');

		$variable = new LanguageVariable($this->app->defaultLanguage(), 'foo');
		$this->assertFalse($variable->exists());

		$variable = LanguageVariable::create('foo', 'bar');
		$this->assertTrue($variable->exists());
	}

	public function testHasMultipleValues(): void
	{
		// impersonate kirby to make sure the translation
		// is created in the default language
		$this->app->impersonate('kirby');

		$variable = LanguageVariable::create('foo', 'bar');
		$this->assertFalse($variable->hasMultipleValues());

		$variable = LanguageVariable::create('foz', ['bar', 'baz']);
		$this->assertTrue($variable->hasMultipleValues());
	}

	public function testKey(): void
	{
		$language = new Language(['code' => 'test']);
		$variable = new LanguageVariable($language, 'foo');
		$this->assertSame('foo', $variable->key());
	}

	public function testUpdate(): void
	{
		// impersonate kirby to allow updating the variable
		$this->app->impersonate('kirby');

		$variable = LanguageVariable::create('foo', 'bar');
		$this->assertSame('bar', $variable->value());

		$variable = $variable->update('baz');
		$this->assertSame('baz', $variable->value());
	}
}
