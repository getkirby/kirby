<?php

namespace Kirby\Toolkit;

class ConfigTest extends TestCase
{
    protected function setUp(): void
    {
        Config::set('testvar', 'testvalue');
    }

    public function tearDown(): void
    {
        Config::$data = [];
    }

    public function testGet()
    {
        $this->assertEquals('testvalue', Config::get('testvar'));
        $this->assertEquals('defaultvalue', Config::get('nonexistentvar', 'defaultvalue'));
    }

    public function testSet()
    {
        Config::set('anothervar', 'anothervalue');
        Config::set('testvar', 'overwrittenvalue');

        $this->assertEquals('anothervalue', Config::get('anothervar'));
        $this->assertEquals('overwrittenvalue', Config::get('testvar'));

        Config::set([
            'var1' => 'value1',
            'var2' => 'value2'
        ]);

        $this->assertEquals('value1', Config::get('var1'));
        $this->assertEquals('value2', Config::get('var2'));
    }
}
