<?php

namespace Kirby\Data;

use Exception;
use Kirby\Filesystem\F;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Handler::class)]
class HandlerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Data.Handler';

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

	public function testReadFileMissing()
	{
		$file = static::TMP . '/does-not-exist.json';

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('The file "' . $file . '" does not exist or cannot be read');

		CustomHandler::read($file);
	}
}
