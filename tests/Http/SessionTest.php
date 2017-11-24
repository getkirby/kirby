<?php

namespace Kirby\Http;

use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{

    public function testStart()
    {
        $this->assertFalse(Session::started());
        $this->assertTrue(Session::start());
        $this->assertTrue(Session::started());
        $this->assertTrue(Session::start());
        $this->assertTrue(Session::destroy());
    }

    public function testCheck()
    {
        Session::start();
        $this->assertTrue(Session::check());

        $_SESSION[Session::$name . '_activity'] = 0;
        $this->assertFalse(Session::check());
        Session::restart();

        $_SESSION[Session::$name . '_fingerprint'] = 'aaa';
        $this->assertFalse(Session::check());

        Session::destroy();
    }

    public function testFingerprint()
    {
        $this->assertEquals('', Session::fingerprint());

        Session::$fingerprint = function () {
            return 'foo';
        };

        $this->assertEquals('foo', Session::fingerprint());
    }

    public function testId()
    {
        $id1 = Session::id();
        $this->assertInternalType('string', $id1);

        Session::regenerateId();
        $id2 = Session::id();
        $this->assertInternalType('string', $id2);

        $this->assertNotEquals($id1, $id2);

        Session::destroy();
    }

    public function testSet()
    {
        Session::set('foo', 'bar');
        $this->assertEquals('bar', $_SESSION['foo']);
        Session::destroy();
    }

    public function testSetArray()
    {
        Session::set([
            'bastian' => 'allgeier',
            'nico'    => 'hoffmann'
        ]);
        $this->assertEquals('allgeier', $_SESSION['bastian']);
        $this->assertEquals('hoffmann', $_SESSION['nico']);
        Session::destroy();
    }

    public function testSetWithoutSession()
    {
        Session::start();
        unset($_SESSION);
        $this->assertFalse(Session::set('new', 'stuff'));
        Session::destroy();
    }

    public function testGet()
    {
        Session::set('foo', 'bar');
        $this->assertEquals('bar', Session::get('foo'));
        Session::destroy();
    }

    public function testGetFallback()
    {
        Session::start();
        $this->assertEquals('fallback', Session::get('foo', 'fallback'));
        Session::destroy();
    }

    public function testGetSession()
    {
        Session::start();
        $this->assertEquals($_SESSION, Session::get());
        Session::destroy();
    }

    public function testGetWithoutSession()
    {
        Session::start();
        unset($_SESSION);
        $this->assertFalse(Session::get('new'));
        Session::destroy();
    }

    public function testPull()
    {
        Session::set('foo', 'bar');
        $this->assertEquals('bar', Session::pull('foo', 'fallback'));
        $this->assertEquals('fallback', Session::pull('foo', 'fallback'));
        Session::destroy();
    }

    public function testDestroyStop()
    {
        $this->assertFalse(Session::destroy());
        $this->assertFalse(Session::stop());
        $this->assertTrue(Session::start());
        $this->assertTrue(Session::destroy());
    }
}
