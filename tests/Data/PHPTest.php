<?php

namespace Kirby\Data;

use Exception;
use Kirby\Exception\BadMethodCallException;
use Kirby\Filesystem\F;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Data\PHP
 */
class PHPTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/php';
	public const TMP      = KIRBY_TMP_DIR . '/Data.PHP';

	/**
	 * @covers ::encode
	 * @covers ::encodeArray
	 */
	public function testEncode()
	{
		$input    = static::FIXTURES . '/input.php';
		$expected = static::FIXTURES . '/expected.php';
		$result   = PHP::encode(include $input);

		$this->assertSame(trim(file_get_contents($expected)), $result);

		// scalar values
		$this->assertSame("'test'", PHP::encode('test'));
		$this->assertSame('123', PHP::encode(123));
	}

	/**
	 * @covers ::decode
	 */
	public function testDecode()
	{
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('The PHP::decode() method is not implemented');

		$input  = include static::FIXTURES . '/input.php';
		$result = PHP::decode($input);
	}

	/**
	 * @covers ::read
	 */
	public function testRead()
	{
		$input  = static::FIXTURES . '/input.php';
		$result = PHP::read($input);

		$this->assertSame($result, include $input);
	}

	/**
	 * @covers ::read
	 */
	public function testReadFileMissing()
	{
		$file = static::TMP . '/does-not-exist.php';

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('The file "' . $file . '" does not exist');

		PHP::read($file);
	}

	/**
	 * @covers ::write
	 */
	public function testWrite()
	{
		$input = include static::FIXTURES . '/input.php';
		$file  = static::TMP . '/tmp.php';

		$this->assertTrue(PHP::write($file, $input));

		$this->assertSame($input, include $file);
		$this->assertSame($input, PHP::read($file));

		F::remove($file);
	}
}
