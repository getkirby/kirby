<?php

namespace Kirby\Uuid;

use Kirby\Cache\Cache;
use Kirby\Cache\MemoryCache;
use Kirby\Cache\NullCache;

/**
 * @coversDefaultClass \Kirby\Uuid\Uuids
 */
class UuidsTest extends TestCase
{
	/**
	 * @covers ::populate
	 */
	public function testPopulate()
	{
		$page     = $this->app->page('page-a');
		$pageFile = $this->app->file('page-a/test.pdf');
		$siteFile = $this->app->site()->file('site.txt');
		$userFile = $this->app->user('my-user')->file('user.jpg');
		$block    = $this->app->page('page-a')->notes()->toBlocks()->nth(1);
		$struct   = $this->app->page('page-a')->authors()->toStructure()->first();

		// all
		$this->assertFalse($page->uuid()->isCached());
		$this->assertFalse($pageFile->uuid()->isCached());
		$this->assertFalse($siteFile->uuid()->isCached());
		$this->assertFalse($userFile->uuid()->isCached());
		// TODO: activate for  uuid-block-structure-support
		// $this->assertFalse($block->uuid()->isCached());
		// $this->assertFalse($struct->uuid()->isCached());

		Uuids::populate();

		$this->assertTrue($page->uuid()->isCached());
		$this->assertTrue($pageFile->uuid()->isCached());
		$this->assertTrue($siteFile->uuid()->isCached());
		$this->assertTrue($userFile->uuid()->isCached());
		// $this->assertTrue($block->uuid()->isCached());
		// $this->assertTrue($struct->uuid()->isCached());

		Uuids::cache()->flush();

		// only pages
		$this->assertFalse($page->uuid()->isCached());
		$this->assertFalse($pageFile->uuid()->isCached());
		$this->assertFalse($siteFile->uuid()->isCached());
		$this->assertFalse($userFile->uuid()->isCached());
		// $this->assertFalse($block->uuid()->isCached());
		// $this->assertFalse($struct->uuid()->isCached());

		Uuids::populate('page');

		$this->assertTrue($page->uuid()->isCached());
		$this->assertFalse($pageFile->uuid()->isCached());
		$this->assertFalse($siteFile->uuid()->isCached());
		$this->assertFalse($userFile->uuid()->isCached());
		// $this->assertFalse($block->uuid()->isCached());
		// $this->assertFalse($struct->uuid()->isCached());

		Uuids::cache()->flush();

		// only files
		$this->assertFalse($page->uuid()->isCached());
		$this->assertFalse($pageFile->uuid()->isCached());
		$this->assertFalse($siteFile->uuid()->isCached());
		$this->assertFalse($userFile->uuid()->isCached());
		// $this->assertFalse($block->uuid()->isCached());
		// $this->assertFalse($struct->uuid()->isCached());

		Uuids::populate('file');

		$this->assertTrue($pageFile->uuid()->isCached());
		$this->assertTrue($siteFile->uuid()->isCached());
		$this->assertTrue($userFile->uuid()->isCached());
		// $this->assertFalse($block->uuid()->isCached());
		// $this->assertFalse($struct->uuid()->isCached());

		Uuids::cache()->flush();

		// only blocks
		$this->assertFalse($page->uuid()->isCached());
		$this->assertFalse($pageFile->uuid()->isCached());
		$this->assertFalse($siteFile->uuid()->isCached());
		$this->assertFalse($userFile->uuid()->isCached());
		// $this->assertFalse($block->uuid()->isCached());
		// $this->assertFalse($struct->uuid()->isCached());

		Uuids::populate('block');

		$this->assertFalse($pageFile->uuid()->isCached());
		$this->assertFalse($siteFile->uuid()->isCached());
		$this->assertFalse($userFile->uuid()->isCached());
		// $this->assertTrue($block->uuid()->isCached());
		// $this->assertFalse($struct->uuid()->isCached());

		Uuids::cache()->flush();

		// only structures
		$this->assertFalse($page->uuid()->isCached());
		$this->assertFalse($pageFile->uuid()->isCached());
		$this->assertFalse($siteFile->uuid()->isCached());
		$this->assertFalse($userFile->uuid()->isCached());
		// $this->assertFalse($block->uuid()->isCached());
		// $this->assertFalse($struct->uuid()->isCached());

		Uuids::populate('struct');

		$this->assertFalse($pageFile->uuid()->isCached());
		$this->assertFalse($siteFile->uuid()->isCached());
		$this->assertFalse($userFile->uuid()->isCached());
		// $this->assertFalse($block->uuid()->isCached());
		// $this->assertTrue($struct->uuid()->isCached());
	}

	/**
	 * @covers ::cache
	 */
	public function testStore()
	{
		$this->assertInstanceOf(Cache::class, Uuids::cache());

		$this->app->clone([
			'options' => [
				'cache' => [
					'uuid' => ['type' => 'memory']
				]
			]
		]);
		$this->assertInstanceOf(MemoryCache::class, Uuids::cache());

		$this->app->clone([
			'options' => [
				'cache' => [
					'uuid' => false
				]
			]
		]);
		$this->assertInstanceOf(NullCache::class, Uuids::cache());
	}
}
