<?php

// namespace Kirby\Uuid;

// use Generator;
// use Kirby\Cms\Blocks;

// /**
//  * @coversDefaultClass \Kirby\Uuid\BlockUuid
//  */
// class BlockUuidTest extends TestCase
// {
// 	/**
// 	 * @covers ::fieldToCollection
// 	 */
// 	public function testFieldToCollection()
// 	{
// 		$field  = $this->app->page('page-a')->notes();
// 		$blocks = BlockUuid::fieldToCollection($field);
// 		$this->assertInstanceOf(Blocks::class, $blocks);
// 		$this->assertSame(2, $blocks->count());
// 	}

// 	/**
// 	 * @covers ::findByCache
// 	 */
// 	public function testFindByCache()
// 	{
// 		$block = $this->app->page('page-a')->notes()->toBlocks()->nth(1);

// 		// not yet in cache
// 		$uuid  = new BlockUuid('block://my-block-2');
// 		$this->assertFalse($uuid->isCached());
// 		$this->assertNull($uuid->model(true));

// 		// fill cache
// 		$block->uuid()->populate();

// 		// retrieve from cache
// 		$this->assertTrue($uuid->isCached());
// 		$this->assertTrue($block->is($uuid->model(true)));
// 	}

// 	/**
// 	 * @covers ::findByIndex
// 	 */
// 	public function testFindByIndex()
// 	{
// 		$block = $this->app->page('page-a')->notes()->toBlocks()->nth(1);
// 		$uuid  = new BlockUuid('block://my-block-2');
// 		$this->assertFalse($uuid->isCached());
// 		$this->assertNull($uuid->model(true));
// 		$this->assertTrue($block->is($uuid->model()));
// 		$this->assertTrue($uuid->isCached());

// 		// not found
// 		$uuid = new BlockUuid('block://does-not-exist');
// 		$this->assertNull($uuid->model());
// 	}

// 	/**
// 	 * @covers ::index
// 	 */
// 	public function testIndex()
// 	{
// 		$index = BlockUuid::index();
// 		$this->assertInstanceOf(Generator::class, $index);
// 		$this->assertInstanceOf(Blocks::class, $index->current());
// 		$this->assertSame(2, iterator_count($index));
// 	}

// 	/**
// 	 * @covers ::value
// 	 */
// 	public function testValue()
// 	{
// 		$block = $this->app->page('page-a')->notes()->toBlocks()->nth(1);
// 		$uuid  = $block->uuid();
// 		$this->assertSame('page://my-page/notes/my-block-2', $uuid->value());
// 	}
// }
