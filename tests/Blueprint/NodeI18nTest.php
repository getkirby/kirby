<?php

namespace Kirby\Blueprint;

/**
 * @coversDefaultClass \Kirby\Blueprint\NodeI18n
 */
class NodeI18nTest extends TestCase
{
	/**
	 * @covers ::__construct
	 * @covers ::render
	 */
	public function testConstructWithArray()
	{
		$translated = new NodeI18n(['en' => 'Test']);
		$this->assertSame('Test', $translated->render($this->model()));
	}

	/**
	 * @covers ::__construct
	 * @covers ::render
	 */
	public function testConstructWithI18nKey()
	{
		$translated = new NodeI18n(['*' => 'avatar']);
		$this->assertSame('Profile picture', $translated->render($this->model()));
	}

	/**
	 * @covers ::factory
	 */
	public function testFactory()
	{
		$translated = NodeI18n::factory('Test');
		$this->assertSame(['en' => 'Test'], $translated->translations);
	}

	/**
	 * @covers ::render
	 */
	public function testRenderWithNonEnglishFallback()
	{
		$translated = new NodeI18n(['de' => 'Täst']);
		$this->assertSame('Täst', $translated->render($this->model()));
	}
}
