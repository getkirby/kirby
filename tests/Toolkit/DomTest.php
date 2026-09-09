<?php

namespace Kirby\Toolkit;

use Closure;
use DOMAttr;
use DOMDocument;
use DOMDocumentType;
use DOMElement;
use Kirby\AssertionFailedError;
use Kirby\Cms\App;
use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;

/**
 * @coversDefaultClass \Kirby\Toolkit\Dom
 */
class DomTest extends TestCase
{
	protected static $testClosures = [];

	public static function setUpBeforeClass(): void
	{
		// define static test closures for use in data providers because returning a closure
		// from a data provider breaks serialization when using PHPUnit process isolation
		static::$testClosures = [
			'listContainsName_customCompare1' => function ($expected, $real): bool {
				return strtolower($expected) === strtolower($real);
			},
			'listContainsName_customCompare2' => function ($expected, $real): bool {
				return strtolower($expected) === strtolower($real);
			},
			'serialize_attrCallback1' => function (DOMAttr $attr, array $options): void {
				// no return value
			},
			'serialize_attrCallback2' => function (DOMAttr $attr, array $options): array {
				if (is_a($options['attrCallback'], Closure::class) !== true) {
					throw new AssertionFailedError('Options provided to callback are not complete or invalid');
				}

				if ($attr->nodeName === 'b') {
					$attr->ownerElement->removeAttributeNode($attr);
					return [new InvalidArgumentException('The "b" attribute is not allowed')];
				}

				return [];
			},
			'sanitize_doctypeCallback' => function (DOMDocumentType $doctype, array $options): void {
				if (is_a($options['doctypeCallback'], Closure::class) !== true) {
					throw new AssertionFailedError('Options provided to callback are not complete or invalid');
				}

				throw new InvalidArgumentException('The "' . $doctype->name . '" doctype is not allowed');
			},
			'sanitize_elementCallback1' => function (DOMElement $element, array $options): void {
				// no return value
			},
			'sanitize_elementCallback2' => function (DOMElement $element, array $options): array {
				if (is_a($options['elementCallback'], Closure::class) !== true) {
					throw new AssertionFailedError('Options provided to callback are not complete or invalid');
				}

				if ($element->nodeName === 'b') {
					Dom::remove($element);
					return [new InvalidArgumentException('The "b" element is not allowed')];
				}

				return [];
			},
		];
	}

	public static function parseSaveProvider(): array
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

			// TODO: activate again, once it produces reliable results in
			// CI and all local setups
			// HTML snippet with syntax issue
			// [
			// 	'html',
			// 	'<p>This is <strong>important</strong!</p>',
			// 	'<p>This is <strong>important</strong>!</p>'
			// ],

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
	public function testParseSave(string $type, string $code, string|null $expected = null)
	{
		$dom = new Dom($code, $type);
		$this->assertSame($expected ?? $code, $dom->toString());
	}

	public static function parseSaveNormalizeProvider(): array
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

			// TODO: activate again, once it produces reliable results in
			// CI and all local setups
			// HTML snippet with syntax issue
			// [
			// 	'html',
			// 	'<p>This is <strong>important</strong!</p>',
			// 	'<html><body><p>This is <strong>important</strong>!</p></body></html>'
			// ],

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
	public function testParseSaveNormalize(string $type, string $code, string|null $expected = null)
	{
		$dom = new Dom($code, $type);
		$this->assertSame($expected ?? $code, $dom->toString(true));
	}

	/**
	 * @covers ::__construct
	 */
	public function testParseInvalid()
	{
		$this->expectException(InvalidArgumentException::class);
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

	public static function extractUrlsProvider(): array
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

			// @import string form
			[
				'@import "https://getkirby.com/style.css"',
				['https://getkirby.com/style.css']
			],
			[
				'@import \'https://getkirby.com/style.css\'',
				['https://getkirby.com/style.css']
			],
			[
				'@import   "  https://getkirby.com/style.css  "',
				['https://getkirby.com/style.css']
			],

			// @import without whitespace before the string
			[
				'@import"https://getkirby.com/style.css"',
				['https://getkirby.com/style.css']
			],

			// @import with a CSS comment instead of whitespace
			[
				'@import/**/"https://getkirby.com/style.css"',
				['https://getkirby.com/style.css']
			],

			// @import with a tab (stripped by the ASCII filter → becomes no-space)
			[
				"@import\t\"https://getkirby.com/style.css\"",
				['https://getkirby.com/style.css']
			],

			// @import url() form
			[
				'@import url("https://getkirby.com/style.css")',
				['https://getkirby.com/style.css']
			],

			// mixed url() and @import string form
			[
				'@import "https://getkirby.com/a.css"; text { background: url(https://getkirby.com/b.png); }',
				['https://getkirby.com/b.png', 'https://getkirby.com/a.css']
			],

			// `/*` inside a quoted string is not a CSS comment, but part of
			// the URL; it must not swallow the closing quote of the string
			// when a real comment follows later in the value
			[
				'@import "https://getkirby.com/style.css/*"; text {} /* comment */',
				['https://getkirby.com/style.css/*']
			],

			// URLs that legitimately contain `/*...*/` must not be rewritten
			[
				'@import "https://getkirby.com/a/*b*/c.css"',
				['https://getkirby.com/a/*b*/c.css']
			],
			[
				'text { background: url("https://getkirby.com/a/*b*/c.png") }',
				['https://getkirby.com/a/*b*/c.png']
			],

			// CSS escapes are decoded like the browser's tokenizer does
			[
				'text { background: url(\\2f\\2f malicious.com/a.png) }',
				['//malicious.com/a.png']
			],
			[
				'@import "\\2f\\2f malicious.com/a.css"',
				['//malicious.com/a.css']
			],
			[
				'text { background: url(\\/\\/malicious.com/a.png) }',
				['//malicious.com/a.png']
			],
			[
				'text { background: \\75rl(//malicious.com/a.png) }',
				['//malicious.com/a.png']
			],
			[
				'text { background: url(https:\\2f\\2fmalicious.com/a.png) }',
				['https://malicious.com/a.png']
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

	public static function isAllowedAttrProvider(): array
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
	 * @covers ::normalizeSanitizeOptions
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

	public static function isAllowedGlobalAttrProvider(): array
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
	 * @covers ::normalizeSanitizeOptions
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

	public static function isAllowedUrlProvider(): array
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

			// forbidden protocol-relative URL with leading whitespace
			// that the browser strips before it parses the URL
			[' //test', 'Protocol-relative URLs are not allowed'],
			["\t//test", 'Protocol-relative URLs are not allowed'],
			["\n//test", 'Protocol-relative URLs are not allowed'],
			["\r//test", 'Protocol-relative URLs are not allowed'],
			["\x00//test", 'Protocol-relative URLs are not allowed'],
			["\x0c//test", 'Protocol-relative URLs are not allowed'],
			["/\t/test", 'Protocol-relative URLs are not allowed'],

			// forbidden protocol-relative URL with backslashes
			// that the browser treats like forward slashes
			['/\\test', 'Protocol-relative URLs are not allowed'],
			['\\/test', 'Protocol-relative URLs are not allowed'],
			['\\\\test', 'Protocol-relative URLs are not allowed'],
			["\\\t/test", 'Protocol-relative URLs are not allowed'],

			// forbidden relative URL with whitespace inside the
			// `../` sequence that the browser strips
			["..\t/some/path", 'The ../ sequence is not allowed in relative URLs'],
			["..\n/some/path", 'The ../ sequence is not allowed in relative URLs'],
			[".\r./some/path", 'The ../ sequence is not allowed in relative URLs'],
			["some/..\t/../path", 'The ../ sequence is not allowed in relative URLs'],

			// forbidden relative URL with a percent-encoded dot segment
			// that the browser decodes before it resolves the path
			['%2e%2e/some/path', 'The ../ sequence is not allowed in relative URLs'],
			['%2E%2E/some/path', 'The ../ sequence is not allowed in relative URLs'],
			['.%2e/some/path', 'The ../ sequence is not allowed in relative URLs'],
			['%2e./some/path', 'The ../ sequence is not allowed in relative URLs'],
			['some/%2e%2e/path', 'The ../ sequence is not allowed in relative URLs'],

			// a percent-encoded dot that is not a dot segment is allowed
			['some%2epath/file.jpg', true],

			// forbidden URL type with leading whitespace
			[' javascript:alert()', 'Unknown URL type'],
			["java\tscript:alert()", 'Unknown URL type'],

			// forbidden URL when no domains are accepted, with leading whitespace
			[' https://getkirby.com', 'The hostname "getkirby.com" is not allowed', [
				'allowedDomains' => []
			]],

			// the browser ends the host at a backslash, so the
			// allowlist must be checked against that same host
			['https://malicious.com\\@getkirby.com', 'The hostname "malicious.com" is not allowed', [
				'allowedDomains' => ['getkirby.com']
			]],
			['https://getkirby.com\\@malicious.com', true, [
				'allowedDomains' => ['getkirby.com']
			]],

			// allowed values are unaffected by the normalization
			[' #test-fragment', true],
			[' some/path', true],
			['  ', true],
			[' mailto:test@getkirby.com', true],
			[' data:image/jpeg;base64,test', true, [
				'allowedDataUris' => [
					'data:image/jpeg;base64'
				]
			]],
		];
	}

	/**
	 * @dataProvider isAllowedUrlProvider
	 * @covers ::isAllowedUrl
	 * @covers ::normalizeSanitizeOptions
	 */
	public function testIsAllowedUrl(string $url, $expected, array $options = [])
	{
		$this->assertSame($expected, Dom::isAllowedUrl($url, $options));
	}

	public static function isAllowedUrlCmsProvider(): array
	{
		return [
			// allowed URL with site at the domain root
			['https://getkirby.com', '/some/path', false, true],

			// allowed URL with site at the domain root
			['/', '/some/path', false, true],

			// allowed URL with site in a subfolder
			['https://getkirby.com/some', '/some/path', false, true],

			// allowed URL with site in a subfolder
			['/some', '/some/path', false, true],

			// disallowed URL with site in a subfolder
			['https://getkirby.com/site', '/some/path', false, 'The URL points outside of the site index URL'],

			// generally disallowed URL with site in a subfolder (but allowed)
			['https://getkirby.com/site', '/some/path', true, true],

			// disallowed URL with site in a subfolder
			['/site', '/some/path', false, 'The URL points outside of the site index URL'],

			// generally disallowed URL with site in a subfolder (but allowed)
			['/site', '/some/path', true, true],

			// the index URL must match up to a path segment boundary
			['https://getkirby.com/site', '/sitemap.xml', false, 'The URL points outside of the site index URL'],
			['/site', '/sitemap.xml', false, 'The URL points outside of the site index URL'],

			// the index URL itself is allowed
			['/site', '/site', false, true],
			['/site', '/site/', false, true],

			// a query or fragment ends the path and must not
			// make the index URL itself fail the check
			['/site', '/site?q=kirby', false, true],
			['/site', '/site#contact', false, true],
			['/site', '/site/about?q=kirby', false, true],
			['/', '/?q=kirby', false, true],

			// but they must not open up the check either
			['/site', '/sitemap?q=kirby', false, 'The URL points outside of the site index URL'],
			['/site', '/sitemap#contact', false, 'The URL points outside of the site index URL'],

			// percent-encoded dot segments must not skip the traversal check
			['/site', '/site/%2e%2e/some/path', false, 'The ../ sequence is not allowed in relative URLs'],
			['/site', '/site/.%2e/some/path', false, 'The ../ sequence is not allowed in relative URLs'],

			// disallowed URL with directory traversal
			['https://getkirby.com/site', '/site/../some/path', false, 'The ../ sequence is not allowed in relative URLs'],

			// disallowed URL with directory traversal
			['/site', '/site/../some/path', false, 'The ../ sequence is not allowed in relative URLs'],

			// leading whitespace must not skip the site index URL check
			['https://getkirby.com/site', ' /some/path', false, 'The URL points outside of the site index URL'],
			['/site', "\t/some/path", false, 'The URL points outside of the site index URL'],

			// whitespace must not skip the directory traversal check
			['/site', "/site/..\t/some/path", false, 'The ../ sequence is not allowed in relative URLs'],
		];
	}

	/**
	 * @dataProvider isAllowedUrlCmsProvider
	 * @covers ::isAllowedUrl
	 */
	public function testIsAllowedUrlCms(string $indexUrl, string $url, bool $allowHostRelativeUrls, string|bool $expected)
	{
		new App([
			'urls' => [
				'index' => $indexUrl
			]
		]);

		$this->assertSame($expected, Dom::isAllowedUrl($url, compact('allowHostRelativeUrls')));
	}

	/**
	 * Conformance check against the URL test data of the
	 * web-platform-tests project, the reference corpus for the
	 * URL parser that every browser is measured against
	 *
	 * Whenever a browser resolves a URL to a host other than the one
	 * the site is served from, the sanitizer must not allow it. The
	 * opposite direction is deliberately not asserted, as Kirby blocks
	 * more than the browser on purpose (e.g. `../` sequences), which
	 * is the safe direction.
	 *
	 * @covers ::isAllowedUrl
	 */
	public function testIsAllowedUrlConformance(): void
	{
		$data = Data::read(__DIR__ . '/fixtures/urltestdata.json');

		foreach ($data['cases'] as $case) {
			new App([
				'urls' => [
					'index' => 'http://' . $case['site']
				]
			]);

			$this->assertNotSame(
				true,
				Dom::isAllowedUrl(
					url: $case['input'],
					options: ['allowedDomains' => [$case['site']]]
				),
				'The browser resolves ' . json_encode($case['input']) .
				' to ' . $case['resolved']
			);
		}
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

	public static function listContainsNameProvider(): array
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
				'listContainsName_customCompare1',

				'bodY'
			],
			[
				['html', 'bodY'],
				['BoDy', ''],
				true,
				'listContainsName_customCompare2',

				'bodY'
			],
		];
	}

	/**
	 * @dataProvider listContainsNameProvider
	 * @covers ::listContainsName
	 * @covers ::normalizeSanitizeOptions
	 */
	public function testListContainsName(array $list, array $node, $allowedNamespaces, string|null $compare, $expected)
	{
		if ($compare !== null) {
			$compare = static::$testClosures[$compare];
		}

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

	public static function sanitizeProvider(): array
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

			// a `>` in the data of an allow-listed PI ends the bogus
			// comment an HTML parser opens at `<?`, exposing live markup
			[
				'<?xml-stylesheet ><img src=x onerror=alert(1)>?><p>This is a test</p>',
				[
					'allowedPIs' => ['xml-stylesheet']
				],

				'<p>This is a test</p>',
				['The "xml-stylesheet" processing instruction (line 1) is not allowed']
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
					'attrCallback' => 'serialize_attrCallback1' // static test closure defined at the top of the file
				],

				'<xml a="A" b="B"/>',
				[]
			],
			[
				'<xml a="A" b="B"/>',
				[
					'attrCallback' => 'serialize_attrCallback2' // static test closure defined at the top of the file
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
					'doctypeCallback' => 'sanitize_doctypeCallback' // static test closure defined at the top of the file
				],

				'<xml/>',
				['The "svg" doctype is not allowed']
			],

			// elementCallback
			[
				'<xml><a class="a">A</a><b class="b">B</b></xml>',
				[
					'elementCallback' => 'sanitize_elementCallback1' // static test closure defined at the top of the file
				],

				'<xml><a class="a">A</a><b class="b">B</b></xml>',
				[]
			],
			[
				'<xml><a class="a">A</a><b class="b">B</b></xml>',
				[
					'elementCallback' => 'sanitize_elementCallback2' // static test closure defined at the top of the file
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
		// hydrate the closures in the options from the static closures
		foreach (['attrCallback', 'doctypeCallback', 'elementCallback'] as $name) {
			if (isset($options[$name]) === true) {
				$options[$name] = static::$testClosures[$options[$name]];
			}
		}

		$dom    = new Dom($code, 'XML');
		$errors = $dom->sanitize($options);

		$this->assertSame($expectedErrors, array_map(function ($error) {
			return $error->getMessage();
		}, $errors));
		$this->assertSame($expectedCode, $dom->toString());
	}

	/**
	 * @covers ::context
	 * @covers ::isBreakout
	 * @covers ::isIntegration
	 * @covers ::sanitize
	 * @covers ::sanitizeCharacterData
	 */
	public function testSanitizeCharacterData()
	{
		// helper that sanitizes with an allow-everything configuration so
		// only the character-data handling can alter the document
		$sanitize = function (string $code): array {
			$dom    = new Dom($code, 'XML');
			$errors = $dom->sanitize([]);

			return [
				$dom->toString(),
				array_map(fn ($error) => $error->getMessage(), $errors)
			];
		};

		// a comment whose data closes it early is removed: the tokenizer
		// ends it there, so the rest would re-parse as live markup
		$this->assertSame(
			['<root><title/></root>', ['The comment (line 1) is not allowed']],
			$sanitize('<root><title><!--><img src=x onerror=alert(1)>--></title></root>')
		);

		// the `<!--->` spelling closes the comment just the same
		$this->assertSame(
			['<root><g/></root>', ['The comment (line 1) is not allowed']],
			$sanitize('<root><g><!---><img src=x>--></g></root>')
		);

		// foreign content is no shelter for it either
		$this->assertSame(
			['<root><svg/></root>', ['The comment (line 1) is not allowed']],
			$sanitize('<root><svg><!--><img src=x>--></svg></root>')
		);

		// top-level comment closing early is removed as well
		$this->assertSame(
			['<root/>', ['The comment (line 1) is not allowed']],
			$sanitize('<!--><img src=x>--><root/>')
		);

		// a well-formed comment is inert in every context and stays put,
		// even when it holds markup
		$this->assertSame(
			['<root><g><!--<rect/>--></g></root>', []],
			$sanitize('<root><g><!--<rect/>--></g></root>')
		);

		// inside a raw text element the comment is never a comment, just
		// text, so an early close there cannot expose anything
		$this->assertSame(
			['<root><style><!--><img src=x>--></style></root>', []],
			$sanitize('<root><style><!--><img src=x>--></style></root>')
		);

		// outside foreign content `<![CDATA[` degrades to a comment that
		// ends at its first `>`, so the section is escaped into text
		$this->assertSame(
			[
				'<root>&gt;&lt;img src=x&gt;</root>',
				['The CDATA section (line 1) is not allowed']
			],
			$sanitize('<root><![CDATA[><img src=x>]]></root>')
		);

		// CDATA breaking out of a raw text element is escaped into text
		$this->assertSame(
			[
				'<root><style>&lt;/style&gt;&lt;img src=x&gt;</style></root>',
				['The CDATA section (line 1) is not allowed']
			],
			$sanitize('<root><style><![CDATA[</style><img src=x>]]></style></root>')
		);

		// ...but inside foreign content it stays a real CDATA section and
		// is kept untouched, whatever its data looks like
		$this->assertSame(
			['<root><svg><![CDATA[><img src=x>]]></svg></root>', []],
			$sanitize('<root><svg><![CDATA[><img src=x>]]></svg></root>')
		);

		// CDATA-wrapped content without a closing tag is kept in raw text
		$this->assertSame(
			['<root><style><![CDATA[.a > .b {}]]></style></root>', []],
			$sanitize('<root><style><![CDATA[.a > .b {}]]></style></root>')
		);

		// child elements of an HTML integration point are created in the
		// HTML namespace again, where `<![CDATA[` is only a bogus comment
		$this->assertSame(
			[
				'<root><svg><desc><g>&gt;&lt;img src=x&gt;</g></desc></svg></root>',
				['The CDATA section (line 1) is not allowed']
			],
			$sanitize('<root><svg><desc><g><![CDATA[><img src=x>]]></g></desc></svg></root>')
		);

		// ...but `<svg>` re-enters foreign content below one of them
		$this->assertSame(
			['<root><svg><desc><svg><![CDATA[><img src=x>]]></svg></desc></svg></root>', []],
			$sanitize('<root><svg><desc><svg><![CDATA[><img src=x>]]></svg></desc></svg></root>')
		);

		// the same for the MathML text integration points
		$this->assertSame(
			[
				'<root><math><mtext><b>&gt;&lt;img src=x&gt;</b></mtext></math></root>',
				['The CDATA section (line 1) is not allowed']
			],
			$sanitize('<root><math><mtext><b><![CDATA[><img src=x>]]></b></mtext></math></root>')
		);

		// HTML elements that break a parser out of foreign content leave
		// the whole subtree below them in the HTML namespace
		$this->assertSame(
			[
				'<root><svg><b>&gt;&lt;img src=x&gt;</b></svg></root>',
				['The CDATA section (line 1) is not allowed']
			],
			$sanitize('<root><svg><b><![CDATA[><img src=x>]]></b></svg></root>')
		);

		// raw text starts at the outermost such element, so this comment
		// needs the `</script>` and not the `</style>` to break out
		$this->assertSame(
			['<root><script><style/></script></root>', ['The comment (line 1) is not allowed']],
			$sanitize('<root><script><style><!--</script><img src=x>--></style></script></root>')
		);

		// a foreign `<style>` is no raw text element, so a comment below
		// it is a real comment that must not close itself early
		$this->assertSame(
			['<root><svg><style><desc><g/></desc></style></svg></root>', ['The comment (line 1) is not allowed']],
			$sanitize('<root><svg><style><desc><g><!--><img src=x>--></g></desc></style></svg></root>')
		);

		// an HTML `<style>` on the other hand reads everything below it
		// as text, so the nested `<svg>` never opens foreign content
		$this->assertSame(
			['<root><style><svg/></style></root>', ['The comment (line 1) is not allowed']],
			$sanitize('<root><style><svg><!--</style><img src=x>--></svg></style></root>')
		);

		// integration points only work inside their own foreign root, so
		// neither of these hands its children back to HTML content
		$this->assertSame(
			['<root><math><desc><style/></desc></math></root>', ['The comment (line 1) is not allowed']],
			$sanitize('<root><math><desc><style><!--><img src=x>--></style></desc></math></root>')
		);

		$this->assertSame(
			['<root><svg><mtext><style/></mtext></svg></root>', ['The comment (line 1) is not allowed']],
			$sanitize('<root><svg><mtext><style><!--><img src=x>--></style></mtext></svg></root>')
		);

		// `<annotation-xml>` only hands them back with an HTML encoding,
		// which turns the `<style>` below it into a raw text element
		$this->assertSame(
			[
				'<root><math><annotation-xml encoding="text/html"><style>&lt;/style&gt;&lt;img src=x&gt;</style></annotation-xml></math></root>',
				['The CDATA section (line 1) is not allowed']
			],
			$sanitize('<root><math><annotation-xml encoding="text/html"><style><![CDATA[</style><img src=x>]]></style></annotation-xml></math></root>')
		);

		$this->assertSame(
			['<root><math><annotation-xml><style/></annotation-xml></math></root>', ['The comment (line 1) is not allowed']],
			$sanitize('<root><math><annotation-xml><style><!--><img src=x>--></style></annotation-xml></math></root>')
		);
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
	 * @covers ::sanitize
	 * @covers ::unwrap
	 */
	public function testSanitizeElementsEvenWhenUnwrapped(): void
	{
		$html = '<body><wrapper><script>alert(1)</script><img src="x" onerror="alert(2)"></wrapper></body>';
		$dom  = new Dom($html, 'HTML');

		$dom->sanitize([
			'allowedTags' => [
				'body' => true,
				'img'  => ['src'],
			],
			'allowedAttrs'   => ['src'],
			'disallowedTags' => ['script'],
		]);

		$this->assertSame('<body><img src="x"></body>', $dom->toString());
	}

	/**
	 * @covers ::unwrap
	 */
	public function testUnwrap(): void
	{
		$dom = new Dom('<body><p>This is a test</p><invalid>And this is <p>Awesome<strong>!</strong></p> but contains text</invalid></body>', 'HTML');

		$node = $dom->document()->getElementsByTagName('invalid')[0];
		Dom::unwrap($node);

		$this->assertSame('<body><p>This is a test</p><p>Awesome<strong>!</strong></p></body>', $dom->toString());
	}
}
