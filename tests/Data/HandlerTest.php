<?php

namespace Kirby\Data;

use Kirby\Filesystem\F;
use Kirby\TestCase;

require_once __DIR__ . '/mocks.php';

/**
 * @coversDefaultClass \Kirby\Data\Handler
 */
class HandlerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Data.Handler';

	/**
	 * @covers ::read
	 * @covers ::write
	 */
	public function testReadWrite()
	{
		$data = [
			'name'  => 'Homer Simpson',
			'email' => 'homer@simpson.com'
		];

		$file = static::TMP . '/data.json';

		CustomHandler::write($file, $data);
		$this->assertFileExists($file);
		$this->assertSame(CustomHandler::encode($data), F::read($file));

		$result = CustomHandler::read($file);
		$this->assertSame($data, $result);
	}

	/**
	 * @covers ::read
	 */
	public function testReadFileMissing()
	{
		$file = static::TMP . '/does-not-exist.json';

		$this->expectException('Exception');
		$this->expectExceptionMessage('The file "' . $file . '" does not exist or cannot be read');

		CustomHandler::read($file);
	}
}
