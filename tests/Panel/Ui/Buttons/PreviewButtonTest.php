<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Ui\Buttons\PreviewButton
 */
class PreviewButtonTest extends TestCase
{
	/**
	 * @covers ::__construct
	 */
	public function testButton()
	{
		$button = new PreviewButton(link: 'https://getkirby.com');

		$this->assertSame('k-view-button', $button->component);
		$this->assertSame('k-preview-view-button', $button->class);
		$this->assertSame('window', $button->icon);
		$this->assertSame('https://getkirby.com', $button->link);
		$this->assertSame(null, $button->target);
		$this->assertSame('Preview', $button->title);
	}

}
