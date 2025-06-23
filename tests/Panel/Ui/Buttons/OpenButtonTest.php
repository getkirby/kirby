<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Ui\Buttons\OpenButton
 */
class OpenButtonTest extends TestCase
{
	/**
	 * @covers ::__construct
	 */
	public function testButton()
	{
		$button = new OpenButton(link: 'https://getkirby.com');

		$this->assertSame('k-view-button', $button->component);
		$this->assertSame('k-open-view-button', $button->class);
		$this->assertSame('open', $button->icon);
		$this->assertSame('https://getkirby.com', $button->link);
		$this->assertSame('_blank', $button->target);
		$this->assertSame('Open', $button->title);
	}

}
