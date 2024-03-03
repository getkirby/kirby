<?php

namespace Kirby\Cms;

class CollectionsTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/collections';

	protected function _app()
	{
		return new App([
			'roots' => [
				'collections' => static::FIXTURES
			]
		]);
	}

	public function testGetAndCall()
	{
		$app        = $this->_app();
		$collection = new Collection();

		// get
		$result = $app->collections()->get('test');
		$this->assertEquals($collection, $result); // cannot use strict assertion (different object)

		// __call
		$result = $app->collections()->test();
		$this->assertEquals($collection, $result); // cannot use strict assertion (different object)
	}

	public function testGetWithData()
	{
		$app    = $this->_app();
		$result = $app->collections()->get('string', [
			'a' => 'a',
			'b' => 'b'
		]);

		$this->assertSame('ab', $result);
	}

	public function testGetWithRearrangedData()
	{
		$app    = $this->_app();
		$result = $app->collections()->get('rearranged', [
			'a' => 'a',
			'b' => 'b'
		]);

		$this->assertSame('ab', $result);
	}

	public function testGetWithDifferentData()
	{
		$app = $this->_app();

		$result = $app->collections()->get('string', [
			'a' => 'a',
			'b' => 'b'
		]);
		$this->assertSame('ab', $result);

		$result = $app->collections()->get('string', [
			'a' => 'c',
			'b' => 'd'
		]);
		$this->assertSame('cd', $result);
	}

	public function testGetCloned()
	{
		$app         = $this->_app();
		$collections = $app->collections();

		$a = $collections->get('test');
		$this->assertCount(0, $a);

		$a->add('kirby');
		$this->assertCount(1, $a);

		$b = $collections->get('test');
		$this->assertCount(0, $b);
	}

	public function testHas()
	{
		$app = $this->_app();
		$this->assertTrue($app->collections()->has('test'));
		$this->assertFalse($app->collections()->has('does-not-exist'));
		$this->assertTrue($app->collections()->has('test'));
	}

	public function testLoad()
	{
		$app = $this->_app();
		$result = $app->collections()->load('test');
		$this->assertInstanceOf(Collection::class, $result());

		$result = $app->collections()->load('nested/test');
		$this->assertSame('a', $result());
	}

	public function testLoadNested()
	{
		$app = $this->_app();
		$result = $app->collections()->load('nested/test');
		$this->assertSame('a', $result());
	}
}
