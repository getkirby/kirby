<?php

namespace Kirby\Parsley\Schema;

use Kirby\Parsley\Element;
use Kirby\Parsley\Schema;
use Kirby\Toolkit\Dom;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Blocks::class)]
class BlocksTest extends TestCase
{
	/** @var \Kirby\Parsley\Schema\Blocks */
	protected Schema $schema;

	public function setUp(): void
	{
		$this->schema = new Blocks();
	}

	protected function element($html, $query): Element
	{
		$dom  = new Dom($html);
		$node = $dom->query($query)[0];
		return new Element($node);
	}

	public function testBlockquote(): void
	{
		$html = <<<HTML
			<blockquote>
				Test
			</blockquote>
			HTML;

		$element  = $this->element($html, '//blockquote');
		$expected = [
			'content' => [
				'citation' => null,
				'text'     => 'Test'
			],
			'type' => 'quote',
		];

		$this->assertSame($expected, $this->schema->blockquote($element));
	}

	public function testBlockquoteWithMarks(): void
	{
		$html = <<<HTML
			<blockquote>
				<p><b>Bold</b> <i>Italic</i></p>
			</blockquote>
			HTML;

		$element  = $this->element($html, '//blockquote');
		$expected = [
			'content' => [
				'citation' => null,
				'text'     => '<p><b>Bold</b> <i>Italic</i></p>'
			],
			'type' => 'quote',
		];

		$this->assertSame($expected, $this->schema->blockquote($element));
	}

	public function testBlockquoteWithParagraphs(): void
	{
		$html = <<<HTML
			<blockquote>
				<p>A</p>
				<p>B</p>
			</blockquote>
			HTML;

		$element  = $this->element($html, '//blockquote');
		$expected = [
			'content' => [
				'citation' => null,
				'text'     => '<p>A</p><p>B</p>'
			],
			'type' => 'quote',
		];

		$this->assertSame($expected, $this->schema->blockquote($element));
	}

	public function testBlockquoteWithFooter(): void
	{
		$html = <<<HTML
			<blockquote>
				Test
				<footer>Albert Einstein</footer>
			</blockquote>
			HTML;

		$element  = $this->element($html, '//blockquote');
		$expected = [
			'content' => [
				'citation' => 'Albert Einstein',
				'text'     => 'Test'
			],
			'type' => 'quote',
		];

		$this->assertSame($expected, $this->schema->blockquote($element));
	}

	public function testFallback(): void
	{
		$expected = [
			'content' => [
				'text' => '<p>Test</p>'
			],
			'type' => 'text',
		];

		$this->assertSame($expected, $this->schema->fallback('Test'));
	}

	public function testFallbackForDomElement(): void
	{
		$dom = new Dom('<p><b>Bold</b> <i>Italic</i></p>');
		$p        = $dom->query('//p')[0];
		$el       = new Element($p, [
			['tag' => 'b'],
			['tag' => 'i'],
			['tag' => 'p'],
		]);
		$fallback = $this->schema->fallback($el);

		$expected = [
			'content' => [
				'text' => '<p><b>Bold</b> <i>Italic</i></p>',
			],
			'type' => 'text'
		];

		$this->assertSame($expected, $fallback);
	}

	public function testFallbackForDomElementWithParagraphs(): void
	{
		$dom = new Dom('<div><p>A</p><p>B</p></div>');
		$p        = $dom->query('//div')[0];
		$el       = new Element($p, [
			['tag' => 'b'],
			['tag' => 'i'],
			['tag' => 'p'],
		]);
		$fallback = $this->schema->fallback($el);

		$expected = [
			'content' => [
				'text' => '<p>A</p><p>B</p>',
			],
			'type' => 'text'
		];

		$this->assertSame($expected, $fallback);
	}

	public function testFallbackForEmptyContent(): void
	{
		$this->assertNull($this->schema->fallback(''));
	}

	public function testFallbackForInvalidContent(): void
	{
		$this->assertNull($this->schema->fallback(''));
	}

	public function testHeading(): void
	{
		$html = <<<HTML
			<h1>
				Test
			</h1>
			HTML;

		$element  = $this->element($html, '//h1');
		$expected = [
			'content' => [
				'level' => 'h1',
				'text'  => 'Test'
			],
			'type' => 'heading',
		];

		$this->assertSame($expected, $this->schema->heading($element));
	}

	public static function headingLevelProvider(): array
	{
		return [
			['h1'], ['h2'], ['h3'], ['h4'], ['h5'], ['h6']
		];
	}

	#[DataProvider('headingLevelProvider')]
	public function testHeadingLevel($level): void
	{
		$html = <<<HTML
			<$level>
				Test
			</$level>
			HTML;

		$element  = $this->element($html, '//' . $level);
		$expected = [
			'content' => [
				'level' => $level,
				'text'  => 'Test'
			],
			'type' => 'heading',
		];

		$this->assertSame($expected, $this->schema->heading($element));
	}

	public function testHeadingId(): void
	{
		$html = <<<HTML
			<h1 id="test">
				Test
			</h1>
			HTML;

		$element  = $this->element($html, '//h1');
		$expected = [
			'content' => [
				'id'    => 'test',
				'level' => 'h1',
				'text'  => 'Test'
			],
			'type' => 'heading',
		];

		$this->assertSame($expected, $this->schema->heading($element));
	}

	public function testIframe(): void
	{
		$html = <<<HTML
			<iframe src="https://getkirby.com"></iframe>
			HTML;

		$element  = $this->element($html, '//iframe');
		$expected = [
			'content' => [
				'text' => '<iframe src="https://getkirby.com"></iframe>'
			],
			'type' => 'markdown',
		];

		$this->assertSame($expected, $this->schema->iframe($element));
	}

	public function testIframeWithVimeoVideo(): void
	{
		$html = <<<HTML
			<iframe src="https://player.vimeo.com/video/1"></iframe>
			HTML;

		$element  = $this->element($html, '//iframe');
		$expected = [
			'content' => [
				'caption' => null,
				'url'     => 'https://vimeo.com/1'
			],
			'type' => 'video',
		];

		$this->assertSame($expected, $this->schema->iframe($element));
	}

	public function testIframeWithYoutubeVideo(): void
	{
		$html = <<<HTML
			<iframe src="https://youtube.com/embed/1"></iframe>
			HTML;

		$element  = $this->element($html, '//iframe');
		$expected = [
			'content' => [
				'caption' => null,
				'url'     => 'https://youtube.com/watch?v=1'
			],
			'type' => 'video',
		];

		$this->assertSame($expected, $this->schema->iframe($element));
	}

	public function testIframeWithYoutubeNoCookieVideo(): void
	{
		$html = <<<HTML
			<iframe src="https://youtube-nocookie.com/embed/1"></iframe>
			HTML;

		$element  = $this->element($html, '//iframe');
		$expected = [
			'content' => [
				'caption' => null,
				'url'     => 'https://youtube.com/watch?v=1'
			],
			'type' => 'video',
		];

		$this->assertSame($expected, $this->schema->iframe($element));
	}

	public function testIframeWithCaption(): void
	{
		$html = <<<HTML
			<figure>
				<iframe src="https://youtube.com/embed/1"></iframe>
				<figcaption>Test</figcaption>
			</figure>
			HTML;

		$element  = $this->element($html, '//iframe');
		$expected = [
			'content' => [
				'caption' => 'Test',
				'url'     => 'https://youtube.com/watch?v=1'
			],
			'type' => 'video',
		];

		$this->assertSame($expected, $this->schema->iframe($element));
	}

	public function testIframeWithCaptionAndMarks(): void
	{
		$html = <<<HTML
			<figure>
				<iframe src="https://youtube.com/embed/1"></iframe>
				<figcaption><b>Bold</b><i>Italic</i></figcaption>
			</figure>
			HTML;

		$element  = $this->element($html, '//iframe');
		$expected = [
			'content' => [
				'caption' => '<b>Bold</b><i>Italic</i>',
				'url'     => 'https://youtube.com/watch?v=1'
			],
			'type' => 'video',
		];

		$this->assertSame($expected, $this->schema->iframe($element));
	}

	public function testImg(): void
	{
		$html = <<<HTML
			<img src="https://getkirby.com/image.jpg">
			HTML;

		$element  = $this->element($html, '//img');
		$expected = [
			'content' => [
				'alt'      => null,
				'caption'  => null,
				'link'     => null,
				'location' => 'web',
				'src'      => 'https://getkirby.com/image.jpg'
			],
			'type' => 'image',
		];

		$this->assertSame($expected, $this->schema->img($element));
	}

	public function testImgWithAlt(): void
	{
		$html = <<<HTML
			<img src="https://getkirby.com/image.jpg" alt="Test">
			HTML;

		$element  = $this->element($html, '//img');
		$expected = [
			'content' => [
				'alt'      => 'Test',
				'caption'  => null,
				'link'     => null,
				'location' => 'web',
				'src'      => 'https://getkirby.com/image.jpg'
			],
			'type' => 'image',
		];

		$this->assertSame($expected, $this->schema->img($element));
	}

	public function testImgWithLink(): void
	{
		$html = <<<HTML
			<a href="https://getkirby.com">
				<img src="https://getkirby.com/image.jpg" alt="Test">
			</a>
			HTML;

		$element  = $this->element($html, '//img');
		$expected = [
			'content' => [
				'alt'      => 'Test',
				'caption'  => null,
				'link'     => 'https://getkirby.com',
				'location' => 'web',
				'src'      => 'https://getkirby.com/image.jpg'
			],
			'type' => 'image',
		];

		$this->assertSame($expected, $this->schema->img($element));
	}

	public function testImgWithCaption(): void
	{
		$html = <<<HTML
			<figure>
				<img src="https://getkirby.com/image.jpg" alt="Test">
				<figcaption>Test</figcaption>
			</figure>
			HTML;

		$element  = $this->element($html, '//img');
		$expected = [
			'content' => [
				'alt'      => 'Test',
				'caption'  => 'Test',
				'link'     => null,
				'location' => 'web',
				'src'      => 'https://getkirby.com/image.jpg'
			],
			'type' => 'image',
		];

		$this->assertSame($expected, $this->schema->img($element));
	}

	public function testImgWithLinkAndCaption(): void
	{
		$html = <<<HTML
			<figure>
				<a href="https://getkirby.com">
					<img src="https://getkirby.com/image.jpg" alt="Test">
					<figcaption>Test</figcaption>
				</a>
			</figure>
			HTML;

		$element  = $this->element($html, '//img');
		$expected = [
			'content' => [
				'alt'      => 'Test',
				'caption'  => 'Test',
				'link'     => 'https://getkirby.com',
				'location' => 'web',
				'src'      => 'https://getkirby.com/image.jpg'
			],
			'type' => 'image',
		];

		$this->assertSame($expected, $this->schema->img($element));
	}

	public function testList(): void
	{
		$html = <<<HTML
			<ul>
				<li>A</li>
				<li>B</li>
				<li>C</li>
			</ul>
			HTML;

		$element  = $this->element($html, '//ul');
		$expected = '<ul><li>A</li><li>B</li><li>C</li></ul>';

		$this->assertSame($expected, $this->schema->list($element));
	}

	public function testListWithMarks(): void
	{
		$html = <<<HTML
			<ul>
				<li><b>Bold</b><i>Italic</i></li>
			</ul>
			HTML;

		$element  = $this->element($html, '//ul');
		$expected = '<ul><li><b>Bold</b><i>Italic</i></li></ul>';

		$this->assertSame($expected, $this->schema->list($element));
	}

	public function testListNested(): void
	{
		$html = <<<HTML
			<ul>
				<li>A</li>
				<li>
					<ol>
						<li>1</li>
						<li>2</li>
						<li>3</li>
					</ol>
				</li>
				<li>C</li>
			</ul>
			HTML;

		$element  = $this->element($html, '//ul');
		$expected = '<ul><li>A</li><li><ol><li>1</li><li>2</li><li>3</li></ol></li><li>C</li></ul>';

		$this->assertSame($expected, $this->schema->list($element));
	}

	public function testPre(): void
	{
		$html = <<<HTML
			<pre>Code</pre>
			HTML;

		$element  = $this->element($html, '//pre');
		$expected = [
			'content' => [
				'code'     => 'Code',
				'language' => 'text'
			],
			'type' => 'code',
		];

		$this->assertSame($expected, $this->schema->pre($element));
	}

	public function testPreWithCode(): void
	{
		$html = <<<HTML
			<pre><code>Code</code></pre>
			HTML;

		$element  = $this->element($html, '//pre');
		$expected = [
			'content' => [
				'code'     => 'Code',
				'language' => 'text'
			],
			'type' => 'code',
		];

		$this->assertSame($expected, $this->schema->pre($element));
	}

	public function testPreWithLanguage(): void
	{
		$html = <<<HTML
			<pre><code class="language-php">Code</code></pre>
			HTML;

		$element  = $this->element($html, '//pre');
		$expected = [
			'content' => [
				'code'     => 'Code',
				'language' => 'php'
			],
			'type' => 'code',
		];

		$this->assertSame($expected, $this->schema->pre($element));
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

	public function testTable(): void
	{
		$html = <<<HTML
			<table></table>
			HTML;

		$element  = $this->element($html, '//table');
		$expected = [
			'content' => [
				'text' => '<table></table>',
			],
			'type' => 'markdown',
		];

		$this->assertSame($expected, $this->schema->table($element));
	}
}
