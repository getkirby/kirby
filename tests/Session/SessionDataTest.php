<?php

namespace Kirby\Session;

use Kirby\Exception\LogicException;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Session\SessionData
 */
class SessionDataTest extends TestCase
{
	protected Session $session;
	protected SessionData $sessionData;

	public function setUp(): void
	{
		$this->session     = new MockSession();
		$this->sessionData = new SessionData($this->session, [
			'someString' => 'someValue',
			'someInt'    => 123
		]);
	}

	public function tearDown(): void
	{
		unset($this->session, $this->sessionData);
	}

	/**
	 * @covers ::set
	 */
	public function testSet()
	{
		// string as key
		$this->session->ensuredToken = false;
		$this->session->preparedForWriting = false;
		$this->sessionData->set('someKey', 'someValue');
		$this->assertTrue($this->session->ensuredToken);
		$this->assertTrue($this->session->preparedForWriting);
		$this->assertSame('someValue', $this->sessionData->get('someKey'));

		// int as key
		$this->sessionData->set(123, 42);
		$this->assertSame(42, $this->sessionData->get(123));
		$this->sessionData->increment(123, 5);
		$this->sessionData->decrement(123, 2);
		$this->assertSame(45, $this->sessionData->get(123));

		// key-value array
		$this->session->ensuredToken = false;
		$this->session->preparedForWriting = false;
		$this->sessionData->set([
			'someKey1' => 'someValue1',
			'someKey2' => 'someValue2'
		]);
		$this->assertTrue($this->session->ensuredToken);
		$this->assertTrue($this->session->preparedForWriting);
		$this->assertSame('someValue1', $this->sessionData->get('someKey1'));
		$this->assertSame('someValue2', $this->sessionData->get('someKey2'));
	}

	/**
	 * @covers ::increment
	 */
	public function testIncrement()
	{
		$this->session->ensuredToken = false;
		$this->session->preparedForWriting = false;
		$this->assertSame(123, $this->sessionData->get('someInt'));

		$this->sessionData->increment('someInt', 10);
		$this->assertTrue($this->session->ensuredToken);
		$this->assertTrue($this->session->preparedForWriting);
		$this->assertSame(133, $this->sessionData->get('someInt'));

		$this->sessionData->increment('someInt', 10, 120);
		$this->assertSame(133, $this->sessionData->get('someInt'));

		$this->sessionData->increment('someInt', 10, 140);
		$this->assertSame(140, $this->sessionData->get('someInt'));

		$this->sessionData->increment(['someInt', 'someNewInt'], 10, 145);
		$this->assertSame(145, $this->sessionData->get('someInt'));
		$this->assertSame(10, $this->sessionData->get('someNewInt'));

		$this->sessionData->increment('someInt', 10, 200);
		$this->assertSame(155, $this->sessionData->get('someInt'));
	}

	/**
	 * @covers ::increment
	 */
	public function testIncrementNonIntValue()
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionCode('error.session.data.increment.nonInt');

		$this->sessionData->increment('someString', 10);
	}

	/**
	 * @covers ::decrement
	 */
	public function testDecrement()
	{
		$this->session->ensuredToken = false;
		$this->session->preparedForWriting = false;
		$this->assertSame(123, $this->sessionData->get('someInt'));

		$this->sessionData->decrement('someInt', 10);
		$this->assertTrue($this->session->ensuredToken);
		$this->assertTrue($this->session->preparedForWriting);
		$this->assertSame(113, $this->sessionData->get('someInt'));

		$this->sessionData->decrement('someInt', 10, 150);
		$this->assertSame(113, $this->sessionData->get('someInt'));

		$this->sessionData->decrement('someInt', 10, 105);
		$this->assertSame(105, $this->sessionData->get('someInt'));

		$this->sessionData->decrement(['someInt', 'someNewInt'], 10, -5);
		$this->assertSame(95, $this->sessionData->get('someInt'));
		$this->assertSame(-5, $this->sessionData->get('someNewInt'));

		$this->sessionData->decrement('someInt', 10, 50);
		$this->assertSame(85, $this->sessionData->get('someInt'));
	}

	/**
	 * @covers ::decrement
	 */
	public function testDecrementNonIntValue()
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionCode('error.session.data.decrement.nonInt');

		$this->sessionData->decrement('someString', 10);
	}

	/**
	 * @covers ::get
	 */
	public function testGet()
	{
		// string as key
		$this->assertSame('someValue', $this->sessionData->get('someString', 'someDefault'));
		$this->assertNull($this->sessionData->get('someOtherString'));
		$this->assertSame('someDefault', $this->sessionData->get('someOtherString', 'someDefault'));
		$this->assertSame(123, $this->sessionData->get('someInt', 456));

		// all data
		$this->assertSame([
			'someString' => 'someValue',
			'someInt'    => 123
		], $this->sessionData->get());
	}

	/**
	 * @covers ::pull
	 */
	public function testPull()
	{
		$this->session->ensuredToken = false;
		$this->session->preparedForWriting = false;
		$this->assertSame('someValue', $this->sessionData->pull('someString'));
		$this->assertFalse($this->session->ensuredToken);
		$this->assertTrue($this->session->preparedForWriting);
		$this->assertNull($this->sessionData->get('someString'));

		$this->assertNull($this->sessionData->pull('someOtherString'));
		$this->assertSame('someDefault', $this->sessionData->pull('someOtherString', 'someDefault'));
	}

	/**
	 * @covers ::remove
	 */
	public function testRemove()
	{
		// string as key
		$this->session->ensuredToken = false;
		$this->session->preparedForWriting = false;
		$this->sessionData->remove('someString');
		$this->assertFalse($this->session->ensuredToken);
		$this->assertTrue($this->session->preparedForWriting);
		$this->assertNull($this->sessionData->get('someString'));

		// key-value array
		$this->session->ensuredToken = false;
		$this->session->preparedForWriting = false;
		$this->sessionData->remove(['someString', 'someInt', 'someOtherString']);
		$this->assertFalse($this->session->ensuredToken);
		$this->assertTrue($this->session->preparedForWriting);
		$this->assertSame([], $this->sessionData->get());
	}

	/**
	 * @covers ::clear
	 */
	public function testClear()
	{
		$this->session->ensuredToken = false;
		$this->session->preparedForWriting = false;
		$this->sessionData->clear();
		$this->assertFalse($this->session->ensuredToken);
		$this->assertTrue($this->session->preparedForWriting);
		$this->assertSame([], $this->sessionData->get());
	}

	/**
	 * @covers ::reload
	 */
	public function testReload()
	{
		$newData = ['someOtherString' => 'someOtherValue'];

		$this->session->ensuredToken = false;
		$this->session->preparedForWriting = false;
		$this->sessionData->reload($newData);
		$this->assertFalse($this->session->ensuredToken);
		$this->assertFalse($this->session->preparedForWriting);
		$this->assertSame($newData, $this->sessionData->get());
	}
}
