<?php

namespace Kirby\Image;

use PHPUnit\Framework\TestCase;

class DarkroomTest extends TestCase
{
    public function file(string $driver = null)
    {
        if ($driver !== null) {
            return __DIR__ . '/fixtures/image/cat-' . $driver . '.jpg';
        }

        return __DIR__ . '/fixtures/image/cat.jpg';
    }

    public function testFactory()
    {
        $instance = Darkroom::factory('gd');
        $this->assertInstanceOf(Darkroom\GdLib::class, $instance);

        $instance = Darkroom::factory('im');
        $this->assertInstanceOf(Darkroom\ImageMagick::class, $instance);
    }

    public function testFactoryWithInvalidType()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Invalid Darkroom type');

        $instance = Darkroom::factory('does-not-exist');
    }

    public function testCropWithoutPosition()
    {
        $darkroom = new Darkroom();
        $options  = $darkroom->preprocess($this->file(), [
            'crop'  => true,
            'width' => 100
        ]);

        $this->assertEquals('center', $options['crop']);
    }

    public function testBlurWithoutPosition()
    {
        $darkroom = new Darkroom();
        $options  = $darkroom->preprocess($this->file(), [
            'blur' => true,
        ]);

        $this->assertEquals(10, $options['blur']);
    }

    public function testQualityWithoutValue()
    {
        $darkroom = new Darkroom();
        $options  = $darkroom->preprocess($this->file(), [
            'quality' => null,
        ]);

        $this->assertEquals(90, $options['quality']);
    }

    public function testDefaults()
    {
        $darkroom = new Darkroom();
        $options  = $darkroom->preprocess('/dev/null');

        $this->assertEquals(true, $options['autoOrient']);
        $this->assertEquals(false, $options['crop']);
        $this->assertEquals(false, $options['blur']);
        $this->assertEquals(false, $options['grayscale']);
        $this->assertEquals(null, $options['height']);
        $this->assertEquals(90, $options['quality']);
        $this->assertEquals(null, $options['width']);
    }

    public function testGlobalOptions()
    {
        $darkroom = new Darkroom([
            'quality' => 20
        ]);

        $options = $darkroom->preprocess($this->file());

        $this->assertEquals(20, $options['quality']);
    }

    public function testPassedOptions()
    {
        $darkroom = new Darkroom([
            'quality' => 20
        ]);

        $options = $darkroom->preprocess($this->file(), [
            'quality' => 30
        ]);

        $this->assertEquals(30, $options['quality']);
    }

    public function testProcess()
    {
        $darkroom = new Darkroom([
            'quality' => 20
        ]);

        $options = $darkroom->process($this->file(), [
            'quality' => 30
        ]);

        $this->assertEquals(30, $options['quality']);
    }

    public function testGrayscaleFixes()
    {
        $darkroom = new Darkroom();

        // grayscale
        $options = $darkroom->preprocess($this->file(), [
            'grayscale' => true
        ]);

        $this->assertEquals(true, $options['grayscale']);

        // greyscale
        $options = $darkroom->preprocess($this->file(), [
            'greyscale' => true
        ]);

        $this->assertEquals(true, $options['grayscale']);
        $this->assertEquals(false, isset($options['greyscale']));

        // bw
        $options = $darkroom->preprocess($this->file(), [
            'bw' => true
        ]);

        $this->assertEquals(true, $options['grayscale']);
        $this->assertEquals(false, isset($options['bw']));
    }
}
