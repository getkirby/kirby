<?php

namespace Kirby\Sane;

use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @todo Add more tests from DOMPurify and the other test classes
 */
#[CoversClass(Html::class)]
class HtmlTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Sane.Html';

	protected static string $type = 'html';

	#[DataProvider('allowedProvider')]
	public function testAllowed(string $file): void
	{
		$fixture = $this->fixture($file);

		Html::validateFile($fixture);

		$sanitized = Html::sanitize(file_get_contents($fixture));
		$this->assertStringEqualsFile($fixture, $sanitized);
	}

	public static function allowedProvider(): array
	{
		return static::fixtureList('allowed', 'html');
	}

	public function testDisallowedExternalFile(): void
	{
		$fixture   = $this->fixture('disallowed/link-subfolder.html');
		$sanitized = $this->fixture('sanitized/link-subfolder.html');

		$html = Html::sanitize(file_get_contents($fixture));
		$this->assertStringEqualsFile($fixture, $html);

		$html = Html::sanitize(file_get_contents($fixture), isExternal: true);
		$this->assertStringEqualsFile($sanitized, $html);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL points outside of the site index URL');
		Html::validateFile($fixture);
	}
}
