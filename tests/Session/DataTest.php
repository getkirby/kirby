<?php

namespace Kirby\Session;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Data::class)]
class DataTest extends TestCase
{
	protected Data $data;
	protected Session $session;

	protected function setUp(): void
	{
		$this->session = new MockSession();
		$this->data    = new Data($this->session, [
			'someString' => 'someValue',
			'someInt'    => 123
		]);
	}

	protected function tearDown(): void
	{
		unset($this->session, $this->data);
	}

	public function testClear(): void
	{
		$this->session->ensuredToken = false;
		$this->session->preparedForWriting = false;
		$this->data->clear();
		$this->assertFalse($this->session->ensuredToken);
		$this->assertTrue($this->session->preparedForWriting);
		$this->assertSame([], $this->data->get());
	}

	public function testDecrement(): void
	{
		$this->session->ensuredToken = false;
		$this->session->preparedForWriting = false;
		$this->assertSame(123, $this->data->get('someInt'));

		$this->data->decrement('someInt', 10);
		$this->assertTrue($this->session->ensuredToken);
		$this->assertTrue($this->session->preparedForWriting);
		$this->assertSame(113, $this->data->get('someInt'));

		$this->data->decrement('someInt', 10, 150);
		$this->assertSame(113, $this->data->get('someInt'));

		$this->data->decrement('someInt', 10, 105);
		$this->assertSame(105, $this->data->get('someInt'));

		$this->data->decrement(['someInt', 'someNewInt'], 10, -5);
		$this->assertSame(95, $this->data->get('someInt'));
		$this->assertSame(-5, $this->data->get('someNewInt'));

		$this->data->decrement('someInt', 10, 50);
		$this->assertSame(85, $this->data->get('someInt'));
	}

	public function testDecrementNegativeBy(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.invalidArgument');

		$this->data->decrement('someInt', -10);
	}

	public function testDecrementNonIntValue(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionCode('error.session.data.decrement.nonInt');

		$this->data->decrement('someString', 10);
	}

	public function testGet(): void
	{
		// string as key
		$this->assertSame('someValue', $this->data->get('someString', 'someDefault'));
		$this->assertNull($this->data->get('someOtherString'));
		$this->assertSame('someDefault', $this->data->get('someOtherString', 'someDefault'));
		$this->assertSame(123, $this->data->get('someInt', 456));

		// all data
		$this->assertSame([
			'someString' => 'someValue',
			'someInt'    => 123
		], $this->data->get());
	}

	public function testIncrement(): void
	{
		$this->session->ensuredToken = false;
		$this->session->preparedForWriting = false;
		$this->assertSame(123, $this->data->get('someInt'));

		$this->data->increment('someInt', 10);
		$this->assertTrue($this->session->ensuredToken);
		$this->assertTrue($this->session->preparedForWriting);
		$this->assertSame(133, $this->data->get('someInt'));

		$this->data->increment('someInt', 10, 120);
		$this->assertSame(133, $this->data->get('someInt'));

		$this->data->increment('someInt', 10, 140);
		$this->assertSame(140, $this->data->get('someInt'));

		$this->data->increment(['someInt', 'someNewInt'], 10, 145);
		$this->assertSame(145, $this->data->get('someInt'));
		$this->assertSame(10, $this->data->get('someNewInt'));

		$this->data->increment('someInt', 10, 200);
		$this->assertSame(155, $this->data->get('someInt'));
	}

	public function testIncrementNegativeBy(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.invalidArgument');

		$this->data->increment('someInt', -10);
	}

	public function testIncrementNonIntValue(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionCode('error.session.data.increment.nonInt');

		$this->data->increment('someString', 10);
	}

	public function testPull(): void
	{
		$this->session->ensuredToken = false;
		$this->session->preparedForWriting = false;
		$this->assertSame('someValue', $this->data->pull('someString'));
		$this->assertFalse($this->session->ensuredToken);
		$this->assertTrue($this->session->preparedForWriting);
		$this->assertNull($this->data->get('someString'));

		$this->assertNull($this->data->pull('someOtherString'));
		$this->assertSame('someDefault', $this->data->pull('someOtherString', 'someDefault'));
	}

	public function testReload(): void
	{
		$newData = ['someOtherString' => 'someOtherValue'];

		$this->session->ensuredToken = false;
		$this->session->preparedForWriting = false;
		$this->data->reload($newData);
		$this->assertFalse($this->session->ensuredToken);
		$this->assertFalse($this->session->preparedForWriting);
		$this->assertSame($newData, $this->data->get());
	}

	public function testRemove(): void
	{
		// string as key
		$this->session->ensuredToken = false;
		$this->session->preparedForWriting = false;
		$this->data->remove('someString');
		$this->assertFalse($this->session->ensuredToken);
		$this->assertTrue($this->session->preparedForWriting);
		$this->assertNull($this->data->get('someString'));

		// key-value array
		$this->session->ensuredToken = false;
		$this->session->preparedForWriting = false;
		$this->data->remove(['someString', 'someInt', 'someOtherString']);
		$this->assertFalse($this->session->ensuredToken);
		$this->assertTrue($this->session->preparedForWriting);
		$this->assertSame([], $this->data->get());
	}

	public function testSet(): void
	{
		// string as key
		$this->session->ensuredToken = false;
		$this->session->preparedForWriting = false;
		$this->data->set('someKey', 'someValue');
		$this->assertTrue($this->session->ensuredToken);
		$this->assertTrue($this->session->preparedForWriting);
		$this->assertSame('someValue', $this->data->get('someKey'));

		// int as key
		$this->data->set(123, 42);
		$this->assertSame(42, $this->data->get(123));
		$this->data->increment(123, 5);
		$this->data->decrement(123, 2);
		$this->assertSame(45, $this->data->get(123));

		// key-value array
		$this->session->ensuredToken = false;
		$this->session->preparedForWriting = false;
		$this->data->set([
			'someKey1' => 'someValue1',
			'someKey2' => 'someValue2'
		]);
		$this->assertTrue($this->session->ensuredToken);
		$this->assertTrue($this->session->preparedForWriting);
		$this->assertSame('someValue1', $this->data->get('someKey1'));
		$this->assertSame('someValue2', $this->data->get('someKey2'));
	}
}
