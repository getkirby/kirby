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
     * @covers ::validateFile
     */
    public function testValidateFile()
    {
        $this->assertNull(CustomHandler::validateFile($this->fixture('doctype-valid.svg')));
    }

    /**
     * @covers ::validateFile
     */
    public function testValidateFileError()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in attribute: style');

        CustomHandler::validateFile($this->fixture('external-source-1.svg'));
    }

    /**
     * @covers ::validateFile
     */
    public function testValidateFileMissing()
    {
        $file = $this->fixture('does-not-exist.svg');

        $this->expectException('Exception');
        $this->expectExceptionMessage('The file "' . $file . '" does not exist');

        CustomHandler::validateFile($file);
    }
}
