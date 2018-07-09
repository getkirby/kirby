<?php

namespace Kirby\Cms;

use Kirby\Cms\App;
use Kirby\Data\Yaml;

class FieldMethodsTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
        new App();
    }

    public function field($value = '')
    {
        return new Field(null, 'test', $value);
    }

    public function testFieldMethodCombination()
    {
        $field = $this->field('test')->upper()->short(3);
        $this->assertEquals('TES…', $field->value());
    }

    public function testIsEmpty()
    {
        $this->assertTrue($this->field()->isEmpty());
    }

    public function testIsFalse()
    {
        $this->assertTrue($this->field('false')->isFalse());
        $this->assertTrue($this->field(false)->isFalse());
    }

    public function testIsNotEmpty()
    {
        $this->assertTrue($this->field('test')->isNotEmpty());
    }

    public function testIsTrue()
    {
        $this->assertTrue($this->field('true')->isTrue());
        $this->assertTrue($this->field(true)->isTrue());
    }

    public function testIsValid()
    {
        $this->assertTrue($this->field('mail@example.com')->isValid('email'));
        $this->assertTrue($this->field('https://example.com')->isValid('url'));
    }

    public function testToDataSplit()
    {
        $this->assertEquals(['a', 'b'], $this->field('a, b')->toData());
    }

    public function testToDataSplitWithDifferentSeparator()
    {
        $this->assertEquals(['a', 'b'], $this->field('a; b')->toData(';'));
    }

    public function testToDataYaml()
    {
        $data = ['a', 'b'];

        $this->assertEquals(['a', 'b'], $this->field(Yaml::encode($data))->toData('yaml'));
    }

    public function testToDataJson()
    {
        $data = ['a', 'b'];

        $this->assertEquals(['a', 'b'], $this->field(json_encode($data))->toData('json'));
    }

    public function testToBool()
    {
        $this->assertTrue($this->field('1')->toBool());
        $this->assertTrue($this->field('true')->toBool());
        $this->assertFalse($this->field('0')->toBool());
        $this->assertFalse($this->field('false')->toBool());
    }

    public function testToDate()
    {
        $field = $this->field('2012-12-12');
        $ts    = strtotime('2012-12-12');
        $date  = '12.12.2012';

        $this->assertEquals($ts, $field->toDate());
        $this->assertEquals($date, $field->toDate('d.m.Y'));

        $this->markTestIncomplete('test different date handler');
    }

    public function testToExcerpt()
    {
        $this->markTestIncomplete();
    }

    public function testToFile()
    {
        $this->markTestIncomplete();
    }

    public function testToFloat()
    {
        $field    = $this->field('1.2');
        $expected = 1.2;

        $this->assertEquals($expected, $field->toFloat());
    }

    public function testToInt()
    {
        $this->assertEquals(1, $this->field('1')->toInt());
        $this->assertTrue(is_int($this->field('1')->toInt()));
    }

    public function testToLink()
    {
        $this->markTestIncomplete();
    }

    public function testToPage()
    {
        $this->markTestIncomplete();
    }

    public function testToStructure()
    {
        $this->markTestIncomplete();
    }

    public function testToDefaultUrl()
    {
        $field    = $this->field('super/cool');
        $expected = '/super/cool';

        $this->assertEquals($expected, $field->toUrl());
    }

    public function testToCustomUrl()
    {
        $app = new App([
            'urls' => [
                'index' => 'https://getkirby.com'
            ]
        ]);

        $field    = $this->field('super/cool');
        $expected = 'https://getkirby.com/super/cool';

        $this->assertEquals($expected, $field->toUrl());
    }

    public function testToUser()
    {
        $this->markTestIncomplete();
    }

    public function testLength()
    {
        $this->assertEquals(3, $this->field('abc')->length());
    }

    public function testEscape()
    {
        $this->markTestIncomplete();
    }

    public function testHtml()
    {
        $this->assertEquals('&ouml;', $this->field('ö')->html());
    }

    public function testKirbytext()
    {
        $kirbytext = '(link: # text: Test)';
        $expected  = '<p><a href="#">Test</a></p>';

        $this->assertEquals($expected, $this->field($kirbytext)->kirbytext());
        $this->assertEquals($expected, $this->field($kirbytext)->kt());
    }

    public function testKirbytags()
    {
        $kirbytext = '(link: # text: Test)';
        $expected  = '<a href="#">Test</a>';

        $this->assertEquals($expected, $this->field($kirbytext)->kirbytags());
    }

    public function testLower()
    {
        $this->assertEquals('abc', $this->field('ABC')->lower());
    }

    public function testMarkdown()
    {
        $markdown = '**Test**';
        $expected = '<p><strong>Test</strong></p>';

        $this->assertEquals($expected, $this->field($markdown)->markdown());
    }

    public function testOr()
    {
        $this->markTestIncomplete();
    }

    public function testShort()
    {
        $this->assertEquals('abc…', $this->field('abcd')->short(3));
    }

    public function testSmartypants()
    {
        $text     = '"Test"';
        $expected = '&#8220;Test&#8221;';

        $this->assertEquals($expected, $this->field($text)->smartypants());
    }

    public function testSplit()
    {
        $this->markTestIncomplete();
    }

    public function testUpper()
    {
        $this->assertEquals('ABC', $this->field('abc')->upper());
    }

    public function testWidont()
    {
        $this->markTestIncomplete();
    }

    public function testWords()
    {
        $this->markTestIncomplete();
    }

    public function testXml()
    {
        $this->markTestIncomplete();
    }

    public function testYaml()
    {
        $data = [
            'a',
            'b',
            'c'
        ];

        $yaml = Yaml::encode($data);
        $this->assertEquals($data, $this->field($yaml)->yaml());
    }

}
