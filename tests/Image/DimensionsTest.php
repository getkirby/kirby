<?php

namespace Kirby\Image;

use PHPUnit\Framework\TestCase;

class DimensionsTest extends TestCase
{
    public function testDimensions()
    {
        $dimensions = new Dimensions(1200, 768);
        $this->assertSame(1200, $dimensions->width());
        $this->assertSame(768, $dimensions->height());
    }

    public function testRatio()
    {
        $dimensions = new Dimensions(1200, 768);
        $this->assertSame(1.5625, $dimensions->ratio());

        $dimensions = new Dimensions(768, 1200);
        $this->assertSame(0.64, $dimensions->ratio());

        $dimensions = new Dimensions(0, 0);
        $this->assertSame(0.0, $dimensions->ratio());
    }

    public function testFit()
    {
        // zero dimensions
        $dimensions = new Dimensions(0, 0);
        $dimensions->fit(500);
        $this->assertSame(500, $dimensions->width());
        $this->assertSame(500, $dimensions->height());

        // wider than tall
        $dimensions = new Dimensions(1200, 768);
        $dimensions->fit(500);
        $this->assertSame(500, $dimensions->width());
        $this->assertSame(320, $dimensions->height());

        // taller than wide
        $dimensions = new Dimensions(768, 1200);
        $dimensions->fit(500);
        $this->assertSame(320, $dimensions->width());
        $this->assertSame(500, $dimensions->height());

        // width = height but bigger than box
        $dimensions = new Dimensions(1200, 1200);
        $dimensions->fit(500);
        $this->assertSame(500, $dimensions->width());
        $this->assertSame(500, $dimensions->height());

        // smaller than new size
        $dimensions = new Dimensions(300, 200);
        $dimensions->fit(500);
        $this->assertSame(300, $dimensions->width());
        $this->assertSame(200, $dimensions->height());
    }

    public function testFitForce()
    {
        // wider than tall
        $dimensions = new Dimensions(1200, 768);
        $dimensions->fit(2000, true);
        $this->assertSame(2000, $dimensions->width());
        $this->assertSame(1280, $dimensions->height());

        // taller than wide
        $dimensions = new Dimensions(768, 1200);
        $dimensions->fit(2000, true);
        $this->assertSame(1280, $dimensions->width());
        $this->assertSame(2000, $dimensions->height());
    }

    public function testFitWidth()
    {
        $dimensions = new Dimensions(1200, 768);
        $dimensions->fitWidth(0);
        $this->assertSame(1200, $dimensions->width());
        $this->assertSame(768, $dimensions->height());

        $dimensions = new Dimensions(1200, 768);
        $dimensions->fitWidth(500);
        $this->assertSame(500, $dimensions->width());
        $this->assertSame(320, $dimensions->height());

        // no upscale
        $dimensions = new Dimensions(1200, 768);
        $dimensions->fitWidth(2000);
        $this->assertSame(1200, $dimensions->width());
        $this->assertSame(768, $dimensions->height());

        // force upscale
        $dimensions = new Dimensions(1200, 768);
        $dimensions->fitWidth(2000, true);
        $this->assertSame(2000, $dimensions->width());
        $this->assertSame(1280, $dimensions->height());
    }

    public function testFitHeight()
    {
        $dimensions = new Dimensions(1200, 768);
        $dimensions->fitHeight(0);
        $this->assertSame(1200, $dimensions->width());
        $this->assertSame(768, $dimensions->height());

        $dimensions = new Dimensions(1200, 768);
        $dimensions->fitHeight(500);
        $this->assertSame(781, $dimensions->width());
        $this->assertSame(500, $dimensions->height());

        // no upscale
        $dimensions = new Dimensions(1200, 768);
        $dimensions->fitHeight(2000);
        $this->assertSame(1200, $dimensions->width());
        $this->assertSame(768, $dimensions->height());

        // force upscale
        $dimensions = new Dimensions(1200, 768);
        $dimensions->fitHeight(2000, true);
        $this->assertSame(3125, $dimensions->width());
        $this->assertSame(2000, $dimensions->height());
    }

    public function testFitWidthAndHeight()
    {
        $dimensions = new Dimensions(1200, 768);
        $dimensions->fitWidthAndHeight(1000, 500);
        $this->assertSame(781, $dimensions->width());
        $this->assertSame(500, $dimensions->height());

        $dimensions = new Dimensions(768, 1200);
        $dimensions->fitWidthAndHeight(500, 1000);
        $this->assertSame(500, $dimensions->width());
        $this->assertSame(781, $dimensions->height());
    }

    public function testForSvg()
    {
        $dimensions = Dimensions::forSvg(__DIR__ . '/fixtures/dimensions/circle.svg');

        $this->assertSame(50, $dimensions->width());
        $this->assertSame(50, $dimensions->height());
    }

    public function testResize()
    {
        $dimensions = new Dimensions(1200, 768);
        $dimensions->resize(2000, 800, true);
        $this->assertSame(1250, $dimensions->width());
        $this->assertSame(800, $dimensions->height());
    }

    public function testCrop()
    {
        $dimensions = new Dimensions(1200, 768);
        $dimensions->crop(1000, 500);
        $this->assertSame(1000, $dimensions->width());
        $this->assertSame(500, $dimensions->height());

        $dimensions = new Dimensions(1200, 768);
        $dimensions->crop(500);
        $this->assertSame(500, $dimensions->width());
        $this->assertSame(500, $dimensions->height());
    }

    public function testOrientation()
    {
        $dimensions = new Dimensions(1200, 768);
        $this->assertSame('landscape', $dimensions->orientation());

        $dimensions = new Dimensions(768, 1200);
        $this->assertSame('portrait', $dimensions->orientation());

        $dimensions = new Dimensions(1200, 1200);
        $this->assertSame('square', $dimensions->orientation());
        $this->assertTrue($dimensions->square());

        $dimensions = new Dimensions(0, 0);
        $this->assertFalse($dimensions->orientation());
    }

    public function testArray()
    {
        $dimensions = new Dimensions(1200, 768);
        $array = [
            'width'       => 1200,
            'height'      => 768,
            'ratio'       => 1.5625,
            'orientation' => 'landscape'
        ];
        $this->assertSame($array, $dimensions->toArray());
        $this->assertSame($array, $dimensions->__debugInfo());
    }

    public function testString()
    {
        $dimensions = new Dimensions(1200, 768);
        $this->assertSame('1200 × 768', (string)$dimensions);
    }
}
