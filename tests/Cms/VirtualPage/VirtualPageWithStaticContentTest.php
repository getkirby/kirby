<?php

namespace Kirby\Cms;

use Kirby\Content\ImmutableMemoryStorage;
use PHPUnit\Framework\Attributes\CoversClass;

class VirtualPageWithStaticContent extends Page
{
	public function __construct()
	{
		parent::__construct([
			'slug' => 'test',
			'content' => [
				'title' => 'Title'
			]
		]);

		$this->changeStorage(ImmutableMemoryStorage::class);
	}
}

#[CoversClass(Page::class)]
class VirtualPageWithStaticContentTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.VirtualPageWithStaticContent';

	public function testContent(): void
	{
		$page = new VirtualPageWithStaticContent();

		$this->assertSame('Title', $page->title()->value());
	}
}
