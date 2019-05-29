<?php

namespace Kirby\Toolkit;

class MimeTest extends TestCase
{
    const FIXTURES = __DIR__ . '/fixtures/mime';

    public function testFixCss()
    {
        $this->assertEquals('text/css', Mime::fix('something.css', 'text/x-asm', 'css'));
        $this->assertEquals('text/css', Mime::fix('something.css', 'text/plain', 'css'));
    }

    public function testFixSvg()
    {
        $this->assertEquals('image/svg+xml', Mime::fix('something.svg', 'image/svg', 'svg'));
        $this->assertEquals('image/svg+xml', Mime::fix(static::FIXTURES . '/optimized.svg', 'text/html', 'svg'));
        $this->assertEquals('image/svg+xml', Mime::fix(static::FIXTURES . '/unoptimized.svg', 'text/html', 'svg'));
    }

    public function testFromExtension()
    {
        $mime = Mime::fromExtension('jpg');
        $this->assertEquals('image/jpeg', $mime);
    }

    public function testFromMimeContentType()
    {
        $mime = Mime::fromMimeContentType(__FILE__);
        $this->assertEquals('text/x-php', $mime);
    }

    public function testToExtension()
    {
        $extension = Mime::toExtension('image/jpeg');
        $this->assertEquals('jpg', $extension);
    }

    public function testToExtensions()
    {
        $extensions = Mime::toExtensions('image/jpeg');
        $this->assertEquals(['jpg', 'jpeg', 'jpe'], $extensions);

        $extensions = Mime::toExtensions('text/css');
        $this->assertEquals(['css'], $extensions);
    }

    public function testTypeWithOptimizedSvg()
    {
        $mime = Mime::type(static::FIXTURES . '/optimized.svg');
        $this->assertEquals('image/svg+xml', $mime);
    }

    public function testTypeWithUnoptimizedSvg()
    {
        $mime = Mime::type(static::FIXTURES . '/unoptimized.svg');
        $this->assertEquals('image/svg+xml', $mime);
    }

    public function testTypeWithJson()
    {
        $mime = Mime::type(static::FIXTURES . '/something.json');
        $this->assertEquals('application/json', $mime);
    }

    public function testTypes()
    {
        $this->assertEquals(Mime::$types, Mime::types());
    }
}
