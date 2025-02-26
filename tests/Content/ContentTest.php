<?php

namespace Kirby\Content;

use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Content\Content
 */
class ContentTest extends TestCase
{
	protected Content $content;
	protected ModelWithContent $parent;

	public function setUp(): void
	{
		$this->parent  = new Page(['slug' => 'test']);
		$this->content = new Content([
			'a' => 'A',
			'B' => 'B',
			'MiXeD' => 'mixed',
			'mIXeD' => 'MIXED'
		], $this->parent);
	}

	/**
	 * @covers ::__call
	 */
	public function testCall()
	{
		$this->assertSame('a', $this->content->a()->key());
		$this->assertSame('A', $this->content->a()->value());
		$this->assertSame('mixed', $this->content->mixed()->key());
		$this->assertSame('MIXED', $this->content->mixed()->value());
		$this->assertSame('mixed', $this->content->mIXEd()->key());
		$this->assertSame('MIXED', $this->content->mIXEd()->value());
	}

	/**
	 * @covers ::parent
	 */
	public function testParent()
	{
		$this->assertSame($this->parent, $this->content->parent());
	}

	/**
	 * @covers ::setParent
	 */
	public function testSetParent()
	{
		$page = new Page(['slug' => 'another-test']);
		$this->content->setParent($page);

		$this->assertIsPage($page, $this->content->parent());
	}
}
