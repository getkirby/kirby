<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Cms\PluginAsset
 */
class PluginAssetTest extends TestCase
{
	protected Plugin $plugin;
	protected string $fixture = __DIR__ . '/fixtures/plugin-assets';

	public function setUp(): void
	{
		$this->plugin = new Plugin('getkirby/test-plugin', [
			'root' => $this->fixture
		]);
	}

	/**
	 * @covers ::filename
	 */
	public function testFilename()
	{
		$asset = new PluginAsset(
			'test.css',
			$this->fixture . '/assets/test.css',
			$this->plugin
		);

		$this->assertEquals('test.css', $asset->filename());
	}

	/**
	 * @covers ::mediaHash
	 * @covers ::mediaRoot
	 * @covers ::mediaUrl
	 * @covers ::url
	 */
	public function testMedia()
	{
		$asset = new PluginAsset(
			'test.css',
			$this->fixture . '/assets/test.css',
			$this->plugin
		);

		$this->assertEquals('3526409702-1694877136', $asset->mediaHash());
		$this->assertEquals($this->plugin->mediaRoot() . '/test.css', $asset->mediaRoot());
		$this->assertEquals('/media/plugins/getkirby/test-plugin/test.css?m=3526409702-1694877136', $asset->mediaUrl());
		$this->assertEquals('/media/plugins/getkirby/test-plugin/test.css?m=3526409702-1694877136', $asset->url());
	}

	/**
	 * @covers ::modified
	 */
	public function testModified()
	{
		$asset = new PluginAsset(
			'test.css',
			$this->fixture . '/assets/test.css',
			$this->plugin
		);

		$this->assertEquals(1694877136, $asset->modified());
	}

	/**
	 * @covers ::__construct
	 * @covers ::path
	 * @covers ::plugin
	 * @covers ::root
	 */
	public function testPathRoot()
	{
		$asset = new PluginAsset(
			$path = 'test.css',
			$root = $this->fixture . '/assets/test.css',
			$plugin = $this->plugin
		);

		$this->assertEquals($path, $asset->path());
		$this->assertEquals($plugin, $asset->plugin());
		$this->assertEquals($root, $asset->root());
	}
}
