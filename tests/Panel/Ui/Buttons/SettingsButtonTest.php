<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Cms\Page;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SettingsButton::class)]
class SettingsButtonTest extends TestCase
{
	public function testButton()
	{
		$page   = new Page(['slug' => 'test']);
		$button = new SettingsButton(model: $page);

		$this->assertSame('k-settings-view-button', $button->component);
		$this->assertSame('k-settings-view-button', $button->class);
		$this->assertSame('cog', $button->icon);
		$this->assertSame('/pages/test', $button->options);
		$this->assertSame('Settings', $button->title);
	}
}
