<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use PHPUnit\Framework\Attributes\CoversClass;

class VirtualPage extends Page
{
}

#[CoversClass(Page::class)]
class VirtualPageTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.VirtualPage';

	public function testContent()
	{
		$page = new VirtualPage([
			'slug'    => 'test',
			'content' => [
				'title' => 'Title'
			]
		]);

		$this->assertSame('Title', $page->title()->value());
	}

	public function testContentWithIgnoredTextFile()
	{
		$page = new VirtualPage([
			'slug'    => 'test',
			'content' => [
				'title' => 'Title (virtual)'
			]
		]);

		Data::write(self::TMP . '/content/test/default.txt', [
			'title' => 'Title (on disk)'
		]);

		$this->assertSame('Title (virtual)', $page->title()->value());
	}

	public function testUpdate()
	{
		$page = new VirtualPage([
			'slug'    => 'test',
			'content' => [
				'title' => 'Title'
			]
		]);

		$page = $page->update([
			'title' => 'Title (updated)'
		]);

		$this->assertSame('Title (updated)', $page->title()->value());
	}
}
