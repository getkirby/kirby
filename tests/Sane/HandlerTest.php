<?php

namespace Kirby\Sane;

use Exception;
use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Handler::class)]
class HandlerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Sane.Handler';

	protected static string $type = 'sane';

	public function testSanitizeFile(): void
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

	public function testSanitizeFileMissing(): void
	{
		$file = $this->fixture('does-not-exist.svg');

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('The file "' . $file . '" does not exist');

		CustomHandler::sanitizeFile($file);
	}

	public function testValidateFile(): void
	{
		$this->assertNull(
			CustomHandler::validateFile($this->fixture('doctype-valid.svg'))
		);
	}

	public function testValidateFileError(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL is not allowed in attribute "style"');

		CustomHandler::validateFile($this->fixture('external-source-1.svg'));
	}

	public function testValidateFileErrorExternalFile(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL points outside of the site index URL');

		CustomHandler::validateFile($this->fixture('xlink-subfolder.svg'));
	}

	public function testValidateFileMissing(): void
	{
		$file = $this->fixture('does-not-exist.svg');

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('The file "' . $file . '" does not exist');

		CustomHandler::validateFile($file);
	}
}
