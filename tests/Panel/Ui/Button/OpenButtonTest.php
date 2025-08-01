<?php

namespace Kirby\Panel\Ui\Button;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(OpenButton::class)]
class OpenButtonTest extends TestCase
{
	public function testButton(): void
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
