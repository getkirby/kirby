<?php

namespace Kirby\Text;

use FilesystemIterator;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

#[CoversClass(Markdown::class)]
class MarkdownTest extends TestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures/markdown';

	/**
	 * Each maps a profile name to the Markdown constructor options
	 * and the $inline argument passed to parse()
	 */
	public const array PROFILES = [
		// name     => [[breaks, safe], inline]
		'default'  => [['breaks' => true,  'safe' => false], false],
		'nobreaks' => [['breaks' => false, 'safe' => false], false],
		'safe'     => [['breaks' => true,  'safe' => true], false],
		'inline'   => [['breaks' => true,  'safe' => false], true],
	];

	/**
	 * Inputs where the pipeline throws a fatal error instead of producing
	 * HTML, so there is nothing to snapshot. Raw HTML blocks now pass through
	 * verbatim unless they carry `markdown="1"`, so none remain.
	 */
	public const array KNOWN_CRASHES = [];

	public function testDefaults(): void
	{
		$markdown = new Markdown();

		$this->assertSame([
			'breaks' => true,
			'safe'   => false,
		], $markdown->defaults());
	}

	public function testWithOptions(): void
	{
		$markdown = new Markdown([
			'breaks' => false
		]);

		$this->assertInstanceOf(Markdown::class, $markdown);
	}

	public function testSafeModeDisabled(): void
	{
		$markdown = new Markdown([
			'safe' => false
		]);

		$this->assertSame('<div>Custom HTML</div>', $markdown->parse('<div>Custom HTML</div>'));
	}

	public function testSafeModeEnabled(): void
	{
		$markdown = new Markdown([
			'safe' => true
		]);

		$this->assertSame('<p>&lt;div&gt;Custom HTML&lt;/div&gt;</p>', $markdown->parse('<div>Custom HTML</div>'));
	}

	public function testReusedInstanceDoesNotLeakFootnoteState(): void
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

	public static function snapshotsProvider(): array
	{
		$root  = static::FIXTURES . '/inputs';
		$cut   = strlen($root) + 1;
		$cases = [];

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
		);

		foreach ($iterator as $file) {
			if ($file->getExtension() !== 'md') {
				continue;
			}

			$path = str_replace('\\', '/', substr($file->getPathname(), $cut));
			$cases[$path] = [$path];
		}

		ksort($cases);

		return $cases;
	}

	#[DataProvider('snapshotsProvider')]
	public function testParse(string $path): void
	{
		$input = file_get_contents(static::FIXTURES . '/inputs/' . $path);
		$stem  = substr($path, 0, -3); // strip ".md"

		$expected = [];
		$actual   = [];

		foreach (static::PROFILES as $profile => [$options, $inline]) {
			if (in_array($profile, static::KNOWN_CRASHES[$path] ?? [], true) === true) {
				continue;
			}

			$snapshot = static::FIXTURES . '/snapshots/' . $profile . '/' . $stem . '.html';
			$html     = (new Markdown($options))->parse($input, $inline);

			$this->assertFileExists(
				$snapshot,
				'Missing snapshot for profile "' . $profile . '": ' . $path
			);

			$expected[$profile] = file_get_contents($snapshot);
			$actual[$profile]   = $html;
		}

		$this->assertSame(
			$expected,
			$actual,
			'Markdown output diverged from snapshot for input: ' . $path
		);
	}
}
