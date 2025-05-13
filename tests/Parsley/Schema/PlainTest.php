<?php

namespace Kirby\Parsley\Schema;

use Kirby\Parsley\Element;
use Kirby\Parsley\Schema;
use Kirby\Toolkit\Dom;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Plain::class)]
class PlainTest extends TestCase
{
	/** @var \Kirby\Parsley\Schema\Plain */
	protected Schema $schema;

	public function setUp(): void
	{
		$this->schema = new Plain();
	}

	public function testFallback(): void
	{
		$expected = [
			'content' => [
				'text' => 'Test'
			],
			'type' => 'text',
		];

		$this->assertSame($expected, $this->schema->fallback('Test'));
	}

	public function testFallbackForEmptyContent(): void
	{
		$this->assertNull($this->schema->fallback(''));
	}

	public function testFallbackForDomElement(): void
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

	public function testFallbackForInvalidContent(): void
	{
		$this->assertNull($this->schema->fallback(''));
	}

	public function testMarks(): void
	{
		$this->assertSame([], $this->schema->marks());
	}

	public function testNodes(): void
	{
		$this->assertSame([], $this->schema->nodes());
	}

	public function testSkip(): void
	{
		$this->assertSame([
			'base',
			'link',
			'meta',
			'script',
			'style',
			'title'
		], $this->schema->skip());
	}
}
