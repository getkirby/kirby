<?php

namespace Kirby\Data;

use PHPUnit\Framework\TestCase;
use Kirby\Toolkit\F;

require_once(__DIR__ . '/mocks.php');

/**
 * @coversDefaultClass Kirby\Data\Handler
 */
class HandlerTest extends TestCase
{
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

        $file = __DIR__ . '/tmp/data.json';

        // clean up first
        @unlink($file);

        CustomHandler::write($file, $data);
        $this->assertFileExists($file);
        $this->assertEquals(CustomHandler::encode($data), F::read($file));

        $result = CustomHandler::read($file);
        $this->assertEquals($data, $result);
    }

    /**
     * @covers ::read
     */
    public function testReadFileMissing()
    {
        $file = __DIR__ . '/tmp/does-not-exist.json';

        $this->expectException('Exception');
        $this->expectExceptionMessage('The file "' . $file . '" does not exist');

        CustomHandler::read($file);
    }
}
