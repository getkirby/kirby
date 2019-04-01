<?php

namespace Kirby\Data;

use Kirby\Toolkit\F;
use PHPUnit\Framework\TestCase;

class PHPTest extends TestCase
{
    public function testEncode()
    {
        $input    = __DIR__ . '/fixtures/php/input.php';
        $expected = __DIR__ . '/fixtures/php/expected.php';
        $result   = PHP::encode(include $input);

        $this->assertEquals(trim(file_get_contents($expected)), $result);
    }

    public function testDecode()
    {
        $input  = include __DIR__ . '/fixtures/php/input.php';
        $result = PHP::decode($input);

        $this->assertEquals($input, $result);
    }

    public function testRead()
    {
        $input  = __DIR__ . '/fixtures/php/input.php';
        $result = PHP::read($input);

        $this->assertEquals($result, include $input);
    }

    public function testWrite()
    {
        $input = include __DIR__ . '/fixtures/php/input.php';
        $file  = __DIR__ . '/fixtures/php/tmp.php';

        $this->assertTrue(PHP::write($file, $input));

        $this->assertEquals($input, include $file);
        $this->assertEquals($input, PHP::read($file));

        F::remove($file);
    }
}
