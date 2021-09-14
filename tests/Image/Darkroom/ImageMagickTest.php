<?php

namespace Kirby\Image\Darkroom;

use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Image\Darkroom\ImageMagick
 */
class ImageMagickTest extends TestCase
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
        $im = new ImageMagick();

        copy($this->fixtures . '/cat.jpg', $file = $this->tmp . '/cat.jpg');

        $this->assertSame([
            'autoOrient' => true,
            'blur' => false,
            'crop' => false,
            'format' => null,
            'grayscale' => false,
            'height' => 500,
            'quality' => 90,
            'width' => 500,
            'bin' => 'convert',
            'interlace' => false
        ], $im->process($file));
    }

    /**
     * @covers ::save
     */
    public function testSaveWithFormat()
    {
        $im = new ImageMagick(['format' => 'webp']);

        copy($this->fixtures . '/cat.jpg', $file = $this->tmp . '/cat.jpg');
        $this->assertFalse(F::exists($webp = $this->tmp . '/cat.webp'));
        $im->process($file);
        $this->assertTrue(F::exists($webp));
    }

    public function testKeepColorProfileStripMeta()
    {
        $files = [
            'cat.jpg',
            'onigiri-adobe-rgb-gps.jpg',
            'onigiri-adobe-rgb-gps.webp',
            'png-adobe-rgb-gps.png',
            'png-srgb-gps.png',
        ];

        foreach ($files as $basename) {
            $im = new ImageMagick([
                'bin' => 'convert',
                'width' => 250, // do some arbitrary transformation
            ]);

            copy("{$this->fixtures}/{$basename}", $file = "{$this->tmp}/{$basename}");

            // Test if profile has been kept. Errors have to be redirected to /dev/null,
            // otherwise they would be printed to stdout by ImageMagick.
            $originalProfile = shell_exec('identify -format "%[profile:icc]" ' . escapeshellarg($file) . ' 2>/dev/null');
            $im->process($file);
            $profile = shell_exec('identify -format "%[profile:icc]" ' . escapeshellarg($file) . ' 2>/dev/null');
            $this->assertTrue($profile === $originalProfile);

            // Ensure that other metadata has been stripped
            $meta = shell_exec('identify -verbose ' . escapeshellarg($file));
            $this->assertFalse(strpos($meta, 'photoshop:CaptionWriter'));
            $this->assertFalse(strpos($meta, 'GPS'));
        }
    }
}
