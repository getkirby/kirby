<?php

namespace Kirby\Toolkit;

class MimeTest extends TestCase
{

    const FIXTURES = __DIR__ . '/fixtures/mime';

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

    public function testTypes()
    {
        $this->assertEquals(Mime::$types, Mime::types());
    }

}
