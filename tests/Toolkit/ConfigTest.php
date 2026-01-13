<?php

namespace Kirby\Toolkit;

class ConfigTest extends TestCase
{
	public function setUp(): void
	{
		Config::set('testvar', 'testvalue');
	}

	public function tearDown(): void
	{
		Config::$data = [];
	}

	public function testGet(): void
	{
		$this->assertSame('testvalue', Config::get('testvar'));
		$this->assertSame('defaultvalue', Config::get('nonexistentvar', 'defaultvalue'));
	}

	public function testSet(): void
	{
		Config::set('anothervar', 'anothervalue');
		Config::set('testvar', 'overwrittenvalue');

		$this->assertSame('anothervalue', Config::get('anothervar'));
		$this->assertSame('overwrittenvalue', Config::get('testvar'));

		Config::set([
			'var1' => 'value1',
			'var2' => 'value2'
		]);

		$this->assertSame('value1', Config::get('var1'));
		$this->assertSame('value2', Config::get('var2'));
	}
}
