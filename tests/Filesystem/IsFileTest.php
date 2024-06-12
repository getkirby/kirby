<?php

namespace Kirby\Filesystem;

use Kirby\Cms\App;
use Kirby\Exception\BadMethodCallException;
use Kirby\Image\Image;
use Kirby\TestCase;

class AFile
{
	use IsFile;

	public string $foo = 'bar';
}

/**
 * @coversDefaultClass \Kirby\Filesystem\IsFile
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
		$file = $asset->asset();

		$this->assertInstanceOf(File::class, $file);
		$this->assertSame($file, $asset->asset());
	}

	/**
	 * @covers ::asset
	 */
	public function testAssetStringProp()
	{
		$asset = $this->_asset();
		$file =  $asset->asset('/dev/null/blank.pdf');

		$this->assertInstanceOf(File::class, $file);
		$this->assertSame('/dev/null/blank.pdf', $file->root());
	}

	/**
	 * @covers ::asset
	 */
	public function testAssetImage()
	{
		$asset = $this->_asset('cat.jpg');
		$this->assertInstanceOf(Image::class, $asset->asset());
	}

	/**
	 * @covers ::kirby
	 */
	public function testKirby()
	{
		$asset = $this->_asset();
		$this->assertInstanceOf(App::class, $asset->kirby());
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
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('The method: "nonexists" does not exist');
		$asset->nonexists();
	}
}
