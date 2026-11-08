<?php

namespace Kirby\Option;

use Kirby\Cms\Page;
use Kirby\TestCase;
use Kirby\Toolkit\I18n;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProtectedOption::class)]
class ProtectedOptionTest extends TestCase
{
	public function testRender(): void
	{
		$model  = new Page(['slug' => 'test']);
		$option = new ProtectedOption('secret');

		$this->assertSame([
			'disabled' => false,
			'icon'     => null,
			'info'     => null,
			'text'     => '[unknown]',
			'value'    => 'secret'
		], $option->render($model));
	}

	public function testRenderTranslated(): void
	{
		I18n::$locale       = 'de';
		I18n::$translations = ['de' => ['unknown' => 'unbekannt']];

		$model  = new Page(['slug' => 'test']);
		$option = new ProtectedOption('secret');

		$this->assertSame('[unbekannt]', $option->render($model)['text']);
	}
}
