<?php

namespace Kirby\Text\Tags;

use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    public function testDownloadAttribute()
    {
        $tag  = new File();
        $html = '<a download href="license.pdf">license.pdf</a>';
        $this->assertEquals($html, $tag->parse('license.pdf'));
    }
}
