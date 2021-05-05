<?php

namespace Kirby\File;

use PHPUnit\Framework\TestCase as TestCase;

class AFile
{
    use IsFile;

    public $foo = 'bar';
}

/**
 * @coversDefaultClass \Kirby\File\IsFile
 */
class IsFileTest extends TestCase
{
    protected function _asset($file = 'blank.pdf')
    {
        return new AFile([
            'root' => '/dev/null/' . $file,
            'url'  => 'https://foo.bar/' . $file
        ]);
    }

    /**
     * @covers ::__construct
     * @covers ::root
     * @covers ::url
     */
    public function testConstruct()
    {
        $asset = $this->_asset();

        $this->assertSame('/dev/null/blank.pdf', $asset->root());
        $this->assertSame('https://foo.bar/blank.pdf', $asset->url());
    }

    /**
     * @covers ::asset
     */
    public function testAsset()
    {
        $asset = $this->_asset();
        $this->assertInstanceOf('Kirby\File\File', $asset->asset());
    }

    /**
     * @covers ::asset
     */
    public function testAssetImage()
    {
        $asset = $this->_asset('cat.jpg');
        $this->assertInstanceOf('Kirby\Image\Image', $asset->asset());
    }

    /**
     * @covers ::kirby
     */
    public function testKirby()
    {
        $asset = $this->_asset();
        $this->assertInstanceOf('Kirby\Cms\App', $asset->kirby());
    }

    /**
     * @covers ::__call
     */
    public function testCall()
    {
        $asset = $this->_asset();
        $this->assertSame('pdf', $asset->extension());
    }

    /**
     * @covers ::__call
     */
    public function testCallPublicProperty()
    {
        $asset = $this->_asset();
        $this->assertSame('bar', $asset->foo());
    }

    /**
     * @covers ::__call
     */
    public function testCallNotExisting()
    {
        $asset = $this->_asset();
        $this->expectException('\Kirby\Exception\BadMethodCallException');
        $this->expectExceptionMessage('The method: "nonexists" does not exist');
        $asset->nonexists();
    }
}
