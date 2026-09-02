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

	protected function setUp(): void
	{
		$this->setUpTmp();
	}

	protected function tearDown(): void
	{
		$this->tearDownTmp();
	}

	public function testReadWrite(): void
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

	public function testReadFileMissing(): void
	{
		$file = static::TMP . '/does-not-exist.json';

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('The file "' . $file . '" does not exist or cannot be read');

		CustomHandler::read($file);
	}

	public function testWriteCreatesParentDir(): void
	{
		$file = static::TMP . '/nested/dir/data.json';

		$this->assertTrue(CustomHandler::write($file, ['a' => 'b']));
		$this->assertSame(['a' => 'b'], CustomHandler::read($file));
	}

	public function testWriteNotWritable(): void
	{
		$file = static::TMP . '/data.json';

		CustomHandler::write($file, ['a' => 'b']);
		chmod($file, 0444);

		try {
			$this->expectException(Exception::class);
			$this->expectExceptionMessage('The file "' . $file . '" is not writable');

			CustomHandler::write($file, ['a' => 'c']);
		} finally {
			chmod($file, 0777);
		}
	}

	public function testWriteWithFailingEncode(): void
	{
		$file = static::TMP . '/data.json';

		CustomHandler::write($file, ['a' => 'b']);
		$before = F::read($file);

		try {
			BrokenHandler::write($file, ['a' => 'c']);
			$this->fail('Expected exception was not thrown');
		} catch (Exception $e) {
			$this->assertSame('Encoding failed', $e->getMessage());
		}

		// encoding happens before the file is opened, so a failure
		// must not leave the previous content damaged
		$this->assertSame($before, F::read($file));
	}

	public function testWriteWithFailingEncodeNewFile(): void
	{
		$file = static::TMP . '/never-written.json';

		try {
			BrokenHandler::write($file, ['a' => 'b']);
			$this->fail('Expected exception was not thrown');
		} catch (Exception) {
			// the exception itself is covered above
		}

		// and it must not leave an empty file behind either
		$this->assertFileDoesNotExist($file);
	}
}
