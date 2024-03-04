<?php

namespace Kirby\Toolkit;

/**
 * @coversDefaultClass \Kirby\Toolkit\Config
 */
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

	/**
	 * @covers ::get
	 */
	public function testGet()
	{
		$this->assertSame('testvalue', Config::get('testvar'));
		$this->assertSame('defaultvalue', Config::get('nonexistentvar', 'defaultvalue'));
	}

	/**
	 * @covers ::set
	 */
	public function testSet()
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
