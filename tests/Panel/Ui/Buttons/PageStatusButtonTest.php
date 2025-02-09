<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PageStatusButton::class)]
class PageStatusButtonTest extends TestCase
{
	public function testButtonDraftDisabled()
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

	public function testButtonUnlisted()
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
}
