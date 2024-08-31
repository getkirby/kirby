<?php

namespace Kirby\Sane;

use Exception;
use Kirby\Exception\InvalidArgumentException;

/**
 * @coversDefaultClass \Kirby\Sane\Handler
 */
class HandlerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Sane.Handler';

	protected static string $type = 'sane';

	/**
	 * @covers ::sanitizeFile
	 * @covers ::readFile
	 */
	public function testSanitizeFile()
	{
		$expected = $this->fixture('doctype-valid.svg');
		$tmp      = $this->fixture('doctype-valid.svg', true);

		CustomHandler::sanitizeFile($tmp);

		$this->assertFileEquals($expected, $tmp);

		$expected = $this->fixture('external-source-1.sanitized.svg');
		$tmp      = $this->fixture('external-source-1.svg', true);

		CustomHandler::sanitizeFile($tmp);

		$this->assertFileEquals($expected, $tmp);

		$expected = $this->fixture('xlink-subfolder.sanitized.svg');
		$tmp      = $this->fixture('xlink-subfolder.svg', true);

		CustomHandler::sanitizeFile($tmp);

		$this->assertFileEquals($expected, $tmp);
	}

	/**
	 * @covers ::sanitizeFile
	 * @covers ::readFile
	 */
	public function testSanitizeFileMissing()
	{
		$file = $this->fixture('does-not-exist.svg');

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('The file "' . $file . '" does not exist');

		CustomHandler::sanitizeFile($file);
	}

	/**
	 * @covers ::validateFile
	 * @covers ::readFile
	 */
	public function testValidateFile()
	{
		$this->assertNull(
			CustomHandler::validateFile($this->fixture('doctype-valid.svg'))
		);
	}

	/**
	 * @covers ::validateFile
	 * @covers ::readFile
	 */
	public function testValidateFileError()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL is not allowed in attribute "style"');

		CustomHandler::validateFile($this->fixture('external-source-1.svg'));
	}

	/**
	 * @covers ::validateFile
	 * @covers ::readFile
	 */
	public function testValidateFileErrorExternalFile()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL points outside of the site index URL');

		CustomHandler::validateFile($this->fixture('xlink-subfolder.svg'));
	}

	/**
	 * @covers ::validateFile
	 * @covers ::readFile
	 */
	public function testValidateFileMissing()
	{
		$file = $this->fixture('does-not-exist.svg');

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('The file "' . $file . '" does not exist');

		CustomHandler::validateFile($file);
	}
}
