<?php

namespace Kirby\File;

use Kirby\Cms\App;
use PHPUnit\Framework\TestCase as TestCase;

/**
 * @coversDefaultClass \Kirby\File\Asset
 */
class AssetTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'urls' => [
                'index' => 'https://getkirby.com'
            ],
        ]);
    }

    protected function _asset($file = 'images/logo.svg')
    {
        return new Asset($file);
    }

    /**
     * @covers ::__construct
     * @covers ::root
     * @covers ::id
     * @covers ::url
     * @covers ::path
     */
    public function testConstruct()
    {
        $asset = $this->_asset($file = 'images/logo.svg');

        $this->assertSame('/dev/null/' . $file, $asset->root());
        $this->assertSame('/dev/null/' . $file, $asset->id());
        $this->assertSame('https://getkirby.com/' . $file, $asset->url());
        $this->assertSame('images', $asset->path());
    }

    /**
     * @covers ::mediaHash
     * @covers ::mediaPath
     * @covers ::mediaRoot
     * @covers ::mediaUrl
     */
    public function testMedia()
    {
        $asset = $this->_asset();

        $mediaHash = crc32('logo.svg') . '-';
        $mediaPath = 'assets/images/' . $mediaHash . '/logo.svg';

        $this->assertSame($mediaHash, $asset->mediaHash());
        $this->assertSame($mediaPath, $asset->mediaPath());
        $this->assertSame($this->app->root('media') . '/' . $mediaPath, $asset->mediaRoot());
        $this->assertSame($this->app->url('media') . '/' . $mediaPath, $asset->mediaUrl());
    }

    public function testNonExistingMethod()
    {
        $asset = $this->_asset();
        $this->expectException('\Kirby\Exception\BadMethodCallException');
        $this->expectExceptionMessage('The method: "nonexists" does not exist');
        $asset->nonexists();
    }
}
