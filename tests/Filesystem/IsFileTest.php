<?php

namespace Kirby\Filesystem;

use Kirby\Cms\App;
use Kirby\Cms\File as CmsFile;
use Kirby\Exception\BadMethodCallException;
use Kirby\Image\Image;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

class AFile
{
	use IsFile;

	public string $foo = 'bar';
}

#[CoversClass(CmsFile::class)]
class IsFileTest extends TestCase
{
	protected function _asset($file = 'blank.pdf')
	{
		return new AFile([
			'root' => '/dev/null/' . $file,
			'url'  => 'https://foo.bar/' . $file
		]);
	}

	public function testConstruct(): void
	{
		$asset = $this->_asset();

		$this->assertSame('/dev/null/blank.pdf', $asset->root());
		$this->assertSame('https://foo.bar/blank.pdf', $asset->url());
	}

	public function testAsset(): void
	{
		$asset = $this->_asset();
		$file = $asset->asset();

		$this->assertInstanceOf(File::class, $file);
		$this->assertSame($file, $asset->asset());
	}

	public function testAssetStringProp(): void
	{
		$asset = $this->_asset();
		$file =  $asset->asset('/dev/null/blank.pdf');

		$this->assertInstanceOf(File::class, $file);
		$this->assertSame('/dev/null/blank.pdf', $file->root());
	}

	public function testAssetImage(): void
	{
		$asset = $this->_asset('cat.jpg');
		$this->assertInstanceOf(Image::class, $asset->asset());
	}

	public function testKirby(): void
	{
		$asset = $this->_asset();
		$this->assertInstanceOf(App::class, $asset->kirby());
	}

	public function testCall(): void
	{
		$asset = $this->_asset();
		$this->assertSame('pdf', $asset->extension());
	}

	public function testCallPublicProperty(): void
	{
		$asset = $this->_asset();
		$this->assertSame('bar', $asset->foo());
	}

	public function testCallNotExisting(): void
	{
		$asset = $this->_asset();
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('The method: "nonexists" does not exist');
		$asset->nonexists();
	}
}
