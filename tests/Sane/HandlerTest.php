<?php

namespace Kirby\Sane;

require_once __DIR__ . '/mocks.php';

/**
 * @coversDefaultClass \Kirby\Sane\Handler
 */
class HandlerTest extends TestCase
{
    protected $type = 'sane';

    /**
     * @covers ::sanitizeFile
     * @covers ::readFile
     */
    public function testSanitizeFile()
    {
        $expected = $this->fixture('doctype-valid.svg');
        $tmp      = $this->fixture('doctype-valid.svg', true);
        $this->assertNull(CustomHandler::sanitizeFile($tmp));
        $this->assertFileEquals($expected, $tmp);

        $expected = $this->fixture('external-source-1.sanitized.svg');
        $tmp      = $this->fixture('external-source-1.svg', true);
        $this->assertNull(CustomHandler::sanitizeFile($tmp));
        $this->assertFileEquals($expected, $tmp);
    }

    /**
     * @covers ::sanitizeFile
     * @covers ::readFile
     */
    public function testSanitizeFileMissing()
    {
        $file = $this->fixture('does-not-exist.svg');

        $this->expectException('Exception');
        $this->expectExceptionMessage('The file "' . $file . '" does not exist');

        CustomHandler::sanitizeFile($file);
    }

    /**
     * @covers ::validateFile
     * @covers ::readFile
     */
    public function testValidateFile()
    {
        $this->assertNull(CustomHandler::validateFile($this->fixture('doctype-valid.svg')));
    }

    /**
     * @covers ::validateFile
     * @covers ::readFile
     */
    public function testValidateFileError()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in attribute "style"');

        CustomHandler::validateFile($this->fixture('external-source-1.svg'));
    }

    /**
     * @covers ::validateFile
     * @covers ::readFile
     */
    public function testValidateFileMissing()
    {
        $file = $this->fixture('does-not-exist.svg');

        $this->expectException('Exception');
        $this->expectExceptionMessage('The file "' . $file . '" does not exist');

        CustomHandler::validateFile($file);
    }
}
