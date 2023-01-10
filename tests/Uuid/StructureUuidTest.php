<?php

// namespace Kirby\Uuid;

// use Generator;
// use Kirby\Cms\Structure;

// /**
//  * @coversDefaultClass \Kirby\Uuid\StructureUuid
//  */
// class StructureUuidTest extends TestCase
// {

// 	/**
// 	 * @covers ::fieldToCollection
// 	 */
// 	public function testFieldToCollection()
// 	{
// 		$field     = $this->app->page('page-a')->authors();
// 		$structure = StructureUuid::fieldToCollection($field);
// 		$this->assertInstanceOf(Structure::class, $structure);
// 		$this->assertSame(2, $structure->count());
// 	}

// 	/**
// 	 * @covers ::findByCache
// 	 */
// 	public function testFindByCache()
// 	{
// 		$struct = $this->app->page('page-a')->authors()->toStructure()->first();

// 		// not yet in cache
// 		$uuid  = new StructureUuid('struct://my-struct');
// 		$this->assertFalse($uuid->isCached());
// 		$this->assertNull($uuid->model(true));

// 		// fill cache
// 		$struct->uuid()->populate();

// 		// retrieve from cache
// 		$this->assertTrue($uuid->isCached());
// 		$this->assertTrue($struct->is($uuid->model(true)));
// 	}

// 	/**
// 	 * @covers ::findByIndex
// 	 */
// 	public function testFindByIndex()
// 	{
// 		$struct = $this->app->page('page-a')->authors()->toStructure()->first();
// 		$uuid   = new StructureUuid('struct://my-struct');
// 		$this->assertFalse($uuid->isCached());
// 		$this->assertNull($uuid->model(true));
// 		$this->assertTrue($struct->is($uuid->model()));
// 		$this->assertTrue($uuid->isCached());

// 		// not found
// 		$uuid = new StructureUuid('struct://does-not-exist');
// 		$this->assertNull($uuid->model());
// 	}

// 	/**
// 	 * @covers ::index
// 	 */
// 	public function testIndex()
// 	{
// 		$index = StructureUuid::index();
// 		$this->assertInstanceOf(Generator::class, $index);
// 		$this->assertInstanceOf(Structure::class, $index->current());
// 		$this->assertSame(2, iterator_count($index));
// 	}

// 	/**
// 	 * @covers ::value
// 	 */
// 	public function testValue()
// 	{
// 		$struct = $this->app->page('page-a')->authors()->toStructure()->first();
// 		$uuid   = $struct->uuid();
// 		$this->assertSame('page://my-page/authors/my-struct', $uuid->value());
// 	}
// }
