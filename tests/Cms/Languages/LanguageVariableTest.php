<?php

namespace Kirby\Cms;

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
			]
		]);

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testCreateEmptyKey()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The variable needs a valid key');

		LanguageVariable::create('');
	}

	public function testCreateInvalidKey()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The variable key must not be numeric');

		LanguageVariable::create('0');
	}

	public function testKey()
	{
		$language = new Language(['code' => 'test']);
		$variable = new LanguageVariable($language, 'foo');
		$this->assertSame('foo', $variable->key());
	}
}
