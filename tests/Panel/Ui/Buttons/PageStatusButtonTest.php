<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(PageStatusButton::class)]
class PageStatusButtonTest extends TestCase
{
	public function testButtonDraftDisabled(): void
	{

		$page   = new Page(['slug' => 'test', 'isDraft' => true]);
		$button = new PageStatusButton($page);

		$this->assertSame('k-status-view-button', $button->component);
		$this->assertSame('k-status-view-button k-page-status-button', $button->class);
		$this->assertSame('/pages/test/changeStatus', $button->dialog);
		$this->assertTrue($button->disabled);
		$this->assertSame('status-draft', $button->icon);
		$this->assertTrue($button->responsive);
		$this->assertSame('Draft', $button->text);
		$this->assertSame('Status: Draft (Disabled)', $button->title);
		$this->assertSame('negative-icon', $button->theme);
	}

	public static function undefinedStatusProvider(): array
	{
		return [
			// the home page blueprint has no draft status
			'draft' => [
				['slug' => 'home', 'isDraft' => true],
				'status-draft',
				'negative-icon',
				'Draft'
			],
			'unlisted' => [
				['slug' => 'test', 'blueprint' => ['status' => ['draft' => true, 'listed' => true]]],
				'status-unlisted',
				'info-icon',
				'Unlisted'
			],
			'listed' => [
				['slug' => 'test', 'num' => 1, 'blueprint' => ['status' => ['draft' => true, 'unlisted' => true]]],
				'status-listed',
				'positive-icon',
				'Public'
			],
		];
	}

	#[DataProvider('undefinedStatusProvider')]
	public function testButtonForUndefinedStatus(
		array $props,
		string $icon,
		string $theme,
		string $text
	): void {
		$page   = new Page($props);
		$button = new PageStatusButton($page);

		$this->assertArrayNotHasKey($page->status(), $page->blueprint()->status());
		$this->assertSame($icon, $button->icon);
		$this->assertSame($theme, $button->theme);
		$this->assertSame($text, $button->text);
	}

	public function testButtonUnlisted(): void
	{
		App::instance()->impersonate('kirby');
		$page   = new Page(['slug' => 'test']);
		$button = new PageStatusButton($page);

		$this->assertFalse($button->disabled);
		$this->assertSame('status-unlisted', $button->icon);
		$this->assertTrue($button->responsive);
		$this->assertSame('Unlisted', $button->text);
		$this->assertSame('Status: Unlisted', $button->title);
		$this->assertSame('info-icon', $button->theme);
	}

	public function testButtonWithCustomIconAndTheme(): void
	{
		App::instance()->impersonate('kirby');
		$page   = new Page([
			'slug'      => 'test',
			'blueprint' => [
				'status' => [
					'draft'    => true,
					'unlisted' => ['label' => 'Review', 'icon' => '👀', 'theme' => 'purple'],
				]
			]
		]);
		$button = new PageStatusButton($page);

		$this->assertSame('👀', $button->icon);
		$this->assertSame('Review', $button->text);
		$this->assertSame('Status: Review', $button->title);
		$this->assertSame('purple-icon', $button->theme);
	}
}
