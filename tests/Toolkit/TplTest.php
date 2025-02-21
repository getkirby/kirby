<?php

namespace Kirby\Toolkit;

use Error;

class TplTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';

	public function testLoadWithGoodTemplate(): void
	{
		$tpl = Tpl::load(static::FIXTURES . '/tpl/good.php', ['name' => 'Peter']);
		$this->assertSame('Hello Peter', $tpl);
	}

	public function testLoadWithBadTemplate(): void
	{
		$this->expectException(Error::class);
		Tpl::load(static::FIXTURES . '/tpl/bad.php');
	}

	public function testLoadWithNonExistingFile(): void
	{
		$tpl = Tpl::load(static::FIXTURES . '/tpl/imaginary.php');
		$this->assertSame('', $tpl);
	}
}
