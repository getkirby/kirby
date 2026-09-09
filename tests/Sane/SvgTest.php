<?php

namespace Kirby\Sane;

use Kirby\Exception\InvalidArgumentException;

/**
 * @covers \Kirby\Sane\Svg
 */
class SvgTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Sane.Svg';

	protected static $type = 'svg';

	/**
	 * @dataProvider allowedProvider
	 */
	public function testAllowed(string $file)
	{
		$fixture = $this->fixture($file);
		$cleaned = $this->fixture(str_replace('allowed', 'cleaned', $file));

		$this->assertNull(Svg::validateFile($fixture));

		$sanitized = Svg::sanitize(file_get_contents($fixture));
		$this->assertStringEqualsFile(is_file($cleaned) ? $cleaned : $fixture, $sanitized);
	}

	public static function allowedProvider()
	{
		return static::fixtureList('allowed', 'svg');
	}

	public function testAllowedAriaAttr()
	{
		$fixture = '<svg><path aria-label="Test" /></svg>';
		$cleaned = '<svg><path aria-label="Test"/></svg>';

		$this->assertNull(Svg::validate($fixture));
		$this->assertSame($cleaned, Svg::sanitize($fixture));
	}

	public function testAllowedAriaData()
	{
		$fixture = '<svg><path data-color="test" /></svg>';
		$cleaned = '<svg><path data-color="test"/></svg>';

		$this->assertNull(Svg::validate($fixture));
		$this->assertSame($cleaned, Svg::sanitize($fixture));
	}

	public function testAllowedCharacterData()
	{
		// comments that cannot close themselves early, and CDATA sections
		// in foreign content, never re-parse as live HTML and are kept
		$fixtures = [
			// generator comment at the root
			'<svg><!-- Generator: Sketch --><rect/></svg>',
			// commented-out element inside foreign content
			'<svg><defs><!--<linearGradient id="x"></linearGradient>--></defs></svg>',
			// CDATA-wrapped CSS, including a child combinator `>`
			'<svg><style><![CDATA[.a > .b { fill: red }]]></style></svg>',
			// `<![CDATA[` keys on the namespace of its parent, so it stays
			// a real CDATA section in `<desc>`/`<title>` just as elsewhere
			'<svg><desc><![CDATA[><img src=x onerror=alert(1)>]]></desc></svg>',
			// SVG `<style>` is a foreign element, not an HTML raw text
			// element, so a smuggled `</style>` cannot break out of it
			'<svg><style><![CDATA[</style><img src=x onerror=alert(1)>]]></style></svg>',
			// `<svg>` re-enters foreign content below an integration point
			'<svg><desc><svg><![CDATA[><img src=x onerror=alert(1)>]]></svg></desc></svg>',
			// `<font>` only breaks out of foreign content when it carries
			// one of the HTML font attributes
			'<svg><font><![CDATA[><img src=x onerror=alert(1)>]]></font></svg>',
		];

		foreach ($fixtures as $fixture) {
			$this->assertNull(Svg::validate($fixture));
			$this->assertSame($fixture, Svg::sanitize($fixture));
		}
	}

	/**
	 * @dataProvider invalidProvider
	 */
	public function testInvalid(string $file)
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The markup could not be parsed');

		Svg::validateFile($this->fixture($file));
	}

	public static function invalidProvider()
	{
		return static::fixtureList('invalid', 'svg');
	}

	public function testDisallowedJavascriptUrl()
	{
		$fixture   = "<svg>\n<a href='javascript:alert(1)'><path /></a>\n</svg>";
		$sanitized = "<svg>\n<a><path/></a>\n</svg>";

		$this->assertSame($sanitized, Svg::sanitize($fixture));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL is not allowed in attribute "href" (line 2): Unknown URL type');
		Svg::validate($fixture);
	}

	public function testDisallowedJavascriptUrlWithUnicodeLS()
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

	public function testDisallowedXlinkAttack()
	{
		$fixture   = $this->fixture('disallowed/xlink-attack.svg');
		$sanitized = $this->fixture('sanitized/xlink-attack.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL is not allowed in attribute "xlink:href" (line 2): Unknown URL type');
		Svg::validateFile($fixture);
	}

	public function testDisallowedExternalFile()
	{
		$fixture   = $this->fixture('disallowed/xlink-subfolder.svg');
		$sanitized = $this->fixture('sanitized/xlink-subfolder.svg');

		$this->assertStringEqualsFile($fixture, Svg::sanitize(file_get_contents($fixture)));
		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture), isExternal: true));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL points outside of the site index URL');
		Svg::validateFile($fixture);
	}

	public function testDisallowedExternalXmlns1()
	{
		$fixture   = $this->fixture('disallowed/external-xmlns-1.svg');
		$sanitized = $this->fixture('sanitized/external-xmlns-1.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The namespace "https://malicious.com/script.php" is not allowed (around line 1)');
		Svg::validateFile($fixture);
	}

	public function testDisallowedExternalXmlns2()
	{
		$fixture   = $this->fixture('disallowed/external-xmlns-2.svg');
		$sanitized = $this->fixture('sanitized/external-xmlns-2.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The namespace "https://malicious.com/script.php" is not allowed (around line 1)');
		Svg::validateFile($fixture);
	}

	public function testDisallowedDataUriSvg1()
	{
		$fixture   = $this->fixture('disallowed/data-uri-svg-1.svg');
		$sanitized = $this->fixture('sanitized/data-uri-svg-1.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL is not allowed in attribute "style" (line 7)');
		Svg::validateFile($fixture);
	}

	public function testDisallowedDataUriSvg2()
	{
		$fixture   = $this->fixture('disallowed/data-uri-svg-2.svg');
		$sanitized = $this->fixture('sanitized/data-uri-svg-2.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL is not allowed in attribute "filter" (line 7)');
		Svg::validateFile($fixture);
	}

	public function testDisallowedExternalSource1()
	{
		$fixture   = $this->fixture('disallowed/external-source-1.svg');
		$sanitized = $this->fixture('sanitized/external-source-1.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL is not allowed in attribute "style" (line 2)');
		Svg::validateFile($fixture);
	}

	public function testDisallowedExternalSource2()
	{
		$fixture   = $this->fixture('disallowed/external-source-2.svg');
		$sanitized = $this->fixture('sanitized/external-source-2.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL is not allowed in attribute "href" (line 2)');
		Svg::validateFile($fixture);
	}

	public function testDisallowedProtocolRelativeBackslash(): void
	{
		$fixture   = $this->fixture('disallowed/protocol-relative-backslash.svg');
		$sanitized = $this->fixture('sanitized/protocol-relative-backslash.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL is not allowed in attribute "href" (line 2): Protocol-relative URLs are not allowed');
		Svg::validateFile($fixture);
	}

	public function testDisallowedProtocolRelativeWhitespace(): void
	{
		$fixture   = $this->fixture('disallowed/protocol-relative-whitespace.svg');
		$sanitized = $this->fixture('sanitized/protocol-relative-whitespace.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL is not allowed in attribute "href" (line 2): Protocol-relative URLs are not allowed');
		Svg::validateFile($fixture);
	}

	public function testDisallowedOnclickAttr()
	{
		$fixture   = "<svg>\n<path onclick='alert(1)' />\n</svg>";
		$sanitized = "<svg>\n<path/>\n</svg>";

		$this->assertSame($sanitized, Svg::sanitize($fixture));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The "onclick" attribute (line 2) is not allowed');
		Svg::validate($fixture);
	}

	public function testDisallowedOnloadAttr()
	{
		$fixture   = '<svg onload="alert(1)"></svg>';
		$sanitized = '<svg/>';

		$this->assertSame($sanitized, Svg::sanitize($fixture));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The "onload" attribute (line 1) is not allowed');
		Svg::validate($fixture);
	}

	public function testDisallowedUseAttack1()
	{
		$fixture   = $this->fixture('disallowed/use-attack-1.svg');
		$sanitized = $this->fixture('sanitized/use-attack-1.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Nested "use" elements are not allowed (used in line 14)');
		Svg::validateFile($fixture);
	}

	public function testDisallowedUseAttack2()
	{
		$fixture   = $this->fixture('disallowed/use-attack-2.svg');
		$sanitized = $this->fixture('sanitized/use-attack-2.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Nested "use" elements are not allowed (used in line 18)');
		Svg::validateFile($fixture);
	}

	public function testDisallowedUseAttack3()
	{
		$fixture   = $this->fixture('disallowed/use-attack-3.svg');
		$sanitized = $this->fixture('sanitized/use-attack-3.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Nested "use" elements are not allowed (used in line 18)');
		Svg::validateFile($fixture);
	}

	public function testDisallowedUseAttackWhitespace(): void
	{
		$fixture   = $this->fixture('disallowed/use-attack-whitespace.svg');
		$sanitized = $this->fixture('sanitized/use-attack-whitespace.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Nested "use" elements are not allowed (used in line 14)');
		Svg::validateFile($fixture);
	}

	public function testDisallowedDoctypeExternal1()
	{
		$fixture   = $this->fixture('disallowed/doctype-external-1.svg');
		$sanitized = $this->fixture('sanitized/doctype-external-1.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The doctype must not reference external files');
		Svg::validateFile($fixture);
	}

	public function testDisallowedDoctypeExternal2()
	{
		$fixture   = $this->fixture('disallowed/doctype-external-2.svg');
		$sanitized = $this->fixture('sanitized/doctype-external-2.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The doctype must not reference external files');
		Svg::validateFile($fixture);
	}

	public function testDisallowedDoctypeEntityAttack()
	{
		$fixture   = $this->fixture('disallowed/doctype-entity-attack.svg');
		$sanitized = $this->fixture('sanitized/doctype-entity-attack.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The doctype must not define a subset');
		Svg::validateFile($fixture);
	}

	public function testDisallowedDoctypeWrong()
	{
		$fixture   = $this->fixture('disallowed/doctype-wrong.svg');
		$sanitized = $this->fixture('sanitized/doctype-wrong.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid doctype');
		Svg::validateFile($fixture);
	}

	public function testDisallowedCaseSensitive()
	{
		$fixture   = "<svg>\n<Text x='0' y='20'>Hello</Text>\n</svg>";
		$sanitized = "<svg>\n\n</svg>";

		$this->assertSame($sanitized, Svg::sanitize($fixture));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The "Text" element (line 2) is not allowed');
		Svg::validate($fixture);
	}

	public function testDisallowedForeignobject()
	{
		$fixture   = '<svg><foreignobject><iframe onload="alert(1)" /></foreignobject></svg>';
		$sanitized = '<svg/>';

		$this->assertSame($sanitized, Svg::sanitize($fixture));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The "foreignobject" element (line 1) is not allowed');
		Svg::validate($fixture);
	}

	public function testDisallowedSet()
	{
		$fixture   = $this->fixture('disallowed/set.svg');
		$sanitized = $this->fixture('sanitized/set.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The "set" element (line 7) is not allowed');
		Svg::validateFile($fixture);
	}

	public function testDisallowedScript()
	{
		$fixture   = '<svg><script>alert(1)</script></svg>';
		$sanitized = '<svg/>';

		$this->assertSame($sanitized, Svg::sanitize($fixture));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The "script" element (line 1) is not allowed');
		Svg::validate($fixture);
	}

	public function testDisallowedBlockquote()
	{
		$fixture   = '<svg><blockquote>SVGs are SVGs are SVGs</blockquote></svg>';
		$sanitized = '<svg/>';

		$this->assertSame($sanitized, Svg::sanitize($fixture));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The "blockquote" element (line 1) is not allowed');
		Svg::validate($fixture);
	}

	public function testDisallowedStyleImportEscaped(): void
	{
		$fixture   = $this->fixture('disallowed/style-import-escaped.svg');
		$sanitized = $this->fixture('sanitized/style-import-escaped.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL is not allowed in the "style" element (around line 3)');
		Svg::validateFile($fixture);
	}

	public function testDisallowedStyleImportExternal()
	{
		$fixture   = $this->fixture('disallowed/style-import-external.svg');
		$sanitized = $this->fixture('sanitized/style-import-external.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL is not allowed in the "style" element (around line 3)');
		Svg::validateFile($fixture);
	}

	public function testDisallowedStyleUrlExternal()
	{
		$fixture   = $this->fixture('disallowed/style-url-external.svg');
		$sanitized = $this->fixture('sanitized/style-url-external.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The URL is not allowed in the "style" element (around line 3)');
		Svg::validateFile($fixture);
	}

	public function testDisallowedStylesheet()
	{
		$fixture   = $this->fixture('disallowed/stylesheet.svg');
		$sanitized = $this->fixture('sanitized/stylesheet.svg');

		$this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The "xml-stylesheet" processing instruction (line 6) is not allowed');
		Svg::validateFile($fixture);
	}

	public function testDisallowedMutationXssTitleComment()
	{
		// `<!-->` is an abrupt closing of an empty comment for the HTML
		// tokenizer, so the `<img onerror>` behind it re-parses as live
		$fixture   = '<svg><title><!--><img src=x onerror=alert(1)>--></title></svg>';
		$sanitized = '<svg><title/></svg>';

		$this->assertSame($sanitized, Svg::sanitize($fixture));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The comment (line 1) is not allowed');
		Svg::validate($fixture);
	}

	public function testDisallowedMutationXssTopLevelComment()
	{
		// the same abrupt close at the top level, where the exposed
		// `<img onerror>` lands directly in the HTML namespace
		$fixture   = '<!--><img src=x onerror=alert(1)>--><svg></svg>';
		$sanitized = '<svg/>';

		$this->assertSame($sanitized, Svg::sanitize($fixture));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The comment (line 1) is not allowed');
		Svg::validate($fixture);
	}

	public function testDisallowedMutationXssForeignContentComment()
	{
		// the abrupt close happens in the tokenizer, before any insertion
		// mode applies, and `<img>` breaks out of SVG foreign content
		$fixture   = '<svg><g><!--><img src=x onerror=alert(1)>--></g></svg>';
		$sanitized = '<svg><g/></svg>';

		$this->assertSame($sanitized, Svg::sanitize($fixture));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The comment (line 1) is not allowed');
		Svg::validate($fixture);
	}

	public function testDisallowedMutationXssForeignContentCommentDash()
	{
		// `<!--->` closes the empty comment just like `<!-->` does
		$fixture   = '<svg><g><!---><img src=x onerror=alert(1)>--></g></svg>';
		$sanitized = '<svg><g/></svg>';

		$this->assertSame($sanitized, Svg::sanitize($fixture));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The comment (line 1) is not allowed');
		Svg::validate($fixture);
	}

	public function testDisallowedMutationXssRootComment()
	{
		// the `<svg>` root is foreign content itself, so the breakout
		// needs no integration point anywhere in the ancestor chain
		$fixture   = '<svg><!--><img src=x onerror=alert(1)>--></svg>';
		$sanitized = '<svg/>';

		$this->assertSame($sanitized, Svg::sanitize($fixture));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The comment (line 1) is not allowed');
		Svg::validate($fixture);
	}

	public function testDisallowedMutationXssStyleComment()
	{
		// `<style>` inside `<svg>` is a foreign element, not an HTML raw
		// text element, so the abrupt close goes live in there as well
		$fixture   = '<svg><style><!--><img src=x onerror=alert(1)>--></style></svg>';
		$sanitized = '<svg><style/></svg>';

		$this->assertSame($sanitized, Svg::sanitize($fixture));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The comment (line 1) is not allowed');
		Svg::validate($fixture);
	}

	public function testDisallowedMutationXssIntegrationPointCdata()
	{
		// `<desc>` is an HTML integration point, so its child elements are
		// created in the HTML namespace, where `<![CDATA[` is only a bogus
		// comment that ends at the first `>` and exposes the rest
		$fixture   = '<svg><desc><g><![CDATA[><img src=x onerror=alert(1)>]]></g></desc></svg>';
		$sanitized = '<svg><desc><g>&gt;&lt;img src=x onerror=alert(1)&gt;</g></desc></svg>';

		$this->assertSame($sanitized, Svg::sanitize($fixture));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The CDATA section (line 1) is not allowed');
		Svg::validate($fixture);
	}

	public function testDisallowedMutationXssIntegrationPointCdataTitle()
	{
		// `<title>` is an HTML integration point just like `<desc>`,
		// at any depth below it
		$fixture   = '<svg><title><g><g><![CDATA[><img src=x onerror=alert(1)>]]></g></g></title></svg>';
		$sanitized = '<svg><title><g><g>&gt;&lt;img src=x onerror=alert(1)&gt;</g></g></title></svg>';

		$this->assertSame($sanitized, Svg::sanitize($fixture));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The CDATA section (line 1) is not allowed');
		Svg::validate($fixture);
	}

	public function testDisallowedMutationXssIntegrationPointStyleCdata()
	{
		// below an integration point `<style>` is an HTML raw text element
		// again, which a smuggled `</style>` breaks out of
		$fixture   = '<svg><desc><style><![CDATA[</style><img src=x onerror=alert(1)>]]></style></desc></svg>';
		$sanitized = '<svg><desc><style>&lt;/style&gt;&lt;img src=x onerror=alert(1)&gt;</style></desc></svg>';

		$this->assertSame($sanitized, Svg::sanitize($fixture));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The CDATA section (line 1) is not allowed');
		Svg::validate($fixture);
	}

	public function testDisallowedMutationXssIntegrationPointStyleComment()
	{
		// the same raw text element also turns a comment into plain text,
		// so only the closing tag can break out of it
		$fixture   = '<svg><desc><style><!--</style><img src=x onerror=alert(1)>--></style></desc></svg>';
		$sanitized = '<svg><desc><style/></desc></svg>';

		$this->assertSame($sanitized, Svg::sanitize($fixture));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The comment (line 1) is not allowed');
		Svg::validate($fixture);
	}

	public function testDisallowedMutationXssBreakoutCdata()
	{
		// `<font>` with an HTML font attribute makes the parser leave the
		// foreign subtree entirely and continue in the HTML namespace
		$fixture   = '<svg><font color="red"><![CDATA[><img src=x onerror=alert(1)>]]></font></svg>';
		$sanitized = '<svg><font color="red">&gt;&lt;img src=x onerror=alert(1)&gt;</font></svg>';

		$this->assertSame($sanitized, Svg::sanitize($fixture));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The CDATA section (line 1) is not allowed');
		Svg::validate($fixture);
	}

	public function testDisallowedMutationXssBreakoutStyleCdata()
	{
		// after the breakout `<style>` is an HTML raw text element as well
		$fixture   = '<svg><font color="red"><style><![CDATA[</style><img src=x onerror=alert(1)>]]></style></font></svg>';
		$sanitized = '<svg><font color="red"><style>&lt;/style&gt;&lt;img src=x onerror=alert(1)&gt;</style></font></svg>';

		$this->assertSame($sanitized, Svg::sanitize($fixture));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The CDATA section (line 1) is not allowed');
		Svg::validate($fixture);
	}

	public function testDisallowedMutationXssForeignStyleComment()
	{
		// a foreign `<style>` is no raw text element, so a comment nested
		// below it is a real comment that must not close itself early
		$fixture   = '<svg><style><desc><g><!--><img src=x onerror=alert(1)>--></g></desc></style></svg>';
		$sanitized = '<svg><style><desc><g/></desc></style></svg>';

		$this->assertSame($sanitized, Svg::sanitize($fixture));

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The comment (line 1) is not allowed');
		Svg::validate($fixture);
	}

	public function testParseNonSvg()
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The file is not a SVG (got <html>)');

		Svg::validate('<html></html>');
	}
}
