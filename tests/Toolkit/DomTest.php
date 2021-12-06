<?php

namespace Kirby\Toolkit;

use Closure;
use DOMAttr;
use DOMDocument;
use DOMDocumentType;
use DOMElement;
use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;

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

    /**
     * @covers ::__construct
     */
    public function testParseInvalid()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage("The markup could not be parsed: Start tag expected, '<' not found");

        new Dom('{"this": "is not XML"}', 'XML');
    }

    /**
     * @covers ::body
     */
    public function testBody()
    {
        // with full document input
        $dom = new Dom('<html><body class="test"><p>This is a test</p></body></html>', 'HTML');
        $this->assertInstanceOf('DOMElement', $dom->body());
        $this->assertSame('<body class="test"><p>This is a test</p></body>', $dom->document()->saveHtml($dom->body()));

        // partial document 1
        $dom = new Dom('<body class="test"><p>This is a test</p></body>', 'HTML');
        $this->assertInstanceOf('DOMElement', $dom->body());
        $this->assertSame('<body class="test"><p>This is a test</p></body>', $dom->document()->saveHtml($dom->body()));

        // partial document 2
        $dom = new Dom('<p>This is a test</p>', 'HTML');
        $this->assertInstanceOf('DOMElement', $dom->body());
        $this->assertSame('<body><p>This is a test</p></body>', $dom->document()->saveHtml($dom->body()));

        // document without body
        $dom = new Dom('<html><head></head></html>', 'HTML');
        $this->assertNull($dom->body());
    }

    /**
     * @covers ::document
     */
    public function testDocument()
    {
        $dom = new Dom('<p>This is a test</p>', 'HTML');
        $this->assertSame("<html><body><p>This is a test</p></body></html>\n", $dom->document()->saveHtml());
    }

    public function extractUrlsProvider(): array
    {
        return [
            // empty input
            [
                '',
                []
            ],

            // one URL
            [
                'url(https://getkirby.com)',
                ['https://getkirby.com']
            ],
            [
                'url("https://getkirby.com/?test=test&another=test")',
                ['https://getkirby.com/?test=test&another=test']
            ],
            [
                'url(\'https://getkirby.com\')',
                ['https://getkirby.com']
            ],
            [
                'url(\'https://getkirby.com)',
                ['https://getkirby.com']
            ],
            [
                'url(https://getkirby.com")',
                ['https://getkirby.com']
            ],
            [
                'url(  https://getkirby.com   )',
                ['https://getkirby.com']
            ],
            [
                'url(  "https://getkirby.com"   )',
                ['https://getkirby.com']
            ],
            [
                'url(  "  https://getkirby.com "   )',
                ['  https://getkirby.com ']
            ],
            [
                'UrL(  "  https://getkirby.com "   )',
                ['  https://getkirby.com ']
            ],

            // multiple URLs
            [
                'url(https://getkirby.com); url(https://getkirby.com/test)',
                ['https://getkirby.com', 'https://getkirby.com/test']
            ],
            [
                'url("https://getkirby.com/?test=test&another=test"), url(https://getkirby.com/test)',
                ['https://getkirby.com/?test=test&another=test', 'https://getkirby.com/test']
            ],
            [
                'This is a test with an url(\'https://getkirby.com\') and another url("https://getkirby.com/test").',
                ['https://getkirby.com', 'https://getkirby.com/test']
            ],
            [
                'An url(\'https://getkirby.com) and another url(https://getkirby.com/test")',
                ['https://getkirby.com', 'https://getkirby.com/test']
            ],
            [
                'url(  https://getkirby.com   ) and URl(  "https://getkirby.com/test"   ) and uRl(  "  https://getkirby.com/another-test "   )',
                ['https://getkirby.com', 'https://getkirby.com/test', '  https://getkirby.com/another-test ']
            ],

            // invisible characters
            [
                "ur\0l\0\0(\0'test://te\0st'\0)\0",
                ['test://test']
            ],
        ];
    }

    /**
     * @dataProvider extractUrlsProvider
     * @covers ::extractUrls
     */
    public function testExtractUrls(string $url, array $expected)
    {
        $this->assertSame($expected, Dom::extractUrls($url));
    }

    public function isAllowedAttrProvider(): array
    {
        return [
            // only the global allowlist
            [
                'html',
                'class',
                ['class'],
                [],
                true,

                true
            ],
            [
                'html',
                'class',
                ['class'],
                [],
                [],

                true
            ],
            [
                'html',
                'aria-label',
                [],
                ['aria-'],
                true,

                true
            ],
            [
                'html',
                'test:test-attr',
                [],
                ['test:test-'],
                true,

                true
            ],
            [
                'html',
                'id',
                ['class'],
                ['aria-'],
                true,

                'Not included in the global allowlist'
            ],
            [
                'html',
                'test-attr',
                [],
                ['test:test-'],
                true,

                'Not included in the global allowlist'
            ],
            [
                'html',
                'id',
                ['class'],
                ['aria-'],
                [],

                'Not included in the global allowlist'
            ],

            // specific configuration by tag
            [
                'html',
                'class',
                ['class'],
                ['aria-'],
                ['html' => true],

                true
            ],
            [
                'html',
                'aria-label',
                ['class'],
                ['aria-'],
                ['html' => true],

                true
            ],
            [
                'html',
                'class',
                ['class'],
                ['aria-'],
                ['html' => ['class']],

                true
            ],
            [
                'html',
                'id',
                ['class'],
                ['aria-'],
                ['html' => ['id']],

                true
            ],
            [
                'html',
                'test:test-attr',
                ['class'],
                ['aria-'],
                ['html' => ['test:test-attr']],

                true
            ],
            [
                'html',
                'onload',
                ['class'],
                ['aria-'],
                ['html' => ['id']],

                'Not allowed by the "html" element'
            ],
            [
                'html',
                'class',
                ['class'],
                ['aria-'],
                ['html' => false],

                'The "html" element does not allow attributes'
            ],
        ];
    }

    /**
     * @dataProvider isAllowedAttrProvider
     * @covers ::isAllowedAttr
     */
    public function testIsAllowedAttr(string $tag, string $attr, $allowedAttrs, $allowedAttrPrefixes, $allowedTags, $expected)
    {
        $doc = new DOMDocument();
        $element = $doc->createElement($tag);
        $element->setAttributeNode($attr = new DOMAttr($attr));

        $options = [
            'allowedAttrPrefixes' => $allowedAttrPrefixes,
            'allowedAttrs'        => $allowedAttrs,
            'allowedTags'         => $allowedTags,
            'allowedNamespaces'   => ['test' => 'https://example.com']
        ];

        $this->assertSame($expected, Dom::isAllowedAttr($attr, $options));
    }

    public function isAllowedGlobalAttrProvider(): array
    {
        return [
            // all attrs are allowed
            [
                'test',
                true,
                [],

                true
            ],

            // test by prefix
            [
                'data-test',
                [],
                ['aria-', 'data-'],

                true
            ],
            [
                'test:test-attr',
                [],
                ['test:test-'],

                true
            ],
            [
                'aaria-',
                [],
                ['aria-', 'data-'],

                'Not included in the global allowlist'
            ],
            [
                'test',
                [],
                ['aria-', 'data-'],

                'Not included in the global allowlist'
            ],
            [
                'test:test-attr',
                [],
                ['test-'],

                'Not included in the global allowlist'
            ],
            [
                'custom:test-attr',
                [],
                ['test:test-'],

                'Not included in the global allowlist'
            ],

            // test by full name
            [
                'class',
                ['class', 'id'],
                [],

                true
            ],
            [
                'test:test-attr',
                ['test:test-attr'],
                [],

                true
            ],
            [
                'test',
                ['class', 'id'],
                [],

                'Not included in the global allowlist'
            ],
            [
                'test:test-attr',
                ['test-attr'],
                [],

                'Not included in the global allowlist'
            ],
            [
                'custom:test-attr',
                ['test:test-attr'],
                [],

                'Not included in the global allowlist'
            ],
            [
                'xml:space',
                ['xml:space'],
                [],

                true
            ],
            [
                'xml:space',
                [],
                [],

                'Not included in the global allowlist'
            ],
            [
                'xml:id',
                ['xml:space'],
                [],

                'Not included in the global allowlist'
            ],

            // either list may allow the attribute
            [
                'test-attr',
                ['test-attr'],
                ['aria-'],

                true
            ],
            [
                'test-attr',
                ['test'],
                ['test-'],

                true
            ],
            [
                'aria-label',
                ['test'],
                ['test-'],

                'Not included in the global allowlist'
            ],
        ];
    }

    /**
     * @dataProvider isAllowedGlobalAttrProvider
     * @covers ::isAllowedGlobalAttr
     */
    public function testIsAllowedGlobalAttr(string $name, $allowedAttrs, $allowedAttrPrefixes, $expected)
    {
        $attr    = new DOMAttr($name);
        $options = [
            'allowedAttrs'        => $allowedAttrs,
            'allowedAttrPrefixes' => $allowedAttrPrefixes,
            'allowedNamespaces'   => ['test' => 'https://example.com']
        ];

        $this->assertSame($expected, Dom::isAllowedGlobalAttr($attr, $options));
    }

    public function isAllowedUrlProvider(): array
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
     * @dataProvider isAllowedUrlProvider
     * @covers ::isAllowedUrl
     */
    public function testIsAllowedUrl(string $url, $expected, array $options = [])
    {
        $this->assertSame($expected, Dom::isAllowedUrl($url, $options));
    }

    public function isAllowedUrlCmsProvider()
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
     * @dataProvider isAllowedUrlCmsProvider
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

    /**
     * @covers ::innerMarkup
     */
    public function testInnerMarkup()
    {
        // XML markup
        $dom  = new Dom('<xml><test>Test <testtest>Test test</testtest>!</test></xml>', 'XML');
        $node = $dom->document()->getElementsByTagName('test')[0];
        $this->assertSame('Test <testtest>Test test</testtest>!', $dom->innerMarkup($node));

        // HTML markup
        $dom  = new Dom('<p id="test">Test <strong>Test test</strong>!</p>', 'HTML');
        $node = $dom->document()->getElementById('test');
        $this->assertSame('Test <strong>Test test</strong>!', $dom->innerMarkup($node));
    }

    public function listContainsNameProvider(): array
    {
        return [
            // basic tests
            [
                ['html', 'body'],
                ['body', ''],
                [],
                null,

                'body'
            ],
            [
                ['html', 'body'],
                ['body', ''],
                true,
                null,

                'body'
            ],
            [
                ['html', 'body'],
                ['script', ''],
                [],
                null,

                false
            ],
            [
                ['html', 'body'],
                ['script', ''],
                true,
                null,

                false
            ],
            [
                ['html', 'body'],
                ['BoDy', ''],
                [],
                null,

                false
            ],
            [
                ['html', 'body'],
                ['BoDy', ''],
                true,
                null,

                false
            ],

            // tests with namespaces
            [
                ['test', 'another-test'],
                ['test', 'https://example.com'],
                ['' => 'https://example.com'],
                null,

                'test' // namespace matches
            ],
            [
                ['test', 'another-test'],
                ['test', 'https://example.com'],
                true,
                null,

                'test' // all namespaces allowed
            ],
            [
                ['test', 'another-test'],
                ['test', 'https://example.com/different'],
                ['' => 'https://example.com'],
                null,

                false // namespace is not allowed
            ],
            [
                ['test', 'another-test'],
                ['test', 'https://example.com'],
                ['testns' => 'https://example.com'],
                null,

                false // namespace name mismatch in list
            ],
            [
                ['test', 'another-test'],
                ['testns:test', 'https://example.com'],
                ['testns' => 'https://example.com'],
                null,

                false // the list counts, not the document
            ],
            [
                ['testns:test', 'another-test'],
                ['test', 'https://example.com'],
                ['testns' => 'https://example.com'],
                null,

                'testns:test' // correct namespaced configuration
            ],
            [
                ['testns:test', 'another-test'],
                ['testns:test', 'https://example.com'],
                ['testns' => 'https://example.com'],
                null,

                'testns:test' // namespace in document does not matter
            ],
            [
                ['testns:test', 'another-test'],
                ['customns:test', 'https://example.com'],
                ['testns' => 'https://example.com'],
                null,

                'testns:test' // namespace in document does not matter
            ],
            [
                ['testns:test', 'another-test'],
                ['testns:test', null],
                ['testns' => 'https://example.com'],
                null,

                'testns:test' // namespace not defined in document
            ],
            [
                ['testns:test', 'another-test'],
                ['testns:test', null],
                true,
                null,

                'testns:test' // all namespaces allowed, local name check
            ],
            [
                ['testns:test', 'another-test'],
                ['testns:test', 'https://example.com'],
                true,
                null,

                false // local name check fails because node has namespace
            ],

            // special `xml:` namespace
            [
                ['xml:space'],
                ['space', 'http://www.w3.org/XML/1998/namespace'],
                true,
                null,

                'xml:space' // exact match
            ],
            [
                ['xml:space'],
                ['space', 'http://www.w3.org/XML/1998/namespace'],
                [],
                null,

                'xml:space' // exact match even though namespace is not configured
            ],
            [
                ['xml:space'],
                ['space', 'http://www.w3.org/XML/1998/namespace'],
                ['xml' => 'http://www.w3.org/XML/1998/namespace'],
                null,

                'xml:space' // exact match with defined namespace
            ],
            [
                ['xml:space'],
                ['space', 'http://www.w3.org/XML/1998/namespace'],
                ['xml' => 'http://example.com/this-is-not-legal'],
                null,

                'xml:space' // exact match with different namespace
            ],
            [
                ['xml:space'],
                ['space', 'https://example.com/this-is-not-legal'],
                true,
                null,

                false // wrong namespace
            ],
            [
                ['xml:space'],
                ['space', ''],
                true,
                null,

                false // no namespace
            ],
            [
                ['xlink:space'],
                ['space', 'http://www.w3.org/XML/1998/namespace'],
                true,
                null,

                false // configuration with different namespace
            ],
            [
                ['xlink:space'],
                ['space', 'http://www.w3.org/XML/1998/namespace'],
                ['xlink' => 'http://www.w3.org/1999/xlink'],
                null,

                false // configuration with different namespace
            ],
            [
                ['space'],
                ['space', 'http://www.w3.org/XML/1998/namespace'],
                true,
                null,

                false // configuration without namespace
            ],

            // custom compare function
            [
                ['html', 'bodY'],
                ['BoDy', ''],
                [],
                function ($expected, $real): bool {
                    return strtolower($expected) === strtolower($real);
                },

                'bodY'
            ],
            [
                ['html', 'bodY'],
                ['BoDy', ''],
                true,
                function ($expected, $real): bool {
                    return strtolower($expected) === strtolower($real);
                },

                'bodY'
            ],
        ];
    }

    /**
     * @dataProvider listContainsNameProvider
     * @covers ::listContainsName
     */
    public function testListContainsName(array $list, array $node, $allowedNamespaces, ?Closure $compare, $expected)
    {
        [$nodeName, $nodeNS] = $node;
        if ($nodeNS !== null) {
            $element = new DOMElement($nodeName, null, $nodeNS);
        } else {
            $element = (new DOMDocument())->createElement($nodeName);
        }

        $options = ['allowedNamespaces' => $allowedNamespaces];

        $this->assertSame($expected, Dom::listContainsName($list, $element, $options, $compare));
    }

    /**
     * @covers ::remove
     */
    public function testRemove()
    {
        $dom = new Dom('<p>Test <strong id="strong">Test test</strong>!</p>', 'HTML');

        Dom::remove($dom->document()->getElementById('strong'));
        $this->assertSame('<p>Test !</p>', $dom->toString());
    }

    /**
     * @covers ::query
     */
    public function testQuery()
    {
        $dom = new Dom('<span>Test <span>Test test</span>!</span>', 'HTML');

        // global query
        $node = $dom->query('descendant::span')[0];
        $this->assertSame('<span>Test <span>Test test</span>!</span>', $dom->document()->saveHtml($node));

        // query within a context node
        $node = $dom->query('descendant::span', $node)[0];
        $this->assertSame('<span>Test test</span>', $dom->document()->saveHtml($node));
    }

    public function sanitizeProvider(): array
    {
        return [
            // defaults
            [
                '<p>This <strong id="test">is a test</strong>!</p>',
                [],

                '<p>This <strong id="test">is a test</strong>!</p>',
                []
            ],
            [
                '<a href="https://getkirby.com/test">Link</a>',
                [],

                '<a href="https://getkirby.com/test">Link</a>',
                []
            ],
            [
                '<p style="background: url(https://getkirby.com/test)">Lorem ipsum</p>',
                [],

                '<p style="background: url(https://getkirby.com/test)">Lorem ipsum</p>',
                []
            ],
            [
                '<img src="data:image/jpeg;base64,test"/>',
                [],

                '<img src="data:image/jpeg;base64,test"/>',
                []
            ],
            [
                "<p>\n<a href='javascript:alert()'>Link</a>\n</p>",
                [],

                "<p>\n<a>Link</a>\n</p>",
                ['The URL is not allowed in attribute "href" (line 2): Unknown URL type']
            ],
            [
                "<p>\n<img src='javascript:alert()'/>\n</p>",
                [],

                "<p>\n<img/>\n</p>",
                ['The URL is not allowed in attribute "src" (line 2): Unknown URL type']
            ],
            [
                '<a xmlns:xlink="https://example.com" xlink:href="https://getkirby.com">Link</a>',
                [],

                '<a xmlns:xlink="https://example.com" xlink:href="https://getkirby.com">Link</a>',
                []
            ],
            [
                '<a xlink:href="javascript:alert()">Link</a>',
                [],

                '<a>Link</a>',
                ['The URL is not allowed in attribute "xlink:href" (line 1): Unknown URL type']
            ],
            [
                '<a xmlns:xlink="https://example.com" xlink:href="javascript:alert()">Link</a>',
                [],

                '<a xmlns:xlink="https://example.com">Link</a>',
                ['The URL is not allowed in attribute "xlink:href" (line 1): Unknown URL type']
            ],
            [
                '<p style="background: url(javascript:alert())">Lorem ipsum</p>',
                [],

                '<p>Lorem ipsum</p>',
                ['The URL is not allowed in attribute "style" (line 1): Unknown URL type']
            ],
            [
                '<?xml-stylesheet href="stylesheet.css"?><p>This is a test</p>',
                [],

                "<?xml-stylesheet href=\"stylesheet.css\"?>\n<p>This is a test</p>",
                []
            ],

            // allowedAttrPrefixes
            [
                '<p aria-label="Test" data-test="Test">This is a test</p>',
                [
                    'allowedAttrPrefixes' => ['aria-'],
                ],

                '<p aria-label="Test" data-test="Test">This is a test</p>',
                []
            ],
            [
                '<p aria-label="Test" data-test="Test">This is a test</p>',
                [
                    'allowedAttrPrefixes' => ['aria-'],
                    'allowedAttrs'        => [],
                ],

                '<p aria-label="Test">This is a test</p>',
                ['The "data-test" attribute (line 1) is not allowed: Not included in the global allowlist']
            ],

            // allowedAttrs
            [
                '<p class="test" onload="alert()">This is a test</p>',
                [
                    'allowedAttrs' => ['class', 'on'],
                ],

                '<p class="test">This is a test</p>',
                ['The "onload" attribute (line 1) is not allowed: Not included in the global allowlist']
            ],

            // allowedDataUris
            [
                "<html>\n<img class='jpeg' src='data:image/jpeg;base64,test'/>\n<img class='png' src='data:image/png;base64,test'/>\n</html>",
                [
                    'allowedDataUris' => ['data:image/jpeg'],
                ],

                "<html>\n<img class=\"jpeg\" src=\"data:image/jpeg;base64,test\"/>\n<img class=\"png\"/>\n</html>",
                ['The URL is not allowed in attribute "src" (line 3): Invalid data URI']
            ],

            // allowedDomains
            [
                '<a href="https://getkirby.com/test" src="http://example.com/">Link</a>',
                [
                    'allowedDomains' => ['getkirby.com']
                ],

                '<a href="https://getkirby.com/test">Link</a>',
                ['The URL is not allowed in attribute "src" (line 1): The hostname "example.com" is not allowed']
            ],

            // allowedNamespaces
            [
                '<p class="test">Lorem ipsum</p>',
                [
                    'allowedNamespaces' => ['' => 'https://example.com/test', 'xlink' => 'http://www.w3.org/1999/xlink']
                ],

                '<p class="test">Lorem ipsum</p>',
                []
            ],
            [
                '<p xmlns:test="https://example.com/test" xmlns:mylink="http://www.w3.org/1999/xlink" id="p" test:class="test">Lorem ipsum</p>',
                [
                    'allowedNamespaces' => ['' => 'https://example.com/test', 'xlink' => 'http://www.w3.org/1999/xlink']
                ],

                '<p xmlns:test="https://example.com/test" xmlns:mylink="http://www.w3.org/1999/xlink" id="p" test:class="test">Lorem ipsum</p>',
                []
            ],
            [
                '<p xmlns:test="https://example.com/" xmlns:mylink="http://www.w3.org/1999/xlink">Lorem ipsum</p>',
                [
                    'allowedNamespaces' => ['' => 'https://example.com/test', 'xlink' => 'http://www.w3.org/1999/xlink']
                ],

                '<p xmlns:mylink="http://www.w3.org/1999/xlink">Lorem ipsum</p>',
                ['The namespace "https://example.com/" is not allowed (around line 1)']
            ],
            [
                '<p xmlns:test="https://example.com/test" aria-label="p" test:aria-role="test">Lorem ipsum</p>',
                [
                    'allowedAttrs' => [],
                    'allowedAttrPrefixes' => ['aria-'],
                    'allowedNamespaces' => ['' => 'https://example.com/test']
                ],

                '<p xmlns:test="https://example.com/test" aria-label="p" test:aria-role="test">Lorem ipsum</p>',
                []
            ],
            [
                '<a xmlns:test="https://example.com/test" aria-label="p" test:aria-role="test">Link</a>',
                [
                    'allowedAttrs' => [],
                    'allowedAttrPrefixes' => ['namespace:aria-'],
                    'allowedNamespaces' => ['namespace' => 'https://example.com/test']
                ],

                '<a xmlns:test="https://example.com/test" test:aria-role="test">Link</a>',
                ['The "aria-label" attribute (line 1) is not allowed: Not included in the global allowlist']
            ],
            [
                '<p xmlns:test="https://example.com/test" xmlns:mylink="http://www.w3.org/1999/xlink" id="p" test:class="test">Lorem ipsum</p>',
                [
                    'allowedAttrs' => ['class', 'id'],
                    'allowedNamespaces' => ['' => 'https://example.com/test', 'xlink' => 'http://www.w3.org/1999/xlink']
                ],

                '<p xmlns:test="https://example.com/test" xmlns:mylink="http://www.w3.org/1999/xlink" id="p" test:class="test">Lorem ipsum</p>',
                []
            ],
            [
                '<a xmlns:mylink="http://www.w3.org/1999/xlink" mylink:href="https://getkirby.com">Link</a>',
                [
                    'allowedAttrs' => ['xlink:href'],
                    'allowedNamespaces' => ['' => 'https://example.com/test', 'xlink' => 'http://www.w3.org/1999/xlink']
                ],

                '<a xmlns:mylink="http://www.w3.org/1999/xlink" mylink:href="https://getkirby.com">Link</a>',
                []
            ],
            [
                '<a xmlns:mylink="http://www.w3.org/1999/xlink" mylink:test="https://getkirby.com">Link</a>',
                [
                    'allowedAttrs' => ['xlink:href'],
                    'allowedNamespaces' => ['' => 'https://example.com/test', 'xlink' => 'http://www.w3.org/1999/xlink']
                ],

                '<a xmlns:mylink="http://www.w3.org/1999/xlink">Link</a>',
                ['The "mylink:test" attribute (line 1) is not allowed: Not included in the global allowlist']
            ],
            [
                '<a xmlns:mylink="http://www.w3.org/1999/xlink" mylink:href="https://getkirby.com" mylink:test="https://getkirby.com">Link</a>',
                [
                    'allowedAttrs' => [],
                    'allowedNamespaces' => ['xlink' => 'http://www.w3.org/1999/xlink'],
                    'allowedTags' => ['a' => ['xlink:href']]
                ],

                '<a xmlns:mylink="http://www.w3.org/1999/xlink" mylink:href="https://getkirby.com">Link</a>',
                ['The "mylink:test" attribute (line 1) is not allowed: Not allowed by the "a" element']
            ],
            [
                '<xml xmlns:test="https://example.com/test"><a>A</a><test:b>B</test:b></xml>',
                [
                    'allowedNamespaces' => ['test' => 'https://example.com/test'],
                    'allowedTags' => ['xml' => true, 'a' => true, 'test:b' => true]
                ],

                '<xml xmlns:test="https://example.com/test"><a>A</a><test:b>B</test:b></xml>',
                []
            ],
            [
                '<xml xmlns="https://example.com/test"><a>A</a></xml>',
                [
                    'allowedNamespaces' => ['test' => 'https://example.com/test'],
                    'allowedTags' => ['test:xml' => true, 'test:a' => true]
                ],

                '<xml xmlns="https://example.com/test"><a>A</a></xml>',
                []
            ],
            [
                '<xml xmlns="https://example.com/test"><a>A</a></xml>',
                [
                    'allowedNamespaces' => ['test' => 'https://example.com/test'],
                    'allowedTags' => ['xml' => true, 'test:a' => true]
                ],

                '<a xmlns="https://example.com/test">A</a>',
                ['The "xml" element (line 1) is not allowed, but its children can be kept']
            ],
            [
                '<xml xmlns:test="https://example.com/test"><a>A</a><test:b>B</test:b></xml>',
                [
                    'allowedNamespaces' => ['test' => 'https://example.com/test'],
                    'allowedTags' => ['xml' => true, 'a' => true, 'b' => true]
                ],

                '<xml xmlns:test="https://example.com/test"><a>A</a></xml>',
                ['The "test:b" element (line 1) is not allowed, but its children can be kept']
            ],
            [
                '<xml xmlns:test="https://example.com/test"><a>A</a></xml>',
                [
                    'allowedNamespaces' => ['test' => 'https://example.com/test'],
                    'allowedTags' => ['xml' => true, 'test:a' => true]
                ],

                '<xml xmlns:test="https://example.com/test"/>',
                ['The "a" element (line 1) is not allowed, but its children can be kept']
            ],
            [
                '<a xmlns:mylink="http://www.w3.org/1999/xlink" href="javascript:" mylink:href="javascript:" mylink:test="javascript:">Link</a>',
                [
                    'allowedNamespaces' => ['xlink' => 'http://www.w3.org/1999/xlink'],
                    'urlAttrs' => ['href', 'xlink:test']
                ],

                '<a xmlns:mylink="http://www.w3.org/1999/xlink" mylink:href="javascript:">Link</a>',
                [
                    'The URL is not allowed in attribute "href" (line 1): Unknown URL type',
                    'The URL is not allowed in attribute "mylink:test" (line 1): Unknown URL type'
                ]
            ],
            [
                '<a xmlns:mylink="http://www.w3.org/1999/xlink" href="javascript:" mylink:href="javascript:" mylink:test="javascript:">Link</a>',
                [
                    'allowedNamespaces' => ['xlink' => 'http://www.w3.org/1999/xlink'],
                    'urlAttrs' => ['href', 'xlink:href']
                ],

                '<a xmlns:mylink="http://www.w3.org/1999/xlink" mylink:test="javascript:">Link</a>',
                [
                    'The URL is not allowed in attribute "href" (line 1): Unknown URL type',
                    'The URL is not allowed in attribute "mylink:href" (line 1): Unknown URL type'
                ]
            ],
            [
                '<xml xmlns:test="https://example.com/test"><a>A</a></xml>',
                [
                    'allowedNamespaces' => ['test' => 'https://example.com/test'],
                    'disallowedTags' => ['a']
                ],

                '<xml xmlns:test="https://example.com/test"/>',
                ['The "a" element (line 1) is not allowed']
            ],
            [
                '<xml xmlns:test="https://example.com/test"><a>A</a></xml>',
                [
                    'allowedNamespaces' => ['test' => 'https://example.com/test'],
                    'disallowedTags' => ['test:a']
                ],

                '<xml xmlns:test="https://example.com/test"><a>A</a></xml>',
                []
            ],
            [
                '<xml xmlns:namespace="https://example.com/test"><namespace:a>A</namespace:a></xml>',
                [
                    'allowedNamespaces' => ['test' => 'https://example.com/test'],
                    'disallowedTags' => ['test:a']
                ],

                '<xml xmlns:namespace="https://example.com/test"/>',
                ['The "namespace:a" element (line 1) is not allowed']
            ],
            [
                '<xml xmlns:namespace="https://example.com/test"><namespace:a>A</namespace:a></xml>',
                [
                    'allowedNamespaces' => ['' => 'https://example.com/test'],
                    'disallowedTags' => ['a']
                ],

                '<xml xmlns:namespace="https://example.com/test"/>',
                ['The "namespace:a" element (line 1) is not allowed']
            ],

            // allowedPIs
            [
                '<?xml-stylesheet href="stylesheet.css"?><?invalid-instruction href="https://malicious.com"?><p>This is a test</p>',
                [
                    'allowedPIs' => ['xml-stylesheet']
                ],

                "<?xml-stylesheet href=\"stylesheet.css\"?>\n<p>This is a test</p>",
                ['The "invalid-instruction" processing instruction (line 1) is not allowed']
            ],

            // allowedTags
            [
                '<xml><a>A</a><b>B</b></xml>',
                [
                    'allowedTags' => ['xml' => true, 'a' => true]
                ],

                '<xml><a>A</a></xml>',
                ['The "b" element (line 1) is not allowed, but its children can be kept']
            ],
            [
                "<xml id='xml' class='test'>\n<a id='a' class='test'>A</a>\n</xml>",
                [
                    'allowedAttrs' => ['id'],
                    'allowedTags' => ['xml' => true, 'a' => false]
                ],

                "<xml id=\"xml\">\n<a>A</a>\n</xml>",
                [
                    'The "class" attribute (line 1) is not allowed: Not included in the global allowlist',
                    'The "id" attribute (line 2) is not allowed: The "a" element does not allow attributes',
                    'The "class" attribute (line 2) is not allowed: The "a" element does not allow attributes'
                ]
            ],
            [
                "<xml aria-role='xml' class='test'>\n<a aria-role='a' class='test'>A</a>\n</xml>",
                [
                    'allowedAttrs' => [],
                    'allowedAttrPrefixes' => ['aria-'],
                    'allowedTags' => ['xml' => true, 'a' => false]
                ],

                "<xml aria-role=\"xml\">\n<a>A</a>\n</xml>",
                [
                    'The "class" attribute (line 1) is not allowed: Not included in the global allowlist',
                    'The "aria-role" attribute (line 2) is not allowed: The "a" element does not allow attributes',
                    'The "class" attribute (line 2) is not allowed: The "a" element does not allow attributes'
                ]
            ],
            [
                '<xml><a class="test" xmlns="https://example.com/test"><b>B1</b><b>B2</b></a></xml>',
                [
                    'allowedTags' => ['xml' => true, 'b' => true]
                ],

                '<xml><b xmlns="https://example.com/test">B1</b><b xmlns="https://example.com/test">B2</b></xml>',
                ['The "a" element (line 1) is not allowed, but its children can be kept']
            ],

            // attrCallback
            [
                '<xml a="A" b="B"/>',
                [
                    'attrCallback' => function (DOMAttr $attr): void {
                        // no return value
                    }
                ],

                '<xml a="A" b="B"/>',
                []
            ],
            [
                '<xml a="A" b="B"/>',
                [
                    'attrCallback' => function (DOMAttr $attr): array {
                        if ($attr->nodeName === 'b') {
                            $attr->ownerElement->removeAttributeNode($attr);
                            return [new InvalidArgumentException('The "b" attribute is not allowed')];
                        }

                        return [];
                    }
                ],

                '<xml a="A"/>',
                ['The "b" attribute is not allowed']
            ],

            // disallowedTags
            [
                '<xml><a>A1</a><disallowed class="test"><a class="test">A2</a></disallowed></xml>',
                [
                    'disallowedTags' => ['disallowed']
                ],

                '<xml><a>A1</a></xml>',
                ['The "disallowed" element (line 1) is not allowed']
            ],
            [
                '<xml><a>A1</a><disAllowed class="test"><a class="test">A2</a></disAllowed></xml>',
                [
                    'disallowedTags' => ['DISallowed']
                ],

                '<xml><a>A1</a></xml>',
                ['The "disAllowed" element (line 1) is not allowed']
            ],

            // doctype defaults and doctypeCallback
            [
                '<!DOCTYPE xml><xml/>',
                [],

                "<!DOCTYPE xml>\n<xml/>",
                []
            ],
            [
                '<!DOCTYPE xml PUBLIC "SOMETHING" "https://malicious.com/something.dtd"><xml/>',
                [],

                '<xml/>',
                ['The doctype must not reference external files']
            ],
            [
                '<!DOCTYPE xml SYSTEM "https://malicious.com/something.dtd"><xml/>',
                [],

                '<xml/>',
                ['The doctype must not reference external files']
            ],
            [
                '<!DOCTYPE xml [<!ENTITY lol "lol">]><xml/>',
                [],

                '<xml/>',
                ['The doctype must not define a subset']
            ],
            [
                '<!DOCTYPE svg><xml/>',
                [
                    'doctypeCallback' => function (DOMDocumentType $doctype): void {
                        throw new InvalidArgumentException('The "' . $doctype->name . '" doctype is not allowed');
                    }
                ],

                '<xml/>',
                ['The "svg" doctype is not allowed']
            ],

            // elementCallback
            [
                '<xml><a class="a">A</a><b class="b">B</b></xml>',
                [
                    'elementCallback' => function (DOMElement $element): void {
                        // no return value
                    }
                ],

                '<xml><a class="a">A</a><b class="b">B</b></xml>',
                []
            ],
            [
                '<xml><a class="a">A</a><b class="b">B</b></xml>',
                [
                    'elementCallback' => function (DOMElement $element): array {
                        if ($element->nodeName === 'b') {
                            Dom::remove($element);
                            return [new InvalidArgumentException('The "b" element is not allowed')];
                        }

                        return [];
                    }
                ],

                '<xml><a class="a">A</a></xml>',
                ['The "b" element is not allowed']
            ],

            // urlAttrs
            [
                '<a class="javascript:alert()" href="javascript:alert()"/>',
                [
                    'urlAttrs' => []
                ],

                '<a class="javascript:alert()" href="javascript:alert()"/>',
                []
            ],
            [
                '<a class="javascript:alert()" href="javascript:alert()"/>',
                [
                    'urlAttrs' => ['href']
                ],

                '<a class="javascript:alert()"/>',
                ['The URL is not allowed in attribute "href" (line 1): Unknown URL type']
            ]
        ];
    }

    /**
     * @dataProvider sanitizeProvider
     * @covers ::sanitize
     * @covers ::sanitizeAttr
     * @covers ::sanitizeDoctype
     * @covers ::sanitizeElement
     * @covers ::sanitizePI
     * @covers ::validateDoctype
     */
    public function testSanitize(string $code, array $options, string $expectedCode, array $expectedErrors)
    {
        $dom    = new Dom($code, 'XML');
        $errors = $dom->sanitize($options);

        $this->assertSame($expectedErrors, array_map(function ($error) {
            return $error->getMessage();
        }, $errors));
        $this->assertSame($expectedCode, $dom->toString());
    }

    /**
     * @covers ::sanitize
     * @covers ::sanitizeDoctype
     * @covers ::validateDoctype
     */
    public function testSanitizeDoctypeCallbackException()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('This exception is not caught as validation error');

        $dom = new Dom('<!DOCTYPE xml><xml/>', 'XML');
        $dom->sanitize([
            'doctypeCallback' => function (DOMDocumentType $doctype): void {
                throw new \InvalidArgumentException('This exception is not caught as validation error');
            }
        ]);
    }

    /**
     * @covers ::unwrap
     */
    public function testUnwrap()
    {
        $dom = new Dom('<body><p>This is a test</p><invalid>And this is <p>Awesome<strong>!</strong></p> but contains text</invalid></body>', 'HTML');

        $node = $dom->document()->getElementsByTagName('invalid')[0];
        Dom::unwrap($node);

        $this->assertSame('<body><p>This is a test</p><p>Awesome<strong>!</strong></p></body>', $dom->toString());
    }
}
