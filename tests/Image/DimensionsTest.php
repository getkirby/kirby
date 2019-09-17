<?php

namespace Kirby\Image;

use PHPUnit\Framework\TestCase;

class DimensionsTest extends TestCase
{
    public function testDimensions()
    {
        $dimensions = new Dimensions(1200, 768);
        $this->assertEquals(1200, $dimensions->width());
        $this->assertEquals(768, $dimensions->height());
    }

    public function testRatio()
    {
        $dimensions = new Dimensions(1200, 768);
        $this->assertEquals(1.5625, $dimensions->ratio());

        $dimensions = new Dimensions(768, 1200);
        $this->assertEquals(0.64, $dimensions->ratio());

        $dimensions = new Dimensions(0, 0);
        $this->assertEquals(0, $dimensions->ratio());
    }

    public function testFit()
    {
        // zero dimensions
        $dimensions = new Dimensions(0, 0);
        $dimensions->fit(500);
        $this->assertEquals(500, $dimensions->width());
        $this->assertEquals(500, $dimensions->height());

        // wider than tall
        $dimensions = new Dimensions(1200, 768);
        $dimensions->fit(500);
        $this->assertEquals(500, $dimensions->width());
        $this->assertEquals(320, $dimensions->height());

        // taller than wide
        $dimensions = new Dimensions(768, 1200);
        $dimensions->fit(500);
        $this->assertEquals(320, $dimensions->width());
        $this->assertEquals(500, $dimensions->height());

        // width = height but bigger than box
        $dimensions = new Dimensions(1200, 1200);
        $dimensions->fit(500);
        $this->assertEquals(500, $dimensions->width());
        $this->assertEquals(500, $dimensions->height());

        // smaller than new size
        $dimensions = new Dimensions(300, 200);
        $dimensions->fit(500);
        $this->assertEquals(300, $dimensions->width());
        $this->assertEquals(200, $dimensions->height());
    }

    public function testFitForce()
    {
        // wider than tall
        $dimensions = new Dimensions(1200, 768);
        $dimensions->fit(2000, true);
        $this->assertEquals(2000, $dimensions->width());
        $this->assertEquals(1280, $dimensions->height());

        // taller than wide
        $dimensions = new Dimensions(768, 1200);
        $dimensions->fit(2000, true);
        $this->assertEquals(1280, $dimensions->width());
        $this->assertEquals(2000, $dimensions->height());
    }

    public function testFitWidth()
    {
        $dimensions = new Dimensions(1200, 768);
        $dimensions->fitWidth(0);
        $this->assertEquals(1200, $dimensions->width());
        $this->assertEquals(768, $dimensions->height());

        $dimensions = new Dimensions(1200, 768);
        $dimensions->fitWidth(500);
        $this->assertEquals(500, $dimensions->width());
        $this->assertEquals(320, $dimensions->height());

        // no upscale
        $dimensions = new Dimensions(1200, 768);
        $dimensions->fitWidth(2000);
        $this->assertEquals(1200, $dimensions->width());
        $this->assertEquals(768, $dimensions->height());

        // force upscale
        $dimensions = new Dimensions(1200, 768);
        $dimensions->fitWidth(2000, true);
        $this->assertEquals(2000, $dimensions->width());
        $this->assertEquals(1280, $dimensions->height());
    }

    public function testFitHeight()
    {
        $dimensions = new Dimensions(1200, 768);
        $dimensions->fitHeight(0);
        $this->assertEquals(1200, $dimensions->width());
        $this->assertEquals(768, $dimensions->height());

        $dimensions = new Dimensions(1200, 768);
        $dimensions->fitHeight(500);
        $this->assertEquals(781, $dimensions->width());
        $this->assertEquals(500, $dimensions->height());

        // no upscale
        $dimensions = new Dimensions(1200, 768);
        $dimensions->fitHeight(2000);
        $this->assertEquals(1200, $dimensions->width());
        $this->assertEquals(768, $dimensions->height());

        // force upscale
        $dimensions = new Dimensions(1200, 768);
        $dimensions->fitHeight(2000, true);
        $this->assertEquals(3125, $dimensions->width());
        $this->assertEquals(2000, $dimensions->height());
    }

    public function testFitWidthAndHeight()
    {
        $dimensions = new Dimensions(1200, 768);
        $dimensions->fitWidthAndHeight(1000, 500);
        $this->assertEquals(781, $dimensions->width());
        $this->assertEquals(500, $dimensions->height());

        $dimensions = new Dimensions(768, 1200);
        $dimensions->fitWidthAndHeight(500, 1000);
        $this->assertEquals(500, $dimensions->width());
        $this->assertEquals(781, $dimensions->height());
    }

    public function testResize()
    {
        $dimensions = new Dimensions(1200, 768);
        $dimensions->resize(2000, 800, true);
        $this->assertEquals(1250, $dimensions->width());
        $this->assertEquals(800, $dimensions->height());
    }

    public function testCrop()
    {
        $dimensions = new Dimensions(1200, 768);
        $dimensions->crop(1000, 500);
        $this->assertEquals(1000, $dimensions->width());
        $this->assertEquals(500, $dimensions->height());

        $dimensions = new Dimensions(1200, 768);
        $dimensions->crop(500);
        $this->assertEquals(500, $dimensions->width());
        $this->assertEquals(500, $dimensions->height());
    }

    public function testOrientation()
    {
        $dimensions = new Dimensions(1200, 768);
        $this->assertEquals('landscape', $dimensions->orientation());

        $dimensions = new Dimensions(768, 1200);
        $this->assertEquals('portrait', $dimensions->orientation());

        $dimensions = new Dimensions(1200, 1200);
        $this->assertEquals('square', $dimensions->orientation());
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
        $this->assertEquals($array, $dimensions->toArray());
        $this->assertEquals($array, $dimensions->__debugInfo());
    }

    public function testString()
    {
        $dimensions = new Dimensions(1200, 768);
        $this->assertEquals('1200 × 768', (string)$dimensions);
    }
}
