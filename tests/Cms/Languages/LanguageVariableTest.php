<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Cms\LanguageVariable
 */
class LanguageVariableTest extends TestCase
{
	protected $app;
	protected $tmp;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => $this->tmp = __DIR__ . '/tmp/LanguageVariableTest',
			]
		]);

		Dir::make($this->tmp);
	}

	public function tearDown(): void
	{
		Dir::remove($this->tmp);
	}

	/**
	 * @covers ::create
	 */
	public function testCreateEmptyKey()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The variable needs a valid key');

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
