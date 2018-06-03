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
        return new ContentField('test', $value);
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
        $this->markTestIncomplete();
    }

    public function testToDate()
    {
        $this->markTestIncomplete();
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
        $this->markTestIncomplete();
    }

    public function testToInt()
    {
        $this->markTestIncomplete();
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

    public function testToUrl()
    {
        $this->markTestIncomplete();
    }

    public function testToUser()
    {
        $this->markTestIncomplete();
    }

    public function length()
    {
        $this->markTestIncomplete();
    }

    public function escape()
    {
        $this->markTestIncomplete();
    }

    public function html()
    {
        $this->markTestIncomplete();
    }

    public function kirbytext()
    {
        $this->markTestIncomplete();
    }

    public function kirbytags()
    {
        $this->markTestIncomplete();
    }

    public function lower()
    {
        $this->markTestIncomplete();
    }

    public function markdown()
    {
        $this->markTestIncomplete();
    }

    public function or()
    {
        $this->markTestIncomplete();
    }

    public function short()
    {
        $this->markTestIncomplete();
    }

    public function smartypants()
    {
        $this->markTestIncomplete();
    }

    public function split()
    {
        $this->markTestIncomplete();
    }

    public function upper()
    {
        $this->markTestIncomplete();
    }

    public function widont()
    {
        $this->markTestIncomplete();
    }

    public function words()
    {
        $this->markTestIncomplete();
    }

    public function xml()
    {
        $this->markTestIncomplete();
    }

}
