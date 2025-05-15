<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Ui\Buttons\VersionsButton
 */
class VersionsButtonTest extends TestCase
{
	/**
	 * @covers ::__construct
	 */
	public function testButton()
	{
		// needed to load the translations
		new App();

		$page   = new Page(['slug' => 'test']);
		$button = new VersionsButton(model: $page, versionId: 'latest');

		$this->assertSame('k-view-button', $button->component);
		$this->assertSame('k-versions-view-button', $button->class);
		$this->assertSame('git-branch', $button->icon);

		$this->assertSame('Latest version', $button->text);
		$this->assertSame('Latest version', $button->options[0]['label']);
		$this->assertTrue($button->options[0]['current']);

		$this->assertSame('Changed version', $button->options[1]['label']);
		$this->assertFalse($button->options[1]['current']);

		$this->assertSame('-', $button->options[2]);

		$this->assertSame('Compare versions', $button->options[3]['label']);
		$this->assertFalse($button->options[3]['current']);
	}
}
