<?php

namespace Kirby\Text;

use Kirby\Cms\App;
use Kirby\TestCase;
use Kirby\Toolkit\Html;

class EmailKirbyTagTest extends TestCase
{
	public function testEmail(): void
	{
		$app      = App::instance();
		$html     = $app->kirbytags('(email: mail@company.com?subject=Test class: email)');
		$expected = '!^<a class="email" href="mailto:(.*?)">(.*?)</a>$!';
		$this->assertMatchesRegularExpression($expected, $html);
		preg_match($expected, $html, $matches);
		$this->assertSame('mail@company.com?subject=Test', Html::decode($matches[1]));
		$this->assertSame('mail@company.com', Html::decode($matches[2]));
	}
}
