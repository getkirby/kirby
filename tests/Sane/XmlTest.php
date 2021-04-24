<?php

namespace Kirby\Sane;

/**
 * @covers \Kirby\Sane\Xml
 */
class XmlTest extends TestCase
{
    protected $type = 'xml';

    public function setUp(): void
    {
        Xml::$allowedDomains = ['getkirby.com'];
    }

    public function tearDown(): void
    {
        Xml::$allowedDomains = [];
    }

    /**
     * @dataProvider allowedProvider
     */
    public function testAllowed(string $file)
    {
        $this->assertNull(Xml::validateFile($this->fixture($file)));
    }

    public function allowedProvider()
    {
        return $this->fixtureList('allowed', 'xml');
    }

    /**
     * @dataProvider invalidProvider
     */
    public function testInvalid(string $file)
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The file could not be parsed');

        Xml::validateFile($this->fixture($file));
    }

    public function invalidProvider()
    {
        return $this->fixtureList('invalid', 'xml');
    }

    public function testIsAllowedUrlJavascript()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in attribute: href (line 2)');

        Xml::validate("<xml>\n<a href='javascript:alert(1)'></a>\n</xml>");
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
        Xml::validate('<xml>123<a href="\u2028javascript:alert(1)">I am a dolphin!</a></xml>');
    }

    public function testIsAllowedUrlXlink()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in attribute: xlink:href (line 2)');

        Xml::validateFile($this->fixture('disallowed/xlink-attack.xml'));
    }

    public function testValidateAttrsDataUri1()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in attribute: style (line 7)');

        Xml::validateFile($this->fixture('disallowed/data-uri-svg-1.xml'));
    }

    public function testValidateAttrsDataUri2()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in attribute: filter (line 7)');

        Xml::validateFile($this->fixture('disallowed/data-uri-svg-2.xml'));
    }

    public function testValidateAttrsExternalSource1()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in attribute: style (line 3)');

        Xml::validateFile($this->fixture('disallowed/external-source-1.xml'));
    }

    public function testValidateAttrsExternalSource2()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in attribute: href (line 3)');

        Xml::validateFile($this->fixture('disallowed/external-source-2.xml'));
    }

    public function testValidateAttrsNamespace1()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The namespace is not allowed in XML files (around line 1)');

        Xml::validateFile($this->fixture('disallowed/namespace-svg.xml'));
    }

    public function testValidateAttrsNamespace2()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The namespace is not allowed in XML files (around line 1)');

        Xml::validateFile($this->fixture('disallowed/namespace-xhtml.xml'));
    }

    public function testValidateDoctypeExternalSubset1()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The doctype must not reference external files');

        Xml::validateFile($this->fixture('disallowed/doctype-external-1.xml'));
    }

    public function testValidateDoctypeExternalSubset2()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The doctype must not reference external files');

        Xml::validateFile($this->fixture('disallowed/doctype-external-2.xml'));
    }

    public function testValidateDoctypeInternalSubset()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The doctype must not define a subset');

        Xml::validateFile($this->fixture('disallowed/doctype-entity-attack.xml'));
    }

    public function testValidateDoctypeSvg()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The doctype is not allowed in XML files');

        Xml::validateFile($this->fixture('disallowed/doctype-svg.xml'));
    }

    public function testValidateDoctypeXhtml()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The doctype is not allowed in XML files');

        Xml::validateFile($this->fixture('disallowed/doctype-xhtml.xml'));
    }

    public function testValidateProcessingInstructionsStylesheet()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The "xml-stylesheet" processing instruction (line 6) is not allowed');

        Xml::validateFile($this->fixture('disallowed/stylesheet.xml'));
    }
}
