<?php

namespace Kirby\Cms;


use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Page::class)]
class PageStatusTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageStatus';

	public function testIsDraft(): void
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$this->assertFalse($page->isDraft());

		$page = new Page([
			'slug' => 'test',
			'num'  => 1
		]);

		$this->assertFalse($page->isDraft(), 'The number should not affect the draft status');

		$page = new Page([
			'slug'    => 'test',
			'isDraft' => true
		]);

		$this->assertTrue($page->isDraft());
	}

	public function testIsListed(): void
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$this->assertFalse($page->isListed());

		$page = new Page([
			'slug' => 'test',
			'num'  => 1
		]);

		$this->assertTrue($page->isListed());
	}

	public function testIsListedInDraftMode(): void
	{
		$page = new Page([
			'slug'    => 'test',
			'isDraft' => true,
			'num'     => 1
		]);

		$this->assertFalse($page->isListed(), 'Drafts can never be listed');
	}

	public function testIsUnlisted(): void
	{
		$page = new Page([
			'slug' => 'test',
		]);

		$this->assertTrue($page->isUnlisted());

		$page = new Page([
			'slug'  => 'test',
			'num'   => 1
		]);

		$this->assertFalse($page->isUnlisted());
	}

	public function testIsUnlistedInDraftMode(): void
	{
		$page = new Page([
			'slug'    => 'test',
			'isDraft' => true,
		]);

		$this->assertFalse($page->isUnlisted(), 'Drafts can never be unlisted');
	}
}
