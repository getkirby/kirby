<?php

namespace Kirby\Sane;

use Kirby\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Svg::class)]
class SvgTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Sane.Svg';

	protected static string $type = 'svg';

	#[DataProvider('allowedProvider')]
	public function testAllowed(string $file): void
	{
		$fixture = $this->fixture($file);
		$cleaned = $this->fixture(str_replace('allowed', 'cleaned', $file));

		Svg::validateFile($fixture);

		$sanitized = Svg::sanitize(file_get_contents($fixture));
		$this->assertStringEqualsFile(is_file($cleaned) ? $cleaned : $fixture, $sanitized);
	}

	public static function allowedProvider(): array
	{
		return static::fixtureList('allowed', 'svg');
	}

	public function testAllowedAriaAttr(): void
	{
		$fixture = '<svg><path aria-label="Test" /></svg>';
		$cleaned = '<svg><path aria-label="Test"/></svg>';

		Svg::validate($fixture);
		$this->assertSame($cleaned, Svg::sanitize($fixture));
	}

	public function testAllowedAriaData(): void
	{
		$fixture = '<svg><path data-color="test" /></svg>';
		$cleaned = '<svg><path data-color="test"/></svg>';

		Svg::validate($fixture);
		$this->assertSame($cleaned, Svg::sanitize($fixture));
	}

	#[DataProvider('invalidProvider')]
	public function testInvalid(string $file): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The markup could not be parsed');

		Svg::validateFile($this->fixture($file));
	}

	public static function invalidProvider(): array
	{
		return static::fixtureList('invalid', 'svg');
	}

	public function testDisallowedJavascriptUrl(): void
	{
		$fixture   = "<svg>\n<a href='javascript:alert(1)'><path /></a>\n</svg>";
		$sanitized = "<svg>\n<a><path/></a>\n</svg>";

		$this->assertSame($sanitized, Svg::sanitize($fixture));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL is not allowed in attribute "href" (line 2): Unknown URL type');
		Svg::validate($fixture);
	}

	public function testDisallowedJavascriptUrlWithUnicodeLS(): void
	{
		/**
		 * Test fixture inspired by DOMPurify
		 * @link https://github.com/cure53/DOMPurify
		 * @copyright 2015 Mario Heiderich
		 * @license https://www.apache.org/licenses/LICENSE-2.0
		 */
		$fixture = '<svg>123<a href="\u2028javascript:alert(1)">I am a dolphin!</a></svg>';
		$sanitized = '<svg>123<a>I am a dolphin!</a></svg>';

		$this->assertSame($sanitized, Svg::sanitize($fixture));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL is not allowed in attribute "href" (line 1): Unknown URL type');
		Svg::validate($fixture);
	}

	public function testDisallowedXlinkAttack(): void
	{
		$fixture   = $this->fixture('disallowed/xlink-attack.svg');
		$sanitized = $this->fixture('sanitized/xlink-attack.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL is not allowed in attribute "xlink:href" (line 2): Unknown URL type');
		Svg::validateFile($fixture);
	}

	public function testDisallowedExternalFile(): void
	{
		$fixture   = $this->fixture('disallowed/xlink-subfolder.svg');
		$sanitized = $this->fixture('sanitized/xlink-subfolder.svg');

		$this->assertStringEqualsFile($fixture, Svg::sanitize(file_get_contents($fixture)));
		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture), isExternal: true));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL points outside of the site index URL');
		Svg::validateFile($fixture);
	}

	public function testDisallowedExternalXmlns1(): void
	{
		$fixture   = $this->fixture('disallowed/external-xmlns-1.svg');
		$sanitized = $this->fixture('sanitized/external-xmlns-1.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The namespace "https://malicious.com/script.php" is not allowed (around line 1)');
		Svg::validateFile($fixture);
	}

	public function testDisallowedExternalXmlns2(): void
	{
		$fixture   = $this->fixture('disallowed/external-xmlns-2.svg');
		$sanitized = $this->fixture('sanitized/external-xmlns-2.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The namespace "https://malicious.com/script.php" is not allowed (around line 1)');
		Svg::validateFile($fixture);
	}

	public function testDisallowedDataUriSvg1(): void
	{
		$fixture   = $this->fixture('disallowed/data-uri-svg-1.svg');
		$sanitized = $this->fixture('sanitized/data-uri-svg-1.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL is not allowed in attribute "style" (line 7)');
		Svg::validateFile($fixture);
	}

	public function testDisallowedDataUriSvg2(): void
	{
		$fixture   = $this->fixture('disallowed/data-uri-svg-2.svg');
		$sanitized = $this->fixture('sanitized/data-uri-svg-2.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL is not allowed in attribute "filter" (line 7)');
		Svg::validateFile($fixture);
	}

	public function testDisallowedExternalSource1(): void
	{
		$fixture   = $this->fixture('disallowed/external-source-1.svg');
		$sanitized = $this->fixture('sanitized/external-source-1.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL is not allowed in attribute "style" (line 2)');
		Svg::validateFile($fixture);
	}

	public function testDisallowedExternalSource2(): void
	{
		$fixture   = $this->fixture('disallowed/external-source-2.svg');
		$sanitized = $this->fixture('sanitized/external-source-2.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL is not allowed in attribute "href" (line 2)');
		Svg::validateFile($fixture);
	}

	public function testDisallowedOnclickAttr(): void
	{
		$fixture   = "<svg>\n<path onclick='alert(1)' />\n</svg>";
		$sanitized = "<svg>\n<path/>\n</svg>";

		$this->assertSame($sanitized, Svg::sanitize($fixture));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The "onclick" attribute (line 2) is not allowed');
		Svg::validate($fixture);
	}

	public function testDisallowedOnloadAttr(): void
	{
		$fixture   = '<svg onload="alert(1)"></svg>';
		$sanitized = '<svg/>';

		$this->assertSame($sanitized, Svg::sanitize($fixture));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The "onload" attribute (line 1) is not allowed');
		Svg::validate($fixture);
	}

	public function testDisallowedUseAttack1(): void
	{
		$fixture   = $this->fixture('disallowed/use-attack-1.svg');
		$sanitized = $this->fixture('sanitized/use-attack-1.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Nested "use" elements are not allowed (used in line 14)');
		Svg::validateFile($fixture);
	}

	public function testDisallowedUseAttack2(): void
	{
		$fixture   = $this->fixture('disallowed/use-attack-2.svg');
		$sanitized = $this->fixture('sanitized/use-attack-2.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Nested "use" elements are not allowed (used in line 18)');
		Svg::validateFile($fixture);
	}

	public function testDisallowedUseAttack3(): void
	{
		$fixture   = $this->fixture('disallowed/use-attack-3.svg');
		$sanitized = $this->fixture('sanitized/use-attack-3.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Nested "use" elements are not allowed (used in line 18)');
		Svg::validateFile($fixture);
	}

	public function testDisallowedDoctypeExternal1(): void
	{
		$fixture   = $this->fixture('disallowed/doctype-external-1.svg');
		$sanitized = $this->fixture('sanitized/doctype-external-1.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The doctype must not reference external files');
		Svg::validateFile($fixture);
	}

	public function testDisallowedDoctypeExternal2(): void
	{
		$fixture   = $this->fixture('disallowed/doctype-external-2.svg');
		$sanitized = $this->fixture('sanitized/doctype-external-2.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The doctype must not reference external files');
		Svg::validateFile($fixture);
	}

	public function testDisallowedDoctypeEntityAttack(): void
	{
		$fixture   = $this->fixture('disallowed/doctype-entity-attack.svg');
		$sanitized = $this->fixture('sanitized/doctype-entity-attack.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The doctype must not define a subset');
		Svg::validateFile($fixture);
	}

	public function testDisallowedDoctypeWrong(): void
	{
		$fixture   = $this->fixture('disallowed/doctype-wrong.svg');
		$sanitized = $this->fixture('sanitized/doctype-wrong.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid doctype');
		Svg::validateFile($fixture);
	}

	public function testDisallowedCaseSensitive(): void
	{
		$fixture   = "<svg>\n<Text x='0' y='20'>Hello</Text>\n</svg>";
		$sanitized = "<svg>\n\n</svg>";

		$this->assertSame($sanitized, Svg::sanitize($fixture));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The "Text" element (line 2) is not allowed');
		Svg::validate($fixture);
	}

	public function testDisallowedForeignobject(): void
	{
		$fixture   = '<svg><foreignobject><iframe onload="alert(1)" /></foreignobject></svg>';
		$sanitized = '<svg/>';

		$this->assertSame($sanitized, Svg::sanitize($fixture));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The "foreignobject" element (line 1) is not allowed');
		Svg::validate($fixture);
	}

	public function testDisallowedSet(): void
	{
		$fixture   = $this->fixture('disallowed/set.svg');
		$sanitized = $this->fixture('sanitized/set.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The "set" element (line 7) is not allowed');
		Svg::validateFile($fixture);
	}

	public function testDisallowedScript(): void
	{
		$fixture   = '<svg><script>alert(1)</script></svg>';
		$sanitized = '<svg/>';

		$this->assertSame($sanitized, Svg::sanitize($fixture));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The "script" element (line 1) is not allowed');
		Svg::validate($fixture);
	}

	public function testDisallowedBlockquote(): void
	{
		$fixture   = '<svg><blockquote>SVGs are SVGs are SVGs</blockquote></svg>';
		$sanitized = '<svg/>';

		$this->assertSame($sanitized, Svg::sanitize($fixture));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The "blockquote" element (line 1) is not allowed');
		Svg::validate($fixture);
	}

	public function testDisallowedStyleUrlExternal(): void
	{
		$fixture   = $this->fixture('disallowed/style-url-external.svg');
		$sanitized = $this->fixture('sanitized/style-url-external.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL is not allowed in the "style" element (around line 3)');
		Svg::validateFile($fixture);
	}

	public function testDisallowedStylesheet(): void
	{
		$fixture   = $this->fixture('disallowed/stylesheet.svg');
		$sanitized = $this->fixture('sanitized/stylesheet.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The "xml-stylesheet" processing instruction (line 6) is not allowed');
		Svg::validateFile($fixture);
	}

	public function testParseNonSvg(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The file is not a SVG (got <html>)');

		Svg::validate('<html></html>');
	}
}
