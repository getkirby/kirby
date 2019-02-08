<?php

namespace Kirby\Session;

use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/mocks.php');

class SessionDataTest extends TestCase
{
    protected $session;
    protected $sessionData;

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
        unset($this->session);
        unset($this->sessionData);
    }

    public function testSet()
    {
        // string as key
        $this->session->ensuredToken = false;
        $this->session->preparedForWriting = false;
        $this->sessionData->set('someKey', 'someValue');
        $this->assertTrue($this->session->ensuredToken);
        $this->assertTrue($this->session->preparedForWriting);
        $this->assertEquals('someValue', $this->sessionData->get('someKey'));

        // key-value array
        $this->session->ensuredToken = false;
        $this->session->preparedForWriting = false;
        $this->sessionData->set([
            'someKey1' => 'someValue1',
            'someKey2' => 'someValue2'
        ]);
        $this->assertTrue($this->session->ensuredToken);
        $this->assertTrue($this->session->preparedForWriting);
        $this->assertEquals('someValue1', $this->sessionData->get('someKey1'));
        $this->assertEquals('someValue2', $this->sessionData->get('someKey2'));
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testSetInvalidKey()
    {
        $this->sessionData->set(123, 'someValue');
    }

    public function testIncrement()
    {
        $this->session->ensuredToken = false;
        $this->session->preparedForWriting = false;
        $this->assertEquals(123, $this->sessionData->get('someInt'));

        $this->sessionData->increment('someInt', 10);
        $this->assertTrue($this->session->ensuredToken);
        $this->assertTrue($this->session->preparedForWriting);
        $this->assertEquals(133, $this->sessionData->get('someInt'));

        $this->sessionData->increment('someInt', 10, 120);
        $this->assertEquals(133, $this->sessionData->get('someInt'));

        $this->sessionData->increment('someInt', 10, 140);
        $this->assertEquals(140, $this->sessionData->get('someInt'));

        $this->sessionData->increment(['someInt', 'someNewInt'], 10, 145);
        $this->assertEquals(145, $this->sessionData->get('someInt'));
        $this->assertEquals(10, $this->sessionData->get('someNewInt'));

        $this->sessionData->increment('someInt', 10, 200);
        $this->assertEquals(155, $this->sessionData->get('someInt'));
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testIncrementInvalidKey()
    {
        $this->sessionData->increment(123, 10);
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testIncrementInvalidMax()
    {
        $this->sessionData->increment('someInt', 10, 'some invalid max value');
    }

    /**
     * @expectedException     Kirby\Exception\LogicException
     * @expectedExceptionCode error.session.data.increment.nonInt
     */
    public function testIncrementNonIntValue()
    {
        $this->sessionData->increment('someString', 10);
    }

    public function testDecrement()
    {
        $this->session->ensuredToken = false;
        $this->session->preparedForWriting = false;
        $this->assertEquals(123, $this->sessionData->get('someInt'));

        $this->sessionData->decrement('someInt', 10);
        $this->assertTrue($this->session->ensuredToken);
        $this->assertTrue($this->session->preparedForWriting);
        $this->assertEquals(113, $this->sessionData->get('someInt'));

        $this->sessionData->decrement('someInt', 10, 150);
        $this->assertEquals(113, $this->sessionData->get('someInt'));

        $this->sessionData->decrement('someInt', 10, 105);
        $this->assertEquals(105, $this->sessionData->get('someInt'));

        $this->sessionData->decrement(['someInt', 'someNewInt'], 10, -5);
        $this->assertEquals(95, $this->sessionData->get('someInt'));
        $this->assertEquals(-5, $this->sessionData->get('someNewInt'));

        $this->sessionData->decrement('someInt', 10, 50);
        $this->assertEquals(85, $this->sessionData->get('someInt'));
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testDecrementInvalidKey()
    {
        $this->sessionData->decrement(123, 10);
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testDecrementInvalidMin()
    {
        $this->sessionData->decrement('someInt', 10, 'some invalid min value');
    }

    /**
     * @expectedException     Kirby\Exception\LogicException
     * @expectedExceptionCode error.session.data.decrement.nonInt
     */
    public function testDecrementNonIntValue()
    {
        $this->sessionData->decrement('someString', 10);
    }

    public function testGet()
    {
        // string as key
        $this->assertEquals('someValue', $this->sessionData->get('someString', 'someDefault'));
        $this->assertEquals(null, $this->sessionData->get('someOtherString'));
        $this->assertEquals('someDefault', $this->sessionData->get('someOtherString', 'someDefault'));
        $this->assertEquals(123, $this->sessionData->get('someInt', 456));

        // all data
        $this->assertEquals([
            'someString' => 'someValue',
            'someInt'    => 123
        ], $this->sessionData->get());
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testGetInvalidKey()
    {
        $this->sessionData->get(123, 456);
    }

    public function testPull()
    {
        $this->session->ensuredToken = false;
        $this->session->preparedForWriting = false;
        $this->assertEquals('someValue', $this->sessionData->pull('someString'));
        $this->assertFalse($this->session->ensuredToken);
        $this->assertTrue($this->session->preparedForWriting);
        $this->assertEquals(null, $this->sessionData->get('someString'));

        $this->assertEquals(null, $this->sessionData->pull('someOtherString'));
        $this->assertEquals('someDefault', $this->sessionData->pull('someOtherString', 'someDefault'));
    }

    public function testRemove()
    {
        // string as key
        $this->session->ensuredToken = false;
        $this->session->preparedForWriting = false;
        $this->sessionData->remove('someString');
        $this->assertFalse($this->session->ensuredToken);
        $this->assertTrue($this->session->preparedForWriting);
        $this->assertEquals(null, $this->sessionData->get('someString'));

        // key-value array
        $this->session->ensuredToken = false;
        $this->session->preparedForWriting = false;
        $this->sessionData->remove(['someString', 'someInt', 'someOtherString']);
        $this->assertFalse($this->session->ensuredToken);
        $this->assertTrue($this->session->preparedForWriting);
        $this->assertEquals([], $this->sessionData->get());
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testRemoveInvalidKey()
    {
        $this->sessionData->remove(123);
    }

    public function testClear()
    {
        $this->session->ensuredToken = false;
        $this->session->preparedForWriting = false;
        $this->sessionData->clear();
        $this->assertFalse($this->session->ensuredToken);
        $this->assertTrue($this->session->preparedForWriting);
        $this->assertEquals([], $this->sessionData->get());
    }

    public function testReload()
    {
        $newData = ['someOtherString' => 'someOtherValue'];

        $this->session->ensuredToken = false;
        $this->session->preparedForWriting = false;
        $this->sessionData->reload($newData);
        $this->assertFalse($this->session->ensuredToken);
        $this->assertFalse($this->session->preparedForWriting);
        $this->assertEquals($newData, $this->sessionData->get());
    }
}
