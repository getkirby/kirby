<?php

namespace Kirby\Cms;

use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Cms\LanguageVariable
 */
class LanguageVariableTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.LanguageVariable';

	protected $app;

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

	/**
	 * @covers ::create
	 */
	public function testCreateCoreKey()
	{
		$this->app->impersonate('kirby');

		$this->expectException(DuplicateException::class);
		$this->expectExceptionMessage('"date" is one of Kirby\'s own translation strings and cannot be overwritten by a language variable');

		LanguageVariable::create('date', 'bar');
	}

	/**
	 * @covers ::create
	 */
	public function testCreateDuplicateKey()
	{
		$this->app->impersonate('kirby');

		$this->expectException(DuplicateException::class);
		$this->expectExceptionMessage('A variable for "foo" already exists');

		LanguageVariable::create('foo', 'bar');
		LanguageVariable::create('foo', 'baz');
	}

	/**
	 * @covers ::create
	 */
	public function testCreateEmptyKey()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid key for the variable');

		LanguageVariable::create('');
	}

	/**
	 * @covers ::create
	 */
	public function testCreateInvalidKey()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The variable key must not be numeric');

		LanguageVariable::create('0');
	}

	/**
	 * @covers ::__construct
	 * @covers ::key
	 */
	public function testKey()
	{
		$language = new Language(['code' => 'test']);
		$variable = new LanguageVariable($language, 'foo');
		$this->assertSame('foo', $variable->key());
	}
}
