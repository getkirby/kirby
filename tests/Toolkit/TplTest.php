<?php

namespace Kirby\Toolkit;

/**
 * @coversDefaultClass \Kirby\Toolkit\Tpl
 */
class TplTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';

	/**
	 * @covers ::load
	 */
	public function testLoadWithGoodTemplate()
	{
		$tpl = Tpl::load(static::FIXTURES . '/tpl/good.php', ['name' => 'Peter']);
		$this->assertSame('Hello Peter', $tpl);
	}

	/**
	 * @covers ::load
	 */
	public function testLoadWithBadTemplate()
	{
		$this->expectException('Error');
		Tpl::load(static::FIXTURES . '/tpl/bad.php');
	}

	/**
	 * @covers ::load
	 */
	public function testLoadWithNonExistingFile()
	{
		$tpl = Tpl::load(static::FIXTURES . '/tpl/imaginary.php');
		$this->assertSame('', $tpl);
	}
}
