<?php

namespace Kirby\Cms;

use Kirby\Content\ImmutableMemoryStorage;
use PHPUnit\Framework\Attributes\CoversClass;

class VirtualPageWithStaticContent extends Page
{
	public function __construct()
	{
		parent::__construct(['slug' => 'test']);

		$this->changeStorage(ImmutableMemoryStorage::class);

		// add some static content
		$this->version()->save([
			'title' => 'Title'
		]);
	}
}

#[CoversClass(Page::class)]
class VirtualPageWithStaticContentTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.VirtualPageWithStaticContent';

	public function testContent()
	{
		$page = new VirtualPageWithStaticContent();

		$this->assertSame('Title', $page->title()->value());
	}
}
