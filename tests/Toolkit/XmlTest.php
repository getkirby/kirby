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
            [['B' => 'b', 'A' => 'a'],   null,  'a="a" b="b"'],
            [['B' => 'b', 'A' => 'a'],   true,  'a="a" b="b"'],
            [['B' => 'b', 'A' => 'a'],   false, 'b="b" a="a"'],
            [['a' => 'a', 'b' => true],  null,  'a="a" b="b"'],
            [['a' => 'a', 'b' => ' '],   null,  'a="a" b=""'],
            [['a' => 'a', 'b' => ''],    null,  'a="a"'],
            [['a' => 'a', 'b' => false], null,  'a="a"'],
            [['a' => 'a', 'b' => null],  null,  'a="a"'],
            [['a' => 'a', 'b' => []],    null,  'a="a"']
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

        $data = [
            '@name' => 'contact',
            '@attributes' => [
                'type' => 'husband'
            ],
            '@value' => 'Homer'
        ];
        $this->assertSame($data, Xml::parse(file_get_contents(__DIR__ . '/fixtures/xml/contact.xml')));
        $this->assertStringEqualsFile(__DIR__ . '/fixtures/xml/contact.xml', Xml::create($data, 'contact'));

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
        $this->assertSame($data, Xml::parse(file_get_contents(__DIR__ . '/fixtures/xml/contacts.xml')));
        $this->assertStringEqualsFile(__DIR__ . '/fixtures/xml/contacts_nowrapper.xml', Xml::create($contacts, 'contact', false));
        $this->assertStringEqualsFile(__DIR__ . '/fixtures/xml/contacts.xml', Xml::create($data, 'contacts'));

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
        $this->assertSame($data, Xml::parse(file_get_contents(__DIR__ . '/fixtures/xml/simpsons.xml')));
        $this->assertStringEqualsFile(__DIR__ . '/fixtures/xml/simpsons.xml', Xml::create($data, 'invalid'));
        $this->assertStringEqualsFile(__DIR__ . '/fixtures/xml/simpsons_4spaces.xml', Xml::create($data, 'invalid', true, '    '));

        unset($data['@name']);
        $this->assertStringEqualsFile(__DIR__ . '/fixtures/xml/simpsons.xml', Xml::create($data, 'simpsons'));

        $this->assertNull(Xml::parse('<this>is invalid</that>'));
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

        $tag = Xml::tag('name', 'content', null, '  ', 1);
        $this->assertSame('  <name>content</name>', $tag);

        $tag = Xml::tag('name', 'content', ['foo' => 'bar']);
        $this->assertSame('<name foo="bar">content</name>', $tag);

        $tag = Xml::tag('name', null, ['foo' => 'bar']);
        $this->assertSame('<name foo="bar" />', $tag);

        $tag = Xml::tag('name', 'Süper Önencœded ßtring', ['foo' => 'bar']);
        $this->assertSame('<name foo="bar"><![CDATA[Süper Önencœded ßtring]]></name>', $tag);

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
            ['<![CDATA[töst]]>', '<![CDATA[töst]]>'],
            ['test', 'test'],
            ['töst', '<![CDATA[töst]]>'],
            ['This is a <![CDATA[test]]> with CDATA', '<![CDATA[This is a <![CDATA[test]]]]><![CDATA[> with CDATA]]>'],
            ['te]]>st', '<![CDATA[te]]]]><![CDATA[>st]]>'],
            ['tö]]>st', '<![CDATA[tö]]]]><![CDATA[>st]]>']
        ];
    }
}
