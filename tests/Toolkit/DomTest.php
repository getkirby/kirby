<?php

namespace Kirby\Toolkit;

use Kirby\Cms\App;

/**
 * @coversDefaultClass \Kirby\Toolkit\Dom
 */
class DomTest extends TestCase
{
    public function parseSaveProvider(): array
    {
        return [
            // full document with doctype
            [
                'html',
                '<!DOCTYPE html><html><body><p>Lorem ipsum</p></body></html>',
                "<!DOCTYPE html>\n<html><body><p>Lorem ipsum</p></body></html>"
            ],

            // full document with lowercase doctype
            [
                'html',
                '<!doctype html><html><body><p>Lorem ipsum</p></body></html>',
                "<!DOCTYPE html>\n<html><body><p>Lorem ipsum</p></body></html>"
            ],

            // full document with doctype (with whitespace)
            [
                'html',
                "<!DOCTYPE html>\n\n<html><body><p>Lorem ipsum</p></body></html>\n\n",
                "<!DOCTYPE html>\n<html><body><p>Lorem ipsum</p></body></html>\n"
            ],

            // Unicode string
            [
                'html',
                '<html><body><p>TEST — jūsų šildymo sistemai</p></body></html>'
            ],

            // Unicode string with entities
            [
                'html',
                '<html><body><p>TEST &mdash;&nbsp;jūs&#371; &scaron;ildymo sistemai</p></body></html>',
                '<html><body><p>TEST — jūsų šildymo sistemai</p></body></html>',
            ],

            // weird whitespace
            [
                'html',
                "<html>\n  <body>\n    <p>Lorem ipsum\n</p>\n  </body>\n</html>\n"
            ],

            // HTML snippet with syntax issue
            [
                'html',
                '<p>This is <strong>important</strong!</p>',
                '<p>This is <strong>important</strong>!</p>'
            ],

            // HTML snippet with doctype
            [
                'html',
                '<!DOCTYPE html><p>This is <strong>important</strong>!</p>',
                "<!DOCTYPE html>\n<html><body><p>This is <strong>important</strong>!</p></body></html>"
            ],

            // HTML snippet without wrapper tag
            [
                'html',
                'This is <em>very</em> <strong>important</strong>!',
                'This is <em>very</em> <strong>important</strong>!'
            ],

            // just a <body>
            [
                'html',
                '<body><p>This is <strong>important</strong>!</p></body>',
                '<body><p>This is <strong>important</strong>!</p></body>'
            ],

            // just a <body> with attributes
            [
                'html',
                '<body id="test"><p>This is <strong>important</strong>!</p></body>',
                '<body id="test"><p>This is <strong>important</strong>!</p></body>'
            ],

            // full document, but without body
            [
                'html',
                '<html><p>This is <strong>important</strong>!</p><html>',
                '<html><body><p>This is <strong>important</strong>!</p></body></html>'
            ],

            // full document, but without body; <html> with attributes
            [
                'html',
                '<html id="test"><p>This is <strong>important</strong>!</p><html>',
                '<html id="test"><body><p>This is <strong>important</strong>!</p></body></html>'
            ],

            // document with doctype
            [
                'xml',
                '<!DOCTYPE svg><svg><text>Lorem ipsum</text></svg>',
                "<!DOCTYPE svg>\n<svg><text>Lorem ipsum</text></svg>"
            ],

            // document with doctype (with whitespace)
            [
                'xml',
                "<!DOCTYPE svg>\n\n<svg><text>Lorem ipsum</text></svg>",
                "<!DOCTYPE svg>\n<svg><text>Lorem ipsum</text></svg>"
            ],

            // document with XML declaration
            [
                'xml',
                '<?xml version="1.0"?><svg><text>Lorem ipsum</text></svg>',
                "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<svg><text>Lorem ipsum</text></svg>"
            ],

            // document with XML declaration and doctype
            [
                'xml',
                '<?xml version="1.0" encoding="utf-8"?><!DOCTYPE svg><svg><text>Lorem ipsum</text></svg>',
                "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<!DOCTYPE svg>\n<svg><text>Lorem ipsum</text></svg>"
            ],

            // Unicode string
            [
                'xml',
                '<xml>TEST — jūsų šildymo sistemai</xml>'
            ],

            // Unicode string with entities
            [
                'xml',
                '<svg><text>TEST &#x2014; jūs&#371; šildymo sistemai</text></svg>',
                '<svg><text>TEST — jūsų šildymo sistemai</text></svg>',
            ],

            // weird whitespace
            [
                'xml',
                "<svg>\n  <text>\n    Lorem ipsum\n</text>\n  </svg>"
            ],
        ];
    }

    /**
     * @dataProvider parseSaveProvider
     * @covers ::__construct
     * @covers ::toString
     * @covers ::exportHtml
     * @covers ::exportXml
     */
    public function testParseSave(string $type, string $code, string $expected = null)
    {
        $dom = new Dom($code, $type);
        $this->assertSame($expected ?? $code, $dom->toString());
    }

    public function parseSaveNormalizeProvider(): array
    {
        return [
            // full document with doctype
            [
                'html',
                '<!DOCTYPE html><html><body><p>Lorem ipsum</p></body></html>',
                "<!DOCTYPE html>\n<html><body><p>Lorem ipsum</p></body></html>"
            ],

            // Unicode string with entities
            [
                'html',
                '<html><body><p>TEST &mdash;&nbsp;jūs&#371; &scaron;ildymo sistemai</p></body></html>',
                '<html><body><p>TEST — jūsų šildymo sistemai</p></body></html>',
            ],

            // weird whitespace
            [
                'html',
                "<html>\n  <body>\n    <p>Lorem ipsum\n</p>\n  </body>\n</html>\n"
            ],

            // HTML snippet with syntax issue
            [
                'html',
                '<p>This is <strong>important</strong!</p>',
                '<html><body><p>This is <strong>important</strong>!</p></body></html>'
            ],

            // HTML snippet with doctype
            [
                'html',
                '<!DOCTYPE html><p>This is <strong>important</strong>!</p>',
                "<!DOCTYPE html>\n<html><body><p>This is <strong>important</strong>!</p></body></html>"
            ],

            // just a <body>
            [
                'html',
                '<body><p>This is <strong>important</strong>!</p></body>',
                '<html><body><p>This is <strong>important</strong>!</p></body></html>'
            ],

            // just a <body> with attributes
            [
                'html',
                '<body id="test"><p>This is <strong>important</strong>!</p></body>',
                '<html><body id="test"><p>This is <strong>important</strong>!</p></body></html>'
            ],

            // full document, but without body
            [
                'html',
                '<html><p>This is <strong>important</strong>!</p><html>',
                '<html><body><p>This is <strong>important</strong>!</p></body></html>'
            ],

            // full document, but without body; <html> with attributes
            [
                'html',
                '<html id="test"><p>This is <strong>important</strong>!</p><html>',
                '<html id="test"><body><p>This is <strong>important</strong>!</p></body></html>'
            ],

            // document with doctype
            [
                'xml',
                '<!DOCTYPE svg><svg><text>Lorem ipsum</text></svg>',
                "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<!DOCTYPE svg>\n<svg><text>Lorem ipsum</text></svg>"
            ],

            // document with doctype (with whitespace)
            [
                'xml',
                "<!DOCTYPE svg>\n\n<svg><text>Lorem ipsum</text></svg>",
                "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<!DOCTYPE svg>\n<svg><text>Lorem ipsum</text></svg>"
            ],

            // document with XML declaration
            [
                'xml',
                '<?xml version="1.0"?><svg><text>Lorem ipsum</text></svg>',
                "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<svg><text>Lorem ipsum</text></svg>"
            ],

            // document with XML declaration and doctype
            [
                'xml',
                '<?xml version="1.0" encoding="utf-8"?><!DOCTYPE svg><svg><text>Lorem ipsum</text></svg>',
                "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<!DOCTYPE svg>\n<svg><text>Lorem ipsum</text></svg>"
            ],

            // Unicode string
            [
                'xml',
                '<xml>TEST — jūsų šildymo sistemai</xml>',
                "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<xml>TEST — jūsų šildymo sistemai</xml>"
            ],

            // Unicode string with UTF-8 XML declaration
            [
                'xml',
                '<?xml version="1.0" encoding="utf-8"?><xml>TEST — jūsų šildymo sistemai</xml>',
                "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<xml>TEST — jūsų šildymo sistemai</xml>"
            ],

            // weird whitespace
            [
                'xml',
                "<svg>\n  <text>\n    Lorem ipsum\n</text>\n  </svg>\n",
                "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<svg>\n  <text>\n    Lorem ipsum\n</text>\n  </svg>\n"
            ],

            // weird whitespace with XML declaration
            [
                'xml',
                "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<svg>\n  <text>\n    Lorem ipsum\n</text>\n  </svg>\n"
            ],
        ];
    }

    /**
     * @dataProvider parseSaveNormalizeProvider
     * @covers ::__construct
     * @covers ::toString
     * @covers ::exportHtml
     * @covers ::exportXml
     */
    public function testParseSaveNormalize(string $type, string $code, string $expected = null)
    {
        $dom = new Dom($code, $type);
        $this->assertSame($expected ?? $code, $dom->toString(true));
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

            // allowed path
            ['some', true],

            // allowed path
            ['some/path', true],

            // allowed path
            ['some/path:test', true],

            // allowed path
            ['some/path:some/test', true],

            // allowed path
            ['./some/path', true],

            // allowed fragment
            ['#', true],

            // allowed fragment
            ['#test-fragment', true],

            // allowed data uri when all are accepted
            ['data:image/jpeg;base64,test', true, [
                'allowedDataUris' => true
            ]],

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

            // forbidden protocol-relative URL
            ['//test', 'Protocol-relative URLs are not allowed'],

            // forbidden relative URL
            ['../some/path', 'The ../ sequence is not allowed in relative URLs'],

            // forbidden relative URL
            ['..\some\path', 'The ../ sequence is not allowed in relative URLs'],

            // forbidden relative URL
            ['some/../../path', 'The ../ sequence is not allowed in relative URLs'],

            // forbidden relative URL
            ['some\..\..\path', 'The ../ sequence is not allowed in relative URLs'],

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
            ['javascript:alert()', 'Unknown URL type'],

            // forbidden URL type
            ['ftp:test', 'Unknown URL type'],

            // forbidden URL type
            ['ftp://test', 'Unknown URL type'],

            // forbidden URL type
            ['my-amazing-protocol:test', 'Unknown URL type'],

            // forbidden URL type
            ['my-amazing-protocol://test', 'Unknown URL type'],
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

    public function urlProviderCms()
    {
        return [
            // allowed URL with site at the domain root
            ['https://getkirby.com', '/some/path', true],

            // allowed URL with site at the domain root
            ['/', '/some/path', true],

            // allowed URL with site in a subfolder
            ['https://getkirby.com/some', '/some/path', true],

            // allowed URL with site in a subfolder
            ['/some', '/some/path', true],

            // disallowed URL with site in a subfolder
            ['https://getkirby.com/site', '/some/path', 'The URL points outside of the site index URL'],

            // disallowed URL with site in a subfolder
            ['/site', '/some/path', 'The URL points outside of the site index URL'],

            // disallowed URL with directory traversal
            ['https://getkirby.com/site', '/site/../some/path', 'The ../ sequence is not allowed in relative URLs'],

            // disallowed URL with directory traversal
            ['/site', '/site/../some/path', 'The ../ sequence is not allowed in relative URLs'],
        ];
    }

    /**
     * @dataProvider urlProviderCms
     * @covers ::isAllowedUrl
     */
    public function testIsAllowedUrlCms(string $indexUrl, string $url, $expected)
    {
        new App([
            'urls' => [
                'index' => $indexUrl
            ]
        ]);

        $this->assertSame($expected, Dom::isAllowedUrl($url, []));
    }
}
