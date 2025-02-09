<?php

namespace Kirby\Panel\Ui\Buttons;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PreviewButton::class)]
class PreviewButtonTest extends TestCase
{
	public function testButton()
	{
		$button = new PreviewButton(link: 'https://getkirby.com');

		$this->assertSame('k-view-button', $button->component);
		$this->assertSame('k-preview-view-button', $button->class);
		$this->assertSame('open', $button->icon);
		$this->assertSame('https://getkirby.com', $button->link);
		$this->assertSame('_blank', $button->target);
		$this->assertSame('Open', $button->title);
	}

}
