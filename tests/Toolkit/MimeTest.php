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

    public function testIsAccepted()
    {
        $pattern = 'text/html,text/plain;q=0.8,application/*;q=0.7';

        $this->assertTrue(Mime::isAccepted('text/html', $pattern));
        $this->assertTrue(Mime::isAccepted('text/plain', $pattern));
        $this->assertTrue(Mime::isAccepted('application/json', $pattern));
        $this->assertTrue(Mime::isAccepted('application/yaml', $pattern));

        $this->assertFalse(Mime::isAccepted('text/xml', $pattern));
    }

    public function testMatches()
    {
        $this->assertTrue(Mime::matches('text/plain', 'text/plain'));
        $this->assertTrue(Mime::matches('text/plain', 'text/*'));
        $this->assertTrue(Mime::matches('text/xml', 'text/*'));
        $this->assertTrue(Mime::matches('text/plain', '*/plain'));
        $this->assertTrue(Mime::matches('application/plain', '*/plain'));
        $this->assertTrue(Mime::matches('text/plain', '*/*'));
        $this->assertTrue(Mime::matches('application/json', '*/*'));

        $this->assertFalse(Mime::matches('text/xml', 'text/plain'));
        $this->assertFalse(Mime::matches('application/json', 'text/*'));
        $this->assertFalse(Mime::matches('text/xml', '*/plain'));
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
