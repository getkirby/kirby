<?php

namespace Kirby\Toolkit;

/**
 * @coversDefaultClass \Kirby\Toolkit\Dom
 */
class DomTest extends TestCase
{
    public function parseSaveHtmlProvider(): array
    {
        return [
            // full document with doctype
            [
                '<!DOCTYPE html><html><body><p>Lorem ipsum</p></body></html>',
                "<!DOCTYPE html>\n<html><body><p>Lorem ipsum</p></body></html>"
            ],

            // full document with doctype (with whitespace)
            [
                "<!DOCTYPE html>\n\n<html><body><p>Lorem ipsum</p></body></html>",
                "<!DOCTYPE html>\n<html><body><p>Lorem ipsum</p></body></html>"
            ],

            // Unicode string
            ['<html><body><p>TEST — jūsų šildymo sistemai</p></body></html>'],

            // Unicode string with entities
            [
                '<html><body><p>TEST &mdash;&nbsp;jūsų šildymo sistemai</p></body></html>',
                '<html><body><p>TEST — jūsų šildymo sistemai</p></body></html>',
            ],

            // weird whitespace
            ["<html>\n  <body>\n    <p>Lorem ipsum\n</p>\n  </body>\n</html>"],

            // partial document with syntax issue
            [
                '<p>This is <strong>important</strong!</p>',
                '<html><body><p>This is <strong>important</strong>!</p></body></html>'
            ]
        ];
    }

    /**
     * @dataProvider parseSaveHtmlProvider
     * @covers ::__construct
     * @covers ::toString
     */
    public function testParseSaveHtml(string $html, string $expected = null)
    {
        $dom = new Dom($html, 'HTML');
        $this->assertSame(($expected ?? $html) . "\n", $dom->toString());
    }

    public function urlProvider(): array
    {
        return [
            // allowed empty url
            ['', true],

            // allowed path
            ['/', true],

            // allowed path
            ['/some/path', true],

            // allowed fragment
            ['#', true],

            // allowed fragment
            ['#test-fragment', true],

            // allowed data uri
            ['data:image/jpeg;base64,test', true, [
                'allowedDataUris' => [
                    'data:image/jpeg;base64'
                ]
            ]],

            // allowed URL when all domains are accepted
            ['http://getkirby.com', true, [
                'allowedDomains' => true
            ]],

            // allowed URL when the domain is accepted
            ['http://getkirby.com', true, [
                'allowedDomains' => [
                    'getkirby.com'
                ]
            ]],

            // allowed empty email address
            ['mailto:', true],

            // allowed valid email address
            ['mailto:test@getkirby.com', true],

            // allowed empty phone number
            ['tel:', true],

            // allowed phone number
            ['tel:+491122334455', true],

            // forbidden data uri
            ['data:image/jpeg;base64,test', 'Invalid data URI', [
                'allowedDataUris' => []
            ]],

            // forbidden data uri
            ['data:image/png;base64,test', 'Invalid data URI', [
                'allowedDataUris' => ['data:image/jpeg;base64']
            ]],

            // forbidden URL when no domains are accepted
            ['https://getkirby.com', 'The hostname "getkirby.com" is not allowed', [
                'allowedDomains' => []
            ]],

            // forbidden URL when the particular domain is not accepted
            ['https://google.com', 'The hostname "google.com" is not allowed', [
                'allowedDomains' => [
                    'getkirby.com'
                ]
            ]],

            // forbidden invalid email address
            ['mailto:test', 'Invalid email address'],

            // forbidden phone numbers
            ['tel:test', 'Invalid telephone number'],

            // forbidden phone numbers - too much formatting
            ['tel:+49 (0) 1234 5678', 'Invalid telephone number'],

            // forbidden phone numbers - invalid plus sign position
            ['tel:491234+5678', 'Invalid telephone number'],

            // forbidden URL type
            ['ftp:test', 'Unknown URL type'],

            // forbidden URL type
            ['ftp://test', 'Unknown URL type'],
        ];
    }

    /**
     * @dataProvider urlProvider
     * @covers ::isAllowedUrl
     */
    public function testIsAllowedUrl(string $url, $expected, array $options = [])
    {
        $this->assertSame($expected, Dom::isAllowedUrl($url, $options));
    }
}
