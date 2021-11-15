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
        $fixture = $this->fixture($file);

        $this->assertNull(Xml::validateFile($fixture));

        $sanitized = Xml::sanitize(file_get_contents($fixture));
        $this->assertStringEqualsFile($fixture, $sanitized);
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
        $this->expectExceptionMessage('The markup could not be parsed');

        Xml::validateFile($this->fixture($file));
    }

    public function invalidProvider()
    {
        return $this->fixtureList('invalid', 'xml');
    }

    public function testDisallowedJavascriptUrl()
    {
        $fixture   = "<xml>\n<a href='javascript:alert(1)'></a>\n</xml>";
        $sanitized = "<xml>\n<a/>\n</xml>";

        $this->assertSame($sanitized, Xml::sanitize($fixture));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in attribute "href" (line 2): Unknown URL type');
        Xml::validate($fixture);
    }

    public function testDisallowedJavascriptUrlWithUnicodeLS()
    {
        /**
         * Test fixture inspired by DOMPurify
         * @link https://github.com/cure53/DOMPurify
         * @copyright 2015 Mario Heiderich
         * @license https://www.apache.org/licenses/LICENSE-2.0
         */
        $fixture   = '<xml>123<a href="\u2028javascript:alert(1)">I am a dolphin!</a></xml>';
        $sanitized = '<xml>123<a>I am a dolphin!</a></xml>';

        $this->assertSame($sanitized, Xml::sanitize($fixture));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in attribute "href" (line 1): Unknown URL type');
        Xml::validate($fixture);
    }

    public function testDisallowedXlinkAttack()
    {
        $fixture   = $this->fixture('disallowed/xlink-attack.xml');
        $sanitized = $this->fixture('sanitized/xlink-attack.xml');

        $this->assertStringEqualsFile($sanitized, Xml::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in attribute "xlink:href" (line 2): Unknown URL type');
        Xml::validateFile($fixture);
    }

    public function testDisallowedDataUriSvg1()
    {
        $fixture   = $this->fixture('disallowed/data-uri-svg-1.xml');
        $sanitized = $this->fixture('sanitized/data-uri-svg-1.xml');

        $this->assertStringEqualsFile($sanitized, Xml::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in attribute "style" (line 7): Invalid data URI');
        Xml::validateFile($fixture);
    }

    public function testDisallowedDataUriSvg2()
    {
        $fixture   = $this->fixture('disallowed/data-uri-svg-2.xml');
        $sanitized = $this->fixture('sanitized/data-uri-svg-2.xml');

        $this->assertStringEqualsFile($sanitized, Xml::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in attribute "filter" (line 7): Invalid data URI');
        Xml::validateFile($fixture);
    }

    public function testDisallowedExternalSource1()
    {
        $fixture   = $this->fixture('disallowed/external-source-1.xml');
        $sanitized = $this->fixture('sanitized/external-source-1.xml');

        $this->assertStringEqualsFile($sanitized, Xml::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in attribute "style" (line 2): The hostname "malicious.com" is not allowed');
        Xml::validateFile($fixture);
    }

    public function testDisallowedExternalSource2()
    {
        $fixture   = $this->fixture('disallowed/external-source-2.xml');
        $sanitized = $this->fixture('sanitized/external-source-2.xml');

        $this->assertStringEqualsFile($sanitized, Xml::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The URL is not allowed in attribute "href" (line 2): The hostname "malicious.com" is not allowed');
        Xml::validateFile($fixture);
    }

    public function testDisallowedNamespaceSvg()
    {
        $fixture   = $this->fixture('disallowed/namespace-svg.xml');
        $sanitized = $this->fixture('sanitized/namespace-svg.xml');

        $this->assertStringEqualsFile($sanitized, Xml::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The namespace "http://www.w3.org/2000/svg" is not allowed (around line 1)');
        Xml::validateFile($fixture);
    }

    public function testDisallowedNamespaceXhtml1()
    {
        $fixture   = $this->fixture('disallowed/namespace-xhtml-1.xml');
        $sanitized = $this->fixture('sanitized/namespace-xhtml-1.xml');

        $this->assertStringEqualsFile($sanitized, Xml::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The namespace "http://www.w3.org/1999/xhtml" is not allowed (around line 1)');
        Xml::validateFile($fixture);
    }

    public function testDisallowedNamespaceXhtml2()
    {
        $fixture   = $this->fixture('disallowed/namespace-xhtml-2.xml');
        $sanitized = $this->fixture('sanitized/namespace-xhtml-2.xml');

        $this->assertStringEqualsFile($sanitized, Xml::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The namespace "http://www.w3.org/1999/xhtml" is not allowed (around line 1)');
        Xml::validateFile($fixture);
    }

    public function testDisallowedDoctypeExternal1()
    {
        $fixture   = $this->fixture('disallowed/doctype-external-1.xml');
        $sanitized = $this->fixture('sanitized/doctype-external-1.xml');

        $this->assertStringEqualsFile($sanitized, Xml::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The doctype must not reference external files');
        Xml::validateFile($fixture);
    }

    public function testDisallowedDoctypeExternal2()
    {
        $fixture   = $this->fixture('disallowed/doctype-external-2.xml');
        $sanitized = $this->fixture('sanitized/doctype-external-2.xml');

        $this->assertStringEqualsFile($sanitized, Xml::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The doctype must not reference external files');
        Xml::validateFile($fixture);
    }

    public function testDisallowedDoctypeEntityAttack()
    {
        $fixture   = $this->fixture('disallowed/doctype-entity-attack.xml');
        $sanitized = $this->fixture('sanitized/doctype-entity-attack.xml');

        $this->assertStringEqualsFile($sanitized, Xml::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The doctype must not define a subset');
        Xml::validateFile($fixture);
    }

    public function testDisallowedDoctypeSvg()
    {
        $fixture   = $this->fixture('disallowed/doctype-svg.xml');
        $sanitized = $this->fixture('sanitized/doctype-svg.xml');

        $this->assertStringEqualsFile($sanitized, Xml::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The doctype is not allowed in XML files');
        Xml::validateFile($fixture);
    }

    public function testDisallowedDoctypeXhtml()
    {
        $fixture   = $this->fixture('disallowed/doctype-xhtml.xml');
        $sanitized = $this->fixture('sanitized/doctype-xhtml.xml');

        $this->assertStringEqualsFile($sanitized, Xml::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The doctype is not allowed in XML files');
        Xml::validateFile($fixture);
    }

    public function testDisallowedStylesheet()
    {
        $fixture   = $this->fixture('disallowed/stylesheet.xml');
        $sanitized = $this->fixture('sanitized/stylesheet.xml');

        $this->assertStringEqualsFile($sanitized, Xml::sanitize(file_get_contents($fixture)));

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The "xml-stylesheet" processing instruction (line 6) is not allowed');
        Xml::validateFile($fixture);
    }
}
