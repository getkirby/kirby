<?php

namespace Kirby\Panel\Ui\Button;

use Kirby\Cms\Page;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(VersionsButton::class)]
class VersionsButtonTest extends TestCase
{
	public function testButton(): void
	{
		$page   = new Page(['slug' => 'test']);
		$button = new VersionsButton(model: $page, mode: 'latest');

		$this->assertSame('k-view-button', $button->component);
		$this->assertSame('k-versions-view-button', $button->class);
		$this->assertSame('git-branch', $button->icon);
	}

	public function testIcon(): void
	{
		$page   = new Page(['slug' => 'test']);
		$button = new VersionsButton(model: $page, mode: 'latest');
		$this->assertSame('git-branch', $button->icon());

		$button = new VersionsButton(model: $page, mode: 'compare');
		$this->assertSame('layout-columns', $button->icon());
	}

	public function testIsCurrent(): void
	{
		$page   = new Page(['slug' => 'test']);
		$button = new VersionsButton(model: $page, mode: 'latest');
		$this->assertTrue($button->isCurrent('latest'));
		$this->assertFalse($button->isCurrent('changes'));
	}

	public function testMode(): void
	{
		$page   = new Page(['slug' => 'test']);

		$button = new VersionsButton(model: $page, mode: 'latest');
		$this->assertSame('latest', $button->mode());

		$button = new VersionsButton(model: $page, mode: 'compare');
		$this->assertSame('compare', $button->mode());
	}

	public function testOptions(): void
	{
		$page   = new Page(['slug' => 'test']);
		$button = new VersionsButton(model: $page, mode: 'latest');

		$options = $button->options();
		$this->assertSame('Latest version', $options[0]['label']);
		$this->assertTrue($options[0]['current']);

		$this->assertSame('Changed version', $options[1]['label']);
		$this->assertFalse($options[1]['current']);

		$this->assertSame('-', $options[2]);

		$this->assertSame('Compare versions', $options[3]['label']);
		$this->assertFalse($options[3]['current']);
	}

	public function testProps(): void
	{
		$page   = new Page(['slug' => 'test']);
		$button = new VersionsButton(model: $page, mode: 'latest');

		$props = $button->props();
		$this->assertIsArray($props['options']);
	}

	public function testUrl(): void
	{
		$page   = new Page(['slug' => 'test']);
		$button = new VersionsButton(model: $page, mode: 'latest');

		$this->assertSame('/pages/test/preview/latest', $button->url('latest'));
		$this->assertSame('/pages/test/preview/changes', $button->url('changes'));
	}
}
