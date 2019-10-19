<?php

namespace Kirby\Cms;

class AssetTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
        ]);
    }

    public function testConstruct()
    {
        $asset = new Asset($file = 'images/logo.svg');

        $this->assertEquals('/dev/null/' . $file, $asset->root());
        $this->assertEquals('/dev/null/' . $file, $asset->id());
        $this->assertEquals('images', $asset->path());
        $this->assertNull($asset->alt());

        $mediaHash = crc32('logo.svg') . '-';
        $mediaPath = 'assets/images/' . $mediaHash . '/logo.svg';

        $this->assertEquals($mediaPath, $asset->mediaPath());
        $this->assertEquals($this->app->root('media') . '/' . $mediaPath, $asset->mediaRoot());
        $this->assertEquals($this->app->url('media') . '/' . $mediaPath, $asset->mediaUrl());
    }
}
