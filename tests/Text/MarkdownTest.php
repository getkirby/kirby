<?php

namespace Kirby\Text;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Markdown::class)]
class MarkdownTest extends TestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures/markdown';

	public const array PROFILES = [
		'features' => [['breaks' => true,  'safe' => false], false],
		'safe'     => [['breaks' => true,  'safe' => true],  false],
		'inline'   => [['breaks' => true,  'safe' => false], true],
	];

	/**
	 * The CommonMark examples where Kirby knowingly diverges from the spec's
	 * expected HTML, keyed by the example's number in `commonmark-spec.txt`:
	 *
	 * - Bare-URL autolinking (602, 608, 611): a deliberate Kirby/GFM feature
	 *   CommonMark lacks.
	 * - Lazy-continuation re-parse (93, 312): the deferred-content model stores
	 *   a container's lines as raw text and re-parses them, so a lazy setext
	 *   underline / indented code is re-interpreted instead of staying
	 *   paragraph text. A known gap.
	 */
	public const array DIVERGENCES = [
		602 => '<p>&lt;<a href="https://foo.bar/baz">https://foo.bar/baz</a> bim&gt;</p>',
		608 => '<p>&lt; <a href="https://foo.bar">https://foo.bar</a> &gt;</p>',
		611 => '<p><a href="https://example.com">https://example.com</a></p>',
		93  => "<blockquote>\n<p>foo\nbar</p>\n</blockquote>\n<p>===</p>",
		312 => "<ul>\n<li>a</li>\n<li>b</li>\n<li>c</li>\n<li>d</li>\n</ul>\n<pre><code>- e\n</code></pre>",
	];

	public function testDefaults(): void
	{
		$markdown = new Markdown();

		$this->assertSame([
			'breaks' => true,
			'safe'   => false,
		], $markdown->defaults());
	}

	public function testParseSafeModeDisabled(): void
	{
		$markdown = new Markdown([
			'safe' => false
		]);

		$this->assertSame('<div>Custom HTML</div>', $markdown->parse('<div>Custom HTML</div>'));
	}

	public function testParseSafeModeEnabled(): void
	{
		$markdown = new Markdown([
			'safe' => true
		]);

		$this->assertSame('<p>&lt;div&gt;Custom HTML&lt;/div&gt;</p>', $markdown->parse('<div>Custom HTML</div>'));
	}

	public static function commonmarkProvider(): array
	{
		$spec = file_get_contents(static::FIXTURES . '/commonmark-spec.txt');

		preg_match_all(
			'/^`{10,} +example.*?\n(.*?)^\.\n(.*?)^`{10,}\s*$/ms',
			$spec,
			$matches,
			PREG_SET_ORDER
		);

		$cases = [];

		foreach ($matches as $index => $match) {
			$number = $index + 1;

			$cases['example ' . $number] = [
				$number,
				str_replace('→', "\t", $match[1]),
				rtrim(str_replace('→', "\t", $match[2]), "\n")
			];
		}

		return $cases;
	}

	#[DataProvider('commonmarkProvider')]
	public function testParseCommonMark(int $number, string $input, string $expected): void
	{
		$html = rtrim(
			(new Markdown(['breaks' => false, 'safe' => false]))->parse($input, false),
			"\n"
		);

		if (isset(static::DIVERGENCES[$number]) === true) {
			$this->assertSame(
				static::DIVERGENCES[$number],
				$html,
				'CommonMark example ' . $number . ' (documented divergence)'
			);
			return;
		}

		$this->assertSame(
			$expected,
			$html,
			'CommonMark example ' . $number
		);
	}

	public static function fixturesProvider(): array
	{
		$cases = [];

		foreach (array_keys(static::PROFILES) as $profile) {
			foreach (glob(static::FIXTURES . '/' . $profile . '/*.md') as $file) {
				$name         = $profile . '/' . basename($file, '.md');
				$cases[$name] = [$name];
			}
		}

		return $cases;
	}

	#[DataProvider('fixturesProvider')]
	public function testParseFixture(string $name): void
	{
		[$profile]          = explode('/', $name);
		[$options, $inline] = static::PROFILES[$profile];

		$input    = file_get_contents(static::FIXTURES . '/' . $name . '.md');
		$expected = file_get_contents(static::FIXTURES . '/' . $name . '.html');

		$this->assertSame(
			$expected,
			(new Markdown($options))->parse($input, $inline),
			'Markdown fixture diverged: ' . $name
		);
	}

	public function testParseReusedInstanceDoesNotLeakFootnoteState(): void
	{
		// a single instance is reused across fields in a request, so parsing
		// must not carry footnote numbering (or any per-document state) over
		$markdown = new Markdown();
		$input    = "A ref.[^1]\n\n[^1]: note one";

		$first  = $markdown->parse($input);
		$second = $markdown->parse($input);

		$this->assertSame($first, $second);
		$this->assertSame($first, (new Markdown())->parse($input));
		$this->assertStringContainsString('class="footnote-ref">1</a>', $second);
	}
}
