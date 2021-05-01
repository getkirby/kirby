<?php

namespace Kirby\File;

use PHPUnit\Framework\TestCase as TestCase;

class SomethingWithAsset
{
    use HasFile;
}

class HasFileTest extends TestCase
{
    protected function _asset($file = 'blank.pdf')
    {
        return new SomethingWithAsset([
            'root' => '/dev/null/' . $file,
            'url'  => 'https://foo.bar/' . $file
        ]);
    }

    public function testConstruct()
    {
        $asset = $this->_asset();

        $this->assertSame('/dev/null/blank.pdf', $asset->root());
        $this->assertSame('https://foo.bar/blank.pdf', $asset->url());
    }

    public function testAsset()
    {
        $asset = $this->_asset();
        $this->assertInstanceOf('Kirby\File\File', $asset->asset());
    }

    public function testAssetImage()
    {
        $asset = $this->_asset('cat.jpg');
        $this->assertInstanceOf('Kirby\File\Image', $asset->asset());
    }

    public function testKirby()
    {
        $asset = $this->_asset();
        $this->assertInstanceOf('Kirby\Cms\App', $asset->kirby());
    }

    public function testCall()
    {
        $asset = $this->_asset();
        $this->assertSame('pdf', $asset->extension());
    }

    public function testCallNotExisting()
    {
        $asset = $this->_asset();
        $this->expectException('\Kirby\Exception\BadMethodCallException');
        $this->expectExceptionMessage('The method: "nonexists" does not exist');
        $asset->nonexists();
    }
}
