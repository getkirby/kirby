<?php

namespace Kirby\Panel\Ui\Item;

use Kirby\Cms\Language;
use Kirby\Panel\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LanguageItem::class)]
class LanguageItemTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Panel.Item.Language';

	protected Language $language;

	public function setUp(): void
	{
		parent::setUp();
		$this->language = new Language([
			'code' => 'en',
			'name' => 'English',
		]);
	}

	public function testImage(): void
	{
		$item = new LanguageItem($this->language);

		$this->assertSame([
			'back' => 'black',
			'color' => 'gray',
			'icon' => 'translate',
		], $item->image());
	}

	public function testInfo(): void
	{
		$item = new LanguageItem($this->language);
		$this->assertSame('en', $item->info());
	}

	public function testProps(): void
	{
		$item = new LanguageItem($this->language);
		$props = $item->props();
		$this->assertSame('en', $props['id']);
		$this->assertSame('English', $props['text']);
		$this->assertSame('en', $props['info']);
		$this->assertSame('black', $props['image']['back']);
		$this->assertSame(false, $props['default']);
		$this->assertSame(true, $props['deletable']);
	}

	public function testRender(): void
	{
		$item = new LanguageItem($this->language);
		$item = $item->render();
		$this->assertSame('k-item', $item['component']);
		$this->assertArrayHasKey('props', $item);
	}

	public function testText(): void
	{
		$item = new LanguageItem($this->language);
		$this->assertSame('English', $item->text());
	}
}
