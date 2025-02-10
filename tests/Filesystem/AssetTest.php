<?php

namespace Kirby\Filesystem;

use Kirby\Cms\App;
use Kirby\Exception\BadMethodCallException;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Asset::class)]
class AssetTest extends TestCase
{
	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => '/dev/null'
			],
			'urls' => [
				'index' => 'https://getkirby.com'
			],
			'assetMethods' => [
				'test' => fn () => 'asset method'
			]
		]);
	}

	protected function _asset($file = 'images/logo.svg')
	{
		return new Asset($file);
	}

	public function testConstruct()
	{
		$asset = $this->_asset($file = 'images/logo.svg');

		$this->assertSame('/dev/null/' . $file, $asset->root());
		$this->assertSame('/dev/null/' . $file, $asset->id());
		$this->assertSame('https://getkirby.com/' . $file, $asset->url());
		$this->assertSame('images', $asset->path());

		$this->app->clone([
			'urls' => [
				'index' => '/'
			]
		]);

		$asset = $this->_asset($file = 'images/logo.svg');

		$this->assertSame('/dev/null/' . $file, $asset->root());
		$this->assertSame('/dev/null/' . $file, $asset->id());
		$this->assertSame('/' . $file, $asset->url());
		$this->assertSame('images', $asset->path());
	}

	public function testCall()
	{
		$asset = $this->_asset($file = 'images/logo.svg');

		// property access
		$this->assertSame('images', $asset->path());

		// asset method
		$this->assertSame('image/svg+xml', $asset->mime());

		// custom method
		$this->assertSame('asset method', $asset->test());

		// invalid
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('The method: "foo" does not exist');
		$asset->foo();
	}

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

	public function testToString()
	{
		$asset = $this->_asset();
		$this->assertSame('<img alt="" src="https://getkirby.com/images/logo.svg">', $asset->__toString());
	}

	public function testNonExistingMethod()
	{
		$asset = $this->_asset();
		$this->expectException(BadMethodCallException::class);
		$this->expectExceptionMessage('The method: "nonexists" does not exist');
		$asset->nonexists();
	}
}
