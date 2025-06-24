<?php

namespace Kirby\Plugin;

use Kirby\Cms\TestCase;
use Kirby\Filesystem\Dir;

/**
 * @coversDefaultClass \Kirby\Plugin\Asset
 */
class AssetTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/plugin-assets';
	public const TMP      = KIRBY_TMP_DIR . '/Plugin.Asset';

	protected Plugin $plugin;

	public function setUp(): void
	{
		parent::setUp();

		Dir::copy(static::FIXTURES, static::TMP . '/test-plugin');

		$this->plugin = new Plugin('getkirby/test-plugin', [
			'root' => static::TMP . '/test-plugin'
		]);

		touch(static::TMP . '/test-plugin/assets/test.css', 1337000000);
	}

	/**
	 * @covers ::extension
	 */
	public function testExtension()
	{
		$asset = new Asset(
			'test.css',
			static::TMP . '/test-plugin/assets/test.css',
			$this->plugin
		);

		$this->assertSame('css', $asset->extension());
	}

	/**
	 * @covers ::filename
	 */
	public function testFilename()
	{
		$asset = new Asset(
			'test.css',
			static::TMP . '/test-plugin/assets/test.css',
			$this->plugin
		);

		$this->assertSame('test.css', $asset->filename());
	}

	/**
	 * @covers ::mediaHash
	 * @covers ::mediaRoot
	 * @covers ::mediaUrl
	 * @covers ::url
	 * @covers ::__toString
	 */
	public function testMedia()
	{
		$asset = new Asset(
			'test.css',
			static::TMP . '/test-plugin/assets/test.css',
			$this->plugin
		);

		$this->assertSame('3526409702-1337000000', $asset->mediaHash());
		$this->assertSame($this->plugin->mediaRoot() . '/3526409702-1337000000/test.css', $asset->mediaRoot());
		$this->assertSame($url = '/media/plugins/getkirby/test-plugin/3526409702-1337000000/test.css', $asset->mediaUrl());
		$this->assertSame($url, $asset->url());
		$this->assertSame($url, (string)$asset);
	}

	/**
	 * @covers ::modified
	 */
	public function testModified()
	{
		$asset = new Asset(
			'test.css',
			static::TMP . '/test-plugin/assets/test.css',
			$this->plugin
		);

		$this->assertSame(1337000000, $asset->modified());
	}

	/**
	 * @covers ::__construct
	 * @covers ::path
	 * @covers ::plugin
	 * @covers ::root
	 */
	public function testPathRoot()
	{
		$asset = new Asset(
			$path = 'test.css',
			$root = static::TMP . '/test-plugin/assets/test.css',
			$plugin = $this->plugin
		);

		$this->assertSame($path, $asset->path());
		$this->assertSame($plugin, $asset->plugin());
		$this->assertSame($root, $asset->root());
	}

	/**
	 * @covers ::publish
	 */
	public function testPublish()
	{
		$asset = new Asset(
			'test.css',
			static::TMP . '/test-plugin/assets/test.css',
			$this->plugin
		);

		$this->assertFileDoesNotExist($asset->mediaRoot());
		$asset->publish();
		$this->assertFileExists($asset->mediaRoot());
	}
}
