<?php

namespace Kirby\Text\Tag;

use Kirby\Cms\App;
use Kirby\TestCase;
use Kirby\Text\KirbyTag;
use Kirby\Toolkit\Html;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(EmailTag::class)]
class EmailTagTest extends TestCase
{
	protected function setUp(): void
	{
		KirbyTag::$types = ['email' => EmailTag::class];
	}

	protected function tearDown(): void
	{
		KirbyTag::$aliases = [];
		KirbyTag::$types   = [];
		App::destroy();
	}

	public function testRender(): void
	{
		$tag  = EmailTag::factory('email', 'mail@company.com?subject=Test', ['class' => 'email']);
		$html = $tag->render();

		$expected = '!^<a class="email" href="mailto:(.*?)">(.*?)</a>$!';
		$this->assertMatchesRegularExpression($expected, $html);

		preg_match($expected, $html, $matches);
		$this->assertSame('mail@company.com?subject=Test', Html::decode($matches[1]));
		$this->assertSame('mail@company.com', Html::decode($matches[2]));
	}
}
