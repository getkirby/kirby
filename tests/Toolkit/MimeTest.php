<?php

namespace Kirby\Toolkit;

class MimeTest extends TestCase
{

    const FIXTURES = __DIR__ . '/fixtures/mime';

    public function testOptimizedSvg()
    {
        $mime = Mime::type(static::FIXTURES . '/optimized.svg');
        $this->assertEquals('image/svg+xml', $mime);
    }

    public function testUnoptimizedSvg()
    {
        $mime = Mime::type(static::FIXTURES . '/unoptimized.svg');
        $this->assertEquals('image/svg+xml', $mime);
    }

}
