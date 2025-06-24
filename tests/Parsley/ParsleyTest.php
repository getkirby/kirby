<?php

namespace Kirby\Parsley;

use DOMDocument;
use Kirby\Filesystem\F;
use Kirby\Parsley\Schema\Blocks;
use Kirby\TestCase;
use Kirby\Toolkit\Dom;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

class TestableParsley extends Parsley
{
	public function setBlocks(array $blocks): void
	{
		$this->blocks = $blocks;
	}
}


#[CoversClass(Parsley::class)]
class ParsleyTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';

	protected function parser(string $html = 'Test')
	{
		return new TestableParsley($html, new Blocks());
	}

	public function testBlocks(): void
	{
		$examples = glob(static::FIXTURES . '/*.html');

		foreach ($examples as $example) {
			$input    = F::read($example);
			$expected = require_once dirname($example) . '/' . F::name($example) . '.php';
			$output   = $this->parser($input)->blocks();

			$this->assertSame($expected, $output, basename($example));
		}
	}

	public static function containsBlockProvider(): array
	{
		return [
			['<h1>Test</h1>', '//h1/text()', false],
			['<h1>Test</h1>', '/html', true],
			['<h1>Test</h1>', '/html/body', true],
			['<h1>Test</h1>', '/html/body/h1', false],
			['<div><h1>Test</h1></div>', '/html/body/div', true],
			['<div><div><h1>Test</h1></div></div>', '/html/body/div', true],
		];
	}

	#[DataProvider('containsBlockProvider')]
	public function testContainsBlock($html, $query, $expected): void
	{
		$dom     = new Dom($html);
		$element = $dom->query($query)[0];

		$this->assertSame($expected, $this->parser()->containsBlock($element));
	}

	public function testContainsBlockWithText(): void
	{
		$dom     = new Dom('Test');
		$element = $dom->query('//body')[0]->childNodes[0];

		$this->assertFalse($this->parser()->containsBlock($element));
	}

	public static function isBlockProvider(): array
	{
		return [
			['<h1>Test</h1>', '/html/body/h1', true],
			['<span>Test</span>', '/html/body/span', false],
		];
	}

	public function testFallbackWithEmptyInput(): void
	{
		$this->assertNull($this->parser()->fallback(''));
	}

	#[DataProvider('isBlockProvider')]
	public function testIsBlock($html, $query, $expected): void
	{
		$dom     = new Dom($html);
		$element = $dom->query($query)[0];

		$this->assertSame($expected, $this->parser()->isBlock($element));
	}

	public static function isInlineProvider(): array
	{
		return [
			['<p>Test</p>', '/html/body/p/text()', true],
			['<p>Test</p>', '/html/body/p', false],
			['<span>Test</span>', '/html/body/span', true],
			['<i><h1>Test</h1></i>', '/html/body/i', false],
		];
	}

	#[DataProvider('isInlineProvider')]
	public function testIsInline($html, $query, $expected): void
	{
		$dom     = new Dom($html);
		$element = $dom->query($query)[0];

		$this->assertSame($expected, $this->parser()->isInline($element));
	}

	public function testIsInlineWithComment(): void
	{
		$dom     = new Dom('<p><!-- test --></p>');
		$comment = $dom->query('/html/body/p')[0]->childNodes[0];

		$this->assertFalse($this->parser()->isInline($comment));
	}

	public function testMergeOrAppendExpectMerge(): void
	{
		$parser = $this->parser();

		$parser->setBlocks([
			[
				'content' => ['text' => '<p>A</p>'],
				'type'    => 'text',
			]
		]);

		$parser->mergeOrAppend([
			'content' => ['text' => '<p>B</p>'],
			'type'    => 'text'
		]);

		$expected = [
			[
				'content' => [
					'text' => '<p>A</p> <p>B</p>'
				],
				'type' => 'text'
			]
		];

		$this->assertSame($expected, $parser->blocks());
	}

	public function testMergeOrAppendExpectAppend(): void
	{
		$parser = $this->parser();

		$parser->setBlocks([
			[
				'content' => ['text' => 'A'],
				'type'    => 'heading',
			]
		]);

		$parser->mergeOrAppend([
			'content' => ['text' => '<p>B</p>'],
			'type'    => 'text'
		]);

		$expected = [
			[
				'content' => [
					'text' => 'A'
				],
				'type' => 'heading'
			],
			[
				'content' => [
					'text' => '<p>B</p>'
				],
				'type' => 'text'
			]
		];

		$this->assertSame($expected, $parser->blocks());
	}

	public function testMergeOrAppendWithoutBlocks(): void
	{
		$parser = $this->parser();

		$parser->setBlocks([]);

		$parser->mergeOrAppend([
			'content' => ['text' => '<p>B</p>'],
			'type'    => 'text'
		]);

		$expected = [
			[
				'content' => [
					'text' => '<p>B</p>'
				],
				'type' => 'text'
			]
		];

		$this->assertSame($expected, $parser->blocks());
	}

	public function testParseNodeWithBlock(): void
	{
		$dom = new Dom('<p>Test</p>');
		$p   = $dom->query('/html/body/p')[0];

		$this->assertInstanceOf('DOMElement', $p);
		$this->assertTrue($this->parser()->parseNode($p));
	}

	public function testParseNodeWithComment(): void
	{
		$dom = new DOMDocument();
		$dom->loadHTML('<!-- comment -->');

		$comment = $dom->childNodes[1];

		$this->assertInstanceOf('DOMComment', $comment);
		$this->assertFalse($this->parser()->parseNode($comment));
	}

	public function testParseNodeWithDoctype(): void
	{
		$dom = new DOMDocument();
		$dom->loadHTML('<!doctype html>');

		$this->assertFalse($this->parser()->parseNode($dom->doctype));
	}

	public function testParseNodeWithSkippableElement(): void
	{
		$dom    = new Dom('<script src="/test.js"></script>');
		$script = $dom->query('/html/body/script')[0];

		$this->assertInstanceOf('DOMElement', $script);
		$this->assertFalse($this->parser()->parseNode($script));
	}

	public function testParseNodeWithText(): void
	{
		$dom = new Dom('Test');

		// html > body > text
		$text = $dom->query('/html/body')[0]->childNodes[0];

		$this->assertInstanceOf('DOMText', $text);
		$this->assertTrue($this->parser()->parseNode($text));
	}

	public function testSkipXmlExtension(): void
	{
		Parsley::$useXmlExtension = false;

		$output   = $this->parser('Test')->blocks();
		$expected = [
			[
				'type' => 'markdown',
				'content' => [
					'text' => 'Test'
				]
			]
		];

		$this->assertSame($output, $expected);

		// revert the global change
		Parsley::$useXmlExtension = true;
	}
}
