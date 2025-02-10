<?php

namespace Kirby\Parsley\Schema;

use Kirby\Parsley\Element;
use Kirby\Toolkit\Dom;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Plain::class)]
class PlainTest extends TestCase
{
	public function setUp(): void
	{
		$this->schema = new Plain();
	}

	public function testFallback()
	{
		$expected = [
			'content' => [
				'text' => 'Test'
			],
			'type' => 'text',
		];

		return $this->assertSame($expected, $this->schema->fallback('Test'));
	}

	public function testFallbackForEmptyContent()
	{
		return $this->assertNull($this->schema->fallback(''));
	}

	public function testFallbackForDomElement()
	{
		$dom      = new Dom('<p>Test</p>');
		$p        = $dom->query('//p')[0];
		$el       = new Element($p);
		$fallback = $this->schema->fallback($el);

		$expected = [
			'content' => [
				'text' => 'Test',
			],
			'type' => 'text'
		];

		$this->assertSame($expected, $fallback);
	}

	public function testFallbackForInvalidContent()
	{
		$this->assertNull($this->schema->fallback(''));
	}

	public function testMarks()
	{
		return $this->assertSame([], $this->schema->marks());
	}

	public function testNodes()
	{
		return $this->assertSame([], $this->schema->nodes());
	}

	public function testSkip()
	{
		return $this->assertSame([
			'base',
			'link',
			'meta',
			'script',
			'style',
			'title'
		], $this->schema->skip());
	}
}
