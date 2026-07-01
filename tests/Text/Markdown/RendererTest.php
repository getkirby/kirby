<?php

namespace Kirby\Text\Markdown;

use Kirby\TestCase;
use Kirby\Text\Markdown\AST\Document;
use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Html;
use Kirby\Text\Markdown\AST\Text;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Renderer::class)]
class RendererTest extends TestCase
{
	protected Renderer $renderer;

	public function setUp(): void
	{
		$this->renderer = new Renderer();
	}

	public function testRenderText(): void
	{
		// text is HTML-escaped, but quotes are left alone
		$node = new Text('<b> & "q"');

		$this->assertSame('&lt;b&gt; &amp; "q"', $this->renderer->render($node));
	}

	public function testRenderElement(): void
	{
		$node = new Element(name: 'p', children: [new Text('hi')]);

		$this->assertSame('<p>hi</p>', $this->renderer->render($node));
	}

	public function testRenderElementAttributes(): void
	{
		// null attributes are skipped, the rest are escaped
		$node = new Element(
			name:       'a',
			attributes: ['href' => '/x', 'title' => null],
			children:   [new Text('link')]
		);

		$this->assertSame('<a href="/x">link</a>', $this->renderer->render($node));
	}

	public function testRenderElementMultiline(): void
	{
		// a multiline element renders its children through renderNodes,
		// which wraps them in newlines
		$node = new Element(
			name:      'p',
			multiline: true,
			children:  [new Text('hi')]
		);

		$this->assertSame('<p>hi</p>', $this->renderer->render($node));
	}

	public function testRenderVoidElement(): void
	{
		$node = new Element(name: 'br');

		$this->assertSame('<br />', $this->renderer->render($node));
	}

	public function testRenderFragment(): void
	{
		// a name-less element renders its children without a wrapping tag
		$node = new Element(name: null, children: [new Text('x')]);

		$this->assertSame('x', $this->renderer->render($node));
	}

	public function testRenderHtml(): void
	{
		$node = new Html('<hr>');

		$this->assertSame('<hr>', $this->renderer->render($node));
	}

	public function testRenderHtmlSafe(): void
	{
		$renderer = new Renderer(safe: true);

		// untrusted raw HTML is escaped in safe mode
		$this->assertSame('&lt;hr&gt;', $renderer->render(new Html('<hr>')));

		// trusted HTML is passed through
		$this->assertSame('&#160;', $renderer->render(new Html('&#160;', trusted: true)));
	}

	public function testRenderDocument(): void
	{
		$node = new Document([new Text('a')]);

		$this->assertSame('a', $this->renderer->render($node));
	}

	public function testRenderNodesBreaks(): void
	{
		// breaking siblings are separated (and wrapped) by newlines
		$markup = $this->renderer->renderNodes([
			new Element(name: 'hr'),
			new Element(name: 'hr')
		]);

		$this->assertSame("\n<hr />\n<hr />\n", $markup);
	}

	public function testRenderSafeFiltersUnsafeUrl(): void
	{
		$renderer = new Renderer(safe: true);
		$node     = new Element(
			name:       'a',
			attributes: ['href' => 'javascript:alert(1)'],
			children:   [new Text('x')]
		);

		// the disallowed scheme's colon is percent-encoded
		$this->assertStringContainsString('href="javascript%3Aalert(1)"', $renderer->render($node));
	}

	public function testRenderSafeAllowsSafeUrl(): void
	{
		$renderer = new Renderer(safe: true);
		$node     = new Element(
			name:       'a',
			attributes: ['href' => 'https://example.com'],
			children:   [new Text('x')]
		);

		$this->assertStringContainsString('href="https://example.com"', $renderer->render($node));
	}

	public function testRenderSafeStripsEventHandler(): void
	{
		$renderer = new Renderer(safe: true);
		$node     = new Element(
			name:       'a',
			attributes: ['href' => 'https://example.com', 'onclick' => 'evil()'],
			children:   [new Text('x')]
		);

		$this->assertStringNotContainsString('onclick', $renderer->render($node));
	}

	public function testRenderSafeStripsInvalidAttribute(): void
	{
		$renderer = new Renderer(safe: true);
		$node     = new Element(
			name:       'span',
			attributes: ['data attr' => 'x'],
			children:   [new Text('y')]
		);

		$this->assertSame('<span>y</span>', $renderer->render($node));
	}

	public function testRenderSafeNamelessElement(): void
	{
		// a name-less element drops all attributes in safe mode
		// and renders its children without a wrapping tag
		$renderer = new Renderer(safe: true);
		$node     = new Element(
			name:       null,
			attributes: ['href' => 'x'],
			children:   [new Text('y')]
		);

		$this->assertSame('y', $renderer->render($node));
	}
}
