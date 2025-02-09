<?php

namespace Kirby\Blueprint;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NodeI18n::class)]
class NodeI18nTest extends TestCase
{
	public function testConstructWithArray()
	{
		$translated = new NodeI18n(['en' => 'Test']);
		$this->assertSame('Test', $translated->render($this->model()));
	}

	public function testConstructWithI18nKey()
	{
		$translated = new NodeI18n(['*' => 'avatar']);
		$this->assertSame('Profile picture', $translated->render($this->model()));
	}

	public function testFactory()
	{
		$translated = NodeI18n::factory('Test');
		$this->assertSame(['en' => 'Test'], $translated->translations);
	}

	public function testRenderWithNonEnglishFallback()
	{
		$translated = new NodeI18n(['de' => 'Täst']);
		$this->assertSame('Täst', $translated->render($this->model()));
	}
}
