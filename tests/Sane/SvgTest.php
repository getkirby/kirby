<?php

namespace Kirby\Sane;

/**
 * @covers \Kirby\Sane\Svg
 */
class SvgTest extends TestCase
{
    protected $type = 'svg';

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

    public function allowedProvider()
    {
        return $this->fixtureList('allowed', 'svg');
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

    /**
     * @dataProvider invalidProvider
     */
    public function testInvalid(string $file)
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The markup could not be parsed');

        Svg::validateFile($this->fixture($file));
    }

    public function invalidProvider()
    {
        return $this->fixtureList('invalid', 'svg');
    }

    public function testDisallowedJavascriptUrl()
    {
        $fixture   = "<svg>\n<a href='javascript:alert(1)'><path /></a>\n</svg>";
        $sanitized = "<svg>\n<a><path/></a>\n</svg>";

        $this->assertSame($sanitized, Svg::sanitize($fixture));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
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

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in attribute "href" (line 1): Unknown URL type');
        Svg::validate($fixture);
    }

    public function testDisallowedXlinkAttack()
    {
        $fixture   = $this->fixture('disallowed/xlink-attack.svg');
        $sanitized = $this->fixture('sanitized/xlink-attack.svg');

        $this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in attribute "xlink:href" (line 2): Unknown URL type');
        Svg::validateFile($fixture);
    }

    public function testDisallowedExternalXmlns1()
    {
        $fixture   = $this->fixture('disallowed/external-xmlns-1.svg');
        $sanitized = $this->fixture('sanitized/external-xmlns-1.svg');

        $this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The namespace "https://malicious.com/script.php" is not allowed (around line 1)');
        Svg::validateFile($fixture);
    }

    public function testDisallowedExternalXmlns2()
    {
        $fixture   = $this->fixture('disallowed/external-xmlns-2.svg');
        $sanitized = $this->fixture('sanitized/external-xmlns-2.svg');

        $this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The namespace "https://malicious.com/script.php" is not allowed (around line 1)');
        Svg::validateFile($fixture);
    }

    public function testDisallowedDataUriSvg1()
    {
        $fixture   = $this->fixture('disallowed/data-uri-svg-1.svg');
        $sanitized = $this->fixture('sanitized/data-uri-svg-1.svg');

        $this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in attribute "style" (line 7)');
        Svg::validateFile($fixture);
    }

    public function testDisallowedDataUriSvg2()
    {
        $fixture   = $this->fixture('disallowed/data-uri-svg-2.svg');
        $sanitized = $this->fixture('sanitized/data-uri-svg-2.svg');

        $this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in attribute "filter" (line 7)');
        Svg::validateFile($fixture);
    }

    public function testDisallowedExternalSource1()
    {
        $fixture   = $this->fixture('disallowed/external-source-1.svg');
        $sanitized = $this->fixture('sanitized/external-source-1.svg');

        $this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in attribute "style" (line 2)');
        Svg::validateFile($fixture);
    }

    public function testDisallowedExternalSource2()
    {
        $fixture   = $this->fixture('disallowed/external-source-2.svg');
        $sanitized = $this->fixture('sanitized/external-source-2.svg');

        $this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in attribute "href" (line 2)');
        Svg::validateFile($fixture);
    }

    public function testDisallowedOnclickAttr()
    {
        $fixture   = "<svg>\n<path onclick='alert(1)' />\n</svg>";
        $sanitized = "<svg>\n<path/>\n</svg>";

        $this->assertSame($sanitized, Svg::sanitize($fixture));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The "onclick" attribute (line 2) is not allowed');
        Svg::validate($fixture);
    }

    public function testDisallowedOnloadAttr()
    {
        $fixture   = '<svg onload="alert(1)"></svg>';
        $sanitized = '<svg/>';

        $this->assertSame($sanitized, Svg::sanitize($fixture));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The "onload" attribute (line 1) is not allowed');
        Svg::validate($fixture);
    }

    public function testDisallowedUseAttack1()
    {
        $fixture   = $this->fixture('disallowed/use-attack-1.svg');
        $sanitized = $this->fixture('sanitized/use-attack-1.svg');

        $this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Nested "use" elements are not allowed (used in line 14)');
        Svg::validateFile($fixture);
    }

    public function testDisallowedUseAttack2()
    {
        $fixture   = $this->fixture('disallowed/use-attack-2.svg');
        $sanitized = $this->fixture('sanitized/use-attack-2.svg');

        $this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Nested "use" elements are not allowed (used in line 18)');
        Svg::validateFile($fixture);
    }

    public function testDisallowedUseAttack3()
    {
        $fixture   = $this->fixture('disallowed/use-attack-3.svg');
        $sanitized = $this->fixture('sanitized/use-attack-3.svg');

        $this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Nested "use" elements are not allowed (used in line 18)');
        Svg::validateFile($fixture);
    }

    public function testDisallowedDoctypeExternal1()
    {
        $fixture   = $this->fixture('disallowed/doctype-external-1.svg');
        $sanitized = $this->fixture('sanitized/doctype-external-1.svg');

        $this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The doctype must not reference external files');
        Svg::validateFile($fixture);
    }

    public function testDisallowedDoctypeExternal2()
    {
        $fixture   = $this->fixture('disallowed/doctype-external-2.svg');
        $sanitized = $this->fixture('sanitized/doctype-external-2.svg');

        $this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The doctype must not reference external files');
        Svg::validateFile($fixture);
    }

    public function testDisallowedDoctypeEntityAttack()
    {
        $fixture   = $this->fixture('disallowed/doctype-entity-attack.svg');
        $sanitized = $this->fixture('sanitized/doctype-entity-attack.svg');

        $this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The doctype must not define a subset');
        Svg::validateFile($fixture);
    }

    public function testDisallowedDoctypeWrong()
    {
        $fixture   = $this->fixture('disallowed/doctype-wrong.svg');
        $sanitized = $this->fixture('sanitized/doctype-wrong.svg');

        $this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid doctype');
        Svg::validateFile($fixture);
    }

    public function testDisallowedCaseSensitive()
    {
        $fixture   = "<svg>\n<Text x='0' y='20'>Hello</Text>\n</svg>";
        $sanitized = "<svg>\n\n</svg>";

        $this->assertSame($sanitized, Svg::sanitize($fixture));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The "Text" element (line 2) is not allowed');
        Svg::validate($fixture);
    }

    public function testDisallowedForeignobject()
    {
        $fixture   = '<svg><foreignobject><iframe onload="alert(1)" /></foreignobject></svg>';
        $sanitized = '<svg/>';

        $this->assertSame($sanitized, Svg::sanitize($fixture));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The "foreignobject" element (line 1) is not allowed');
        Svg::validate($fixture);
    }

    public function testDisallowedSet()
    {
        $fixture   = $this->fixture('disallowed/set.svg');
        $sanitized = $this->fixture('sanitized/set.svg');

        $this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The "set" element (line 7) is not allowed');
        Svg::validateFile($fixture);
    }

    public function testDisallowedScript()
    {
        $fixture   = '<svg><script>alert(1)</script></svg>';
        $sanitized = '<svg/>';

        $this->assertSame($sanitized, Svg::sanitize($fixture));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The "script" element (line 1) is not allowed');
        Svg::validate($fixture);
    }

    public function testDisallowedBlockquote()
    {
        $fixture   = '<svg><blockquote>SVGs are SVGs are SVGs</blockquote></svg>';
        $sanitized = '<svg/>';

        $this->assertSame($sanitized, Svg::sanitize($fixture));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The "blockquote" element (line 1) is not allowed');
        Svg::validate($fixture);
    }

    public function testDisallowedStyleUrlExternal()
    {
        $fixture   = $this->fixture('disallowed/style-url-external.svg');
        $sanitized = $this->fixture('sanitized/style-url-external.svg');

        $this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in the "style" element (around line 3)');
        Svg::validateFile($fixture);
    }

    public function testDisallowedStylesheet()
    {
        $fixture   = $this->fixture('disallowed/stylesheet.svg');
        $sanitized = $this->fixture('sanitized/stylesheet.svg');

        $this->assertStringEqualsFile($sanitized, Svg::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The "xml-stylesheet" processing instruction (line 6) is not allowed');
        Svg::validateFile($fixture);
    }

    public function testParseNonSvg()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The file is not a SVG (got <html>)');

        Svg::validate('<html></html>');
    }
}
