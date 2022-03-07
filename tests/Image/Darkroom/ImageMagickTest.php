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
            'scaleHeight' => 1,
            'scaleWidth' => 1,
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

    /**
     * @dataProvider keepColorProfileStripMetaProvider
     */
    public function testKeepColorProfileStripMeta(string $basename, bool $crop)
    {
        $im = new ImageMagick([
            'bin' => 'convert',
            'crop' => $crop,
            'width' => 250, // do some arbitrary transformation
        ]);

        copy($this->fixtures . '/' . $basename, $file = $this->tmp . '/' . $basename);

        // test if profile has been kept
        // errors have to be redirected to /dev/null, otherwise they would be printed to stdout by ImageMagick
        $originalProfile = shell_exec('identify -format "%[profile:icc]" ' . escapeshellarg($file) . ' 2>/dev/null');
        $im->process($file);
        $profile = shell_exec('identify -format "%[profile:icc]" ' . escapeshellarg($file) . ' 2>/dev/null');

        if (F::extension($basename) === 'png') {
            // ensure that the profile has been stripped from PNG files, because
            // ImageMagick cannot keep it while stripping all other metadata
            // (tested with ImageMagick 7.0.11-14 Q16 x86_64 2021-05-31)
            $this->assertNull($profile);
        } else {
            // ensure that the profile has been kept for all other file types
            $this->assertSame($originalProfile, $profile);
        }

        // ensure that other metadata has been stripped
        $meta = shell_exec('identify -verbose ' . escapeshellarg($file));
        $this->assertStringNotContainsString('photoshop:CaptionWriter', $meta);
        $this->assertStringNotContainsString('GPS', $meta);
    }

    public function keepColorProfileStripMetaProvider(): array
    {
        return [
            ['cat.jpg', false],
            ['cat.jpg', true],
            ['onigiri-adobe-rgb-gps.jpg', false],
            ['onigiri-adobe-rgb-gps.jpg', true],
            ['onigiri-adobe-rgb-gps.webp', false],
            ['onigiri-adobe-rgb-gps.webp', true],
            ['png-adobe-rgb-gps.png', false],
            ['png-adobe-rgb-gps.png', true],
            ['png-srgb-gps.png', false],
            ['png-srgb-gps.png', true],
        ];
    }
}
