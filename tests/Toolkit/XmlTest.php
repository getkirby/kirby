<?php

namespace Kirby\Toolkit;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Toolkit\Xml
 */
class XmlTest extends TestCase
{
	/**
	 * @covers       ::attr
	 * @dataProvider attrProvider
	 */
	public function testAttr($input, $value, $expected)
	{
		$this->assertSame($expected, Xml::attr($input, $value));
	}

	public function attrProvider()
	{
		return [
			[[],                         null,  ''],
			[['B' => 'b', 'A' => 'a'],   null,  'A="a" B="b"'],
			[['B' => 'b', 'A' => 'a'],   true,  'A="a" B="b"'],
			[['B' => 'b', 'A' => 'a'],   false, 'B="b" A="a"'],
			[['a' => 'a', 'b' => true],  null,  'a="a" b="b"'],
			[['a' => 'a', 'b' => ' '],   null,  'a="a" b=""'],
			[['a' => 'a', 'b' => false], null,  'a="a"'],
			[['a' => 'a', 'b' => null],  null,  'a="a"'],
			[['a' => 'a', 'b' => []],    null,  'a="a"'],
			[['a', 'b' => true],         null,  'a="a" b="b"']
		];
	}

	/**
	 * @covers ::attr
	 */
	public function testAttrArrayValue()
	{
		$result = Xml::attr('a', ['a', 'b']);
		$this->assertSame('a="a b"', $result);

		$result = Xml::attr('a', ['a', 1]);
		$this->assertSame('a="a 1"', $result);

		$result = Xml::attr('a', ['a', null]);
		$this->assertSame('a="a"', $result);

		$result = Xml::attr('a', ['value' => '&', 'escape' => true]);
		$this->assertSame('a="&#38;"', $result);

		$result = Xml::attr('a', ['value' => '&', 'escape' => false]);
		$this->assertSame('a="&"', $result);
	}

	/**
	 * @covers ::parse
	 * @covers ::simplify
	 * @covers ::create
	 */
	public function testParseSimplifyCreate()
	{
		$this->assertSame('<name>Homer</name>', Xml::create('Homer', 'name', false));
		$this->assertSame('<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL . '<name>Homer</name>', Xml::create('Homer', 'name', true));

		$this->assertSame('  <name>Homer</name>', Xml::create('Homer', 'name', false, '  ', 1));
		$this->assertSame('    <name>Homer</name>', Xml::create('Homer', 'name', false, '    ', 1));
		$this->assertSame('    <name>Homer</name>', Xml::create('Homer', 'name', false, '  ', 2));

		$fixtures = __DIR__ . '/fixtures/xml';
		$data = [
			'@name'       => 'contact',
			'@attributes' => ['type' => 'husband'],
			'@value'      => 'Homer'
		];
		$this->assertSame($data, Xml::parse(file_get_contents($fixtures . '/contact.xml')));
		$this->assertStringEqualsFile($fixtures . '/contact.xml', Xml::create($data, 'contact'));

		$data = [
			'@name' => 'contacts',
			'contact' => $contacts = [
				[
					'@attributes' => [
						'type' => 'husband'
					],
					'@value' => 'Homer'
				],
				[
					'@attributes' => [
						'type' => 'daughter'
					],
					'@value' => 'Lisa'
				]
			]
		];
		$this->assertSame($data, Xml::parse(file_get_contents($fixtures . '/contacts.xml')));
		$this->assertStringEqualsFile($fixtures . '/contacts_nowrapper.xml', Xml::create($contacts, 'contact', false));
		$this->assertStringEqualsFile($fixtures . '/contacts.xml', Xml::create($data, 'contacts'));

		$data = [
			'@name' => 'simpsons',
			'@namespaces' => [
				'' => 'https://example.com/simpsons',
				'simpson' => 'https://example.com/simpson',
				'unused' => 'https://example.com/unused'
			],
			'simpson' => [
				[
					'@attributes' => [
						'type' => 'father'
					],
					'name' => 'Homer'
				],
				[
					'@attributes' => [
						'type' => 'mother'
					],
					'name' => 'Marge',
					'simpson:contacts' => [
						'simpson:contact' => [
							'@attributes' => [
								'simpson:type' => 'husband'
							],
							'@value' => 'Homer',
						]
					]
				]
			]
		];
		$this->assertSame($data, Xml::parse(file_get_contents($fixtures . '/simpsons.xml')));
		$this->assertStringEqualsFile($fixtures . '/simpsons.xml', Xml::create($data, 'invalid'));
		$this->assertStringEqualsFile($fixtures . '/simpsons_4spaces.xml', Xml::create($data, 'invalid', true, '    '));

		unset($data['@name']);
		$this->assertStringEqualsFile($fixtures . '/simpsons.xml', Xml::create($data, 'simpsons'));

		$this->assertNull(Xml::parse('<this>is invalid</that>'));
	}

	/**
	 * @covers ::parse
	 */
	public function testParseEntities()
	{
		$xml   = '<!DOCTYPE d [<!ENTITY e "bar">]><x>this is a file: foo &e; (with entities)</x>';
		$array = Xml::parse($xml);

		$this->assertSame([
			'@name' => 'x',
			'@value' => 'this is a file: foo bar (with entities)'
		], $array);
	}

	/**
	 * @covers ::parse
	 */
	public function testParseRecursiveEntities()
	{
		$xml = file_get_contents(__DIR__ . '/fixtures/xml/billion-laughs.xml');
		$this->assertNull(Xml::parse($xml));
	}

	/**
	 * @covers ::parse
	 */
	public function testParseXXE()
	{
		$xml   = '<!DOCTYPE d [<!ENTITY e SYSTEM "' . __FILE__ . '">]><x>this is a file: &e; with an XXE vulnerability</x>';
		$array = Xml::parse($xml);

		$this->assertSame([
			'@name' => 'x',
			'@value' => 'this is a file:  with an XXE vulnerability'
		], $array);
	}

	/**
	 * @covers ::encode
	 * @covers ::decode
	 */
	public function testEncodeDecode()
	{
		$expected = 'S&#252;per &#214;nenc&#339;ded &#223;tring';
		$this->assertSame($expected, Xml::encode('Süper Önencœded ßtring'));
		$this->assertSame('Süper Önencœded ßtring', Xml::decode($expected));

		$this->assertSame('S&#252;per Täst', Xml::encode('S&uuml;per Täst', false));

		$this->assertSame('', Xml::decode(''));
		$this->assertSame('', Xml::encode(''));
		$this->assertSame('', Xml::decode(null));
		$this->assertSame('', Xml::encode(null));
	}

	/**
	 * @covers ::entities
	 */
	public function testEntities()
	{
		$this->assertSame(Xml::$entities, Xml::entities());
	}

	/**
	 * @covers ::tag
	 */
	public function testTag()
	{
		$tag = Xml::tag('name', 'content');
		$this->assertSame('<name>content</name>', $tag);

		$tag = Xml::tag('name', 'content', [], '  ', 1);
		$this->assertSame('  <name>content</name>', $tag);

		$tag = Xml::tag('name', 'content', ['foo' => 'bar']);
		$this->assertSame('<name foo="bar">content</name>', $tag);

		$tag = Xml::tag('name', null, ['foo' => 'bar']);
		$this->assertSame('<name foo="bar" />', $tag);

		$tag = Xml::tag('name', 'String with <not> a tag & some text', ['foo' => 'bar']);
		$this->assertSame('<name foo="bar"><![CDATA[String with <not> a tag & some text]]></name>', $tag);

		$tag = Xml::tag('name', 'content', ['foo' => 'bar'], '  ', 1);
		$this->assertSame('  <name foo="bar">content</name>', $tag);

		$tag = Xml::tag('name', 'content', ['foo' => 'bar'], '    ', 1);
		$this->assertSame('    <name foo="bar">content</name>', $tag);

		$tag = Xml::tag('name', ['Test', 'Test2'], ['foo' => 'bar'], ' ', 2);
		$this->assertSame('  <name foo="bar">' . PHP_EOL . '   Test' . PHP_EOL . '   Test2' . PHP_EOL . '  </name>', $tag);
	}

	/**
	 * @covers       ::value
	 * @dataProvider valueProvider
	 */
	public function testValue($input, $expected)
	{
		$this->assertSame($expected, Xml::value($input));
	}

	public function valueProvider()
	{
		return [
			[true, 'true'],
			[false, 'false'],
			[1, '1'],
			[null, null],
			['', null],
			['<![CDATA[test]]>', '<![CDATA[test]]>'],
			['<![CDATA[String with <not> a tag & some text]]>', '<![CDATA[String with <not> a tag & some text]]>'],
			['test', 'test'],
			['String with <not> a tag & some text', '<![CDATA[String with <not> a tag & some text]]>'],
			['This is a <![CDATA[test]]> with CDATA', '<![CDATA[This is a <![CDATA[test]]]]><![CDATA[> with CDATA]]>'],
			['te]]>st', '<![CDATA[te]]]]><![CDATA[>st]]>'],
			['tö]]>st', '<![CDATA[tö]]]]><![CDATA[>st]]>']
		];
	}
}
