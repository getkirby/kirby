<?php

namespace Kirby\Filesystem;

use PHPUnit\Framework\TestCase as TestCase;

/**
 * @coversDefaultClass \Kirby\Filesystem\Mime
 */
class MimeTest extends TestCase
{
    protected $fixtures = __DIR__ . '/fixtures/mime';

    /**
     * @covers ::fix
     */
    public function testFixCss()
    {
        $this->assertSame('text/css', Mime::fix('something.css', 'text/x-asm', 'css'));
        $this->assertSame('text/css', Mime::fix('something.css', 'text/plain', 'css'));
    }

    /**
     * @covers ::fix
     */
    public function testFixSvg()
    {
        $this->assertSame('image/svg+xml', Mime::fix('something.svg', 'image/svg', 'svg'));
        $this->assertSame('image/svg+xml', Mime::fix($this->fixtures . '/optimized.svg', 'text/html', 'svg'));
        $this->assertSame('image/svg+xml', Mime::fix($this->fixtures . '/unoptimized.svg', 'text/html', 'svg'));
    }

    /**
     * @covers ::fromExtension
     */
    public function testFromExtension()
    {
        $mime = Mime::fromExtension('jpg');
        $this->assertSame('image/jpeg', $mime);
    }

    /**
     * @covers ::fromMimeContentType
     */
    public function testFromMimeContentType()
    {
        $mime = Mime::fromMimeContentType(__FILE__);
        $this->assertSame('text/x-php', $mime);
    }

    /**
     * @covers ::fromSvg
     */
    public function testFromSvg()
    {
        $mime = Mime::fromSvg(__DIR__ . '/fixtures/mime/optimized.svg');
        $this->assertSame('image/svg+xml', $mime);
    }

    /**
     * @covers ::fromSvg
     */
    public function testFromSvgNonExistingFile()
    {
        $mime = Mime::fromSvg(__DIR__ . '/imaginary.svg');
        $this->assertFalse($mime);
    }

    /**
     * @covers ::isAccepted
     */
    public function testIsAccepted()
    {
        $pattern = 'text/html,text/plain;q=0.8,application/*;q=0.7';

        $this->assertTrue(Mime::isAccepted('text/html', $pattern));
        $this->assertTrue(Mime::isAccepted('text/plain', $pattern));
        $this->assertTrue(Mime::isAccepted('application/json', $pattern));
        $this->assertTrue(Mime::isAccepted('application/yaml', $pattern));

        $this->assertFalse(Mime::isAccepted('text/xml', $pattern));
    }

    /**
     * @covers ::matches
     */
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

    /**
     * @covers ::toExtension
     */
    public function testToExtension()
    {
        $extension = Mime::toExtension('image/jpeg');
        $this->assertSame('jpg', $extension);

        $extensions = Mime::toExtension('text/css');
        $this->assertSame('css', $extensions);
    }

    /**
     * @covers ::toExtensions
     */
    public function testToExtensions()
    {
        $extensions = Mime::toExtensions('image/jpeg');
        $this->assertSame(['jpg', 'jpeg', 'jpe'], $extensions);

        $extensions = Mime::toExtensions('text/css');
        $this->assertSame(['css'], $extensions);
    }

    /**
     * @covers ::type
     */
    public function testTypeWithOptimizedSvg()
    {
        $mime = Mime::type($this->fixtures . '/optimized.svg');
        $this->assertSame('image/svg+xml', $mime);
    }

    /**
     * @covers ::type
     */
    public function testTypeWithUnoptimizedSvg()
    {
        $mime = Mime::type($this->fixtures . '/unoptimized.svg');
        $this->assertSame('image/svg+xml', $mime);
    }

    /**
     * @covers ::type
     */
    public function testTypeWithJson()
    {
        $mime = Mime::type($this->fixtures . '/something.json');
        $this->assertSame('application/json', $mime);
    }

    /**
     * @covers ::types
     */
    public function testTypes()
    {
        $this->assertSame(Mime::$types, Mime::types());
    }
}
