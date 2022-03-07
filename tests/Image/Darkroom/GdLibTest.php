<?php

namespace Kirby\Image\Darkroom;

use Kirby\Toolkit\Dir;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Image\Darkroom\GdLib
 */
class GdLibTest extends TestCase
{
    protected $fixtures;
    protected $tmp;

    public function setUp(): void
    {
        $this->fixtures = dirname(__DIR__) . '/fixtures/image';
        $this->tmp      = dirname(__DIR__) . '/tmp';

        Dir::make($this->tmp);
    }

    public function tearDown(): void
    {
        Dir::remove($this->tmp);
    }

    public function testProcess()
    {
        $gd = new GdLib();

        copy($this->fixtures . '/cat.jpg', $file = $this->tmp . '/cat.jpg');

        $this->assertSame([
            'autoOrient' => true,
            'blur' => false,
            'crop' => false,
            'format' => null,
            'grayscale' => false,
            'height' => 500,
            'quality' => 90,
            'scaleHeight' => 1,
            'scaleWidth' => 1,
            'width' => 500,
        ], $gd->process($file));
    }

    /**
     * @covers ::mime
     */
    public function testProcessWithFormat()
    {
        $gd = new GdLib(['format' => 'webp']);
        copy($this->fixtures . '/cat.jpg', $file = $this->tmp . '/cat.jpg');
        $this->assertSame('webp', $gd->process($file)['format']);
    }
}
