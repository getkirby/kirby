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
        $this->assertNull(Svg::validateFile($this->fixture($file)));
    }

    public function allowedProvider()
    {
        return $this->fixtureList('allowed', 'svg');
    }

    /**
     * @dataProvider invalidProvider
     */
    public function testInvalid(string $file)
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The file could not be parsed');

        Svg::validateFile($this->fixture($file));
    }

    public function invalidProvider()
    {
        return $this->fixtureList('invalid', 'svg');
    }

    public function testIsAllowedUrlJavascript()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in attribute: href (line 2)');

        Svg::validate("<svg>\n<a href='javascript:alert(1)'><path /></a>\n</svg>");
    }

    public function testIsAllowedUrlJavascriptWithUnicodeLS()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in attribute: href (line 1)');

        /**
         * Test fixture inspired by DOMPurify
         * @link https://github.com/cure53/DOMPurify
         * @copyright 2015 Mario Heiderich
         * @license https://www.apache.org/licenses/LICENSE-2.0
         */
        Svg::validate('<svg>123<a href="\u2028javascript:alert(1)">I am a dolphin!</a></svg>');
    }

    public function testIsAllowedUrlXlink()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in attribute: xlink:href (line 2)');

        Svg::validateFile($this->fixture('disallowed/xlink-attack.svg'));
    }

    public function testIsAllowedUrlXmlns1()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The namespace "xmlns" (around line 2) is not allowed or has an invalid value');

        Svg::validateFile($this->fixture('disallowed/external-xmlns-1.svg'));
    }

    public function testIsAllowedUrlXmlns2()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The namespace "xmlns:malicious" (around line 2) is not allowed or has an invalid value');

        Svg::validateFile($this->fixture('disallowed/external-xmlns-2.svg'));
    }

    public function testLoadNonSvg()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The file is not a SVG (got <html>)');

        Svg::validate('<html></html>');
    }

    public function testValidateAttrsAria()
    {
        $this->assertNull(Svg::validate('<svg><path aria-label="Test" /></svg>'));
    }

    public function testValidateAttrsData()
    {
        $this->assertNull(Svg::validate('<svg><path data-color="test" /></svg>'));
    }

    public function testValidateAttrsDataUri1()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in attribute: style (line 7)');

        Svg::validateFile($this->fixture('disallowed/data-uri-svg-1.svg'));
    }

    public function testValidateAttrsDataUri2()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in attribute: filter (line 7)');

        Svg::validateFile($this->fixture('disallowed/data-uri-svg-2.svg'));
    }

    public function testValidateAttrsExternalSource1()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in attribute: style (line 3)');

        Svg::validateFile($this->fixture('disallowed/external-source-1.svg'));
    }

    public function testValidateAttrsExternalSource2()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in attribute: href (line 3)');

        Svg::validateFile($this->fixture('disallowed/external-source-2.svg'));
    }

    public function testValidateAttrsOnclick()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The "onclick" attribute (line 2) is not allowed in SVGs');

        Svg::validate("<svg>\n<path onclick='alert(1)' />\n</svg>");
    }

    public function testValidateAttrsOnload()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The "onload" attribute (line 1) is not allowed in SVGs');

        Svg::validate('<svg onload="alert(1)"></svg>');
    }

    public function testValidateAttrsUse1()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Nested "use" elements are not allowed in SVGs (used in line 15)');

        Svg::validateFile($this->fixture('disallowed/use-attack-1.svg'));
    }

    public function testValidateAttrsUse2()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Nested "use" elements are not allowed in SVGs (used in line 19)');

        Svg::validateFile($this->fixture('disallowed/use-attack-2.svg'));
    }

    public function testValidateDoctypeExternalSubset1()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The doctype must not reference external files');

        Svg::validateFile($this->fixture('disallowed/doctype-external-1.svg'));
    }

    public function testValidateDoctypeExternalSubset2()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The doctype must not reference external files');

        Svg::validateFile($this->fixture('disallowed/doctype-external-2.svg'));
    }

    public function testValidateDoctypeInternalSubset()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The doctype must not define a subset');

        Svg::validateFile($this->fixture('disallowed/doctype-entity-attack.svg'));
    }

    public function testValidateDoctypeWrong()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid doctype');

        Svg::validateFile($this->fixture('disallowed/doctype-wrong.svg'));
    }

    public function testValidateElementsCaseSensitive()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The "Text" element (line 2) is not allowed in SVGs');

        Svg::validate("<svg>\n<Text x='0' y='20'>Hello</Text>\n</svg>");
    }

    public function testValidateElementsForeignobject()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The "foreignobject" element (line 1) is not allowed in SVGs');

        Svg::validate('<svg><foreignobject><iframe onload="alert(1)" /></foreignobject></svg>');
    }

    public function testValidateElementsSet()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The "set" element (line 7) is not allowed in SVGs');

        Svg::validateFile($this->fixture('disallowed/set.svg'));
    }

    public function testValidateElementsScript()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The "script" element (line 1) is not allowed in SVGs');

        Svg::validate('<svg><script>alert(1)</script></svg>');
    }

    public function testValidateElementsUnknown()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The "blockquote" element (line 1) is not allowed in SVGs');

        Svg::validate('<svg><blockquote>SVGs are SVGs are SVGs</blockquote></svg>');
    }

    public function testValidateElementsStyleURL()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in the <style> element (around line 3)');

        Svg::validateFile($this->fixture('disallowed/style-url-external.svg'));
    }

    public function testValidateProcessingInstructionsStylesheet()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The "xml-stylesheet" processing instruction (line 6) is not allowed');

        Svg::validateFile($this->fixture('disallowed/stylesheet.svg'));
    }
}
