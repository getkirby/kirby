<?php

namespace Kirby\Data;

use Kirby\Toolkit\F;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Kirby\Data\PHP
 */
class PHPTest extends TestCase
{
    /**
     * @covers ::encode
     */
    public function testEncode()
    {
        $input    = __DIR__ . '/fixtures/php/input.php';
        $expected = __DIR__ . '/fixtures/php/expected.php';
        $result   = PHP::encode(include $input);

        $this->assertSame(trim(file_get_contents($expected)), $result);
    }

    /**
     * @covers ::decode
     */
    public function testDecode()
    {
        $input  = include __DIR__ . '/fixtures/php/input.php';
        $result = PHP::decode($input);

        $this->assertSame($input, $result);
    }

    /**
     * @covers ::read
     */
    public function testRead()
    {
        $input  = __DIR__ . '/fixtures/php/input.php';
        $result = PHP::read($input);

        $this->assertSame($result, include $input);
    }

    /**
     * @covers ::read
     */
    public function testReadFileMissing()
    {
        $file = __DIR__ . '/tmp/does-not-exist.php';

        $this->expectException('Exception');
        $this->expectExceptionMessage('The file "' . $file . '" does not exist');

        PHP::read($file);
    }

    /**
     * @covers ::write
     */
    public function testWrite()
    {
        $input = include __DIR__ . '/fixtures/php/input.php';
        $file  = __DIR__ . '/fixtures/php/tmp.php';

        $this->assertTrue(PHP::write($file, $input));

        $this->assertSame($input, include $file);
        $this->assertSame($input, PHP::read($file));

        F::remove($file);
    }
}
