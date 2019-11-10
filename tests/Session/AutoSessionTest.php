<?php

namespace Kirby\Session;

use Kirby\Http\Cookie;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

require_once __DIR__ . '/mocks.php';

/**
 * @coversDefaultClass \Kirby\Session\AutoSession
 */
class AutoSessionTest extends TestCase
{
    protected $store;

    public function setUp(): void
    {
        $this->store = new TestSessionStore();

        MockTime::$time = 1337000000;
    }

    public function tearDown(): void
    {
        unset($this->store);
    }

    /**
     * @covers ::__construct
     */
    public function testSessionsOptions()
    {
        $autoSessionReflector = new ReflectionClass(AutoSession::class);
        $sessionsProperty = $autoSessionReflector->getProperty('sessions');
        $sessionsProperty->setAccessible(true);
        $fileSessionStoreReflector = new ReflectionClass(FileSessionStore::class);
        $pathProperty = $fileSessionStoreReflector->getProperty('path');
        $pathProperty->setAccessible(true);

        // store object as store
        $autoSession = new AutoSession($this->store);
        $this->assertSame($this->store, $sessionsProperty->getValue($autoSession)->store());

        // path string as store
        $autoSession = new AutoSession(__DIR__ . '/fixtures/store');
        $this->assertSame(__DIR__ . '/fixtures/store', $pathProperty->getValue($sessionsProperty->getValue($autoSession)->store()));

        // default cookie name
        $autoSession = new AutoSession($this->store);
        $this->assertSame('kirby_session', $sessionsProperty->getValue($autoSession)->cookieName());

        // custom cookie name
        $autoSession = new AutoSession($this->store, ['cookieName' => 'my_cookie']);
        $this->assertSame('my_cookie', $sessionsProperty->getValue($autoSession)->cookieName());

        // collect garbage every time
        $this->store->collectedGarbage = false;
        $autoSession = new AutoSession($this->store, ['gcInterval' => 1]);
        $this->assertTrue($this->store->collectedGarbage);

        // never collect garbage
        $this->store->collectedGarbage = false;
        $autoSession = new AutoSession($this->store, ['gcInterval' => false]);
        $this->assertFalse($this->store->collectedGarbage);
    }

    /**
     * @covers ::get
     */
    public function testGet()
    {
        Cookie::set('kirby_session', '9999999999.valid.' . $this->store->validKey);
        $_SERVER['HTTP_AUTHORIZATION'] = 'Session 9999999999.valid2.' . $this->store->validKey;
        $autoSession = new AutoSession($this->store);

        // default: no detection
        $session = $autoSession->get();
        $this->assertSame('9999999999.valid.' . $this->store->validKey, $session->token());

        // use detection
        $session = $autoSession->get(['detect' => true]);
        $this->assertSame('9999999999.valid2.' . $this->store->validKey, $session->token());

        // newly created session
        Cookie::remove('kirby_session');
        unset($_SERVER['HTTP_AUTHORIZATION']);
        $session = $autoSession->get();
        $this->assertNull($session->token());
        $this->assertSame('cookie', $session->mode());
        $this->assertSame(1337000000, $session->startTime()); // timestamp is from mock
        $this->assertSame(7200, $session->duration());
        $this->assertSame(1337000000 + 7200, $session->expiryTime()); // timestamp is from mock
        $this->assertSame(1800, $session->timeout());
        $this->assertTrue($session->renewable());

        // session needs to be the same one each time
        $this->assertTrue($session === $autoSession->get());

        // custom create mode
        $autoSession = new AutoSession($this->store);
        $session = $autoSession->get(['createMode' => 'manual']);
        $this->assertNull($session->token());
        $this->assertSame('manual', $session->mode());

        // getting a session with the default createMode shouldn't change the mode
        $session = $autoSession->get();
        $this->assertNull($session->token());
        $this->assertSame('manual', $session->mode());

        // but in the other direction it should
        $autoSession = new AutoSession($this->store);
        $session = $autoSession->get();
        $this->assertNull($session->token());
        $this->assertSame('cookie', $session->mode());
        $session = $autoSession->get(['createMode' => 'manual']);
        $this->assertNull($session->token());
        $this->assertSame('manual', $session->mode());

        // but not if the session has already been initialized
        $autoSession = new AutoSession($this->store);
        $session = $autoSession->get();
        $this->assertNull($session->token());
        $this->assertSame('cookie', $session->mode());
        $session->data()->set('someKey', 'someValue');
        $this->assertNotNull($session->token());
        $session = $autoSession->get(['createMode' => 'manual']);
        $this->assertNotNull($session->token());
        $this->assertSame('cookie', $session->mode());

        // long session defaults
        $autoSession = new AutoSession($this->store);
        $session = $autoSession->get(['long' => true]);
        $this->assertNull($session->token());
        $this->assertSame('cookie', $session->mode());
        $this->assertSame(1337000000, $session->startTime()); // timestamp is from mock
        $this->assertSame(1209600, $session->duration());
        $this->assertSame(1337000000 + 1209600, $session->expiryTime()); // timestamp is from mock
        $this->assertFalse($session->timeout());
        $this->assertTrue($session->renewable());

        // session config update when switching to long session
        $autoSession = new AutoSession($this->store);
        $session = $autoSession->get();
        $this->assertSame(7200, $session->duration());
        $this->assertSame(1800, $session->timeout());
        $session->data()->set('id', 'awesome session');
        $session->commit();
        Cookie::set('kirby_session', $session->token());
        $session = $autoSession->get(['long' => true]);
        $this->assertSame('awesome session', $session->data()->get('id'));
        $this->assertSame(1209600, $session->duration());
        $this->assertSame(false, $session->timeout());
        Cookie::remove('kirby_session');

        // custom duration and timeout (normal session)
        $autoSession = new AutoSession($this->store, [
            'durationNormal' => 1,
            'durationLong'   => 5,
            'timeout'        => 1234
        ]);
        $session = $autoSession->get();
        $this->assertNull($session->token());
        $this->assertSame('cookie', $session->mode());
        $this->assertSame(1337000000, $session->startTime()); // timestamp is from mock
        $this->assertSame(1, $session->duration());
        $this->assertSame(1337000000 + 1, $session->expiryTime()); // timestamp is from mock
        $this->assertSame(1234, $session->timeout());
        $this->assertTrue($session->renewable());

        // custom duration and timeout (long session)
        $session = $autoSession->get(['long' => true]);
        $this->assertNull($session->token());
        $this->assertSame('cookie', $session->mode());
        $this->assertSame(1337000000, $session->startTime()); // timestamp is from mock
        $this->assertSame(5, $session->duration());
        $this->assertSame(1337000000 + 5, $session->expiryTime()); // timestamp is from mock
        $this->assertFalse($session->timeout());
        $this->assertTrue($session->renewable());

        // session config update when the configuration changed
        $autoSession = new AutoSession($this->store);
        $session = $autoSession->get();
        $this->assertSame(7200, $session->duration());
        $this->assertSame(1800, $session->timeout());
        $session->data()->set('id', 'awesome session');
        $session->commit();
        Cookie::set('kirby_session', $session->token());

        // lower values: shouldn't change anything
        $autoSession = new AutoSession($this->store, ['durationNormal' => 7100, 'timeout' => 1000]);
        $session = $autoSession->get();
        $this->assertSame('awesome session', $session->data()->get('id'));
        $this->assertSame(7200, $session->duration());
        $this->assertSame(1800, $session->timeout());
        $session->commit();

        // higher values: should update
        $autoSession = new AutoSession($this->store, ['durationNormal' => 7300, 'timeout' => 1900]);
        $session = $autoSession->get();
        $this->assertSame('awesome session', $session->data()->get('id'));
        $this->assertSame(7300, $session->duration());
        $this->assertSame(1900, $session->timeout());
        $session->commit();

        // remove timeout: should update
        $autoSession = new AutoSession($this->store, ['timeout' => false]);
        $session = $autoSession->get();
        $this->assertSame('awesome session', $session->data()->get('id'));
        $this->assertSame(7300, $session->duration());
        $this->assertSame(false, $session->timeout());
        Cookie::remove('kirby_session');

        // timeout for the first time: shouldn't change anything
        $autoSession = new AutoSession($this->store);
        $session = $autoSession->get(['long' => true]);
        $this->assertSame(1209600, $session->duration());
        $this->assertSame(false, $session->timeout());
        $session->data()->set('id', 'awesome session');
        $session->commit();
        Cookie::set('kirby_session', $session->token());
        $session = $autoSession->get();
        $this->assertSame('awesome session', $session->data()->get('id'));
        $this->assertSame(1209600, $session->duration());
        $this->assertSame(false, $session->timeout());
        $session->commit();
    }

    /**
     * @covers ::createManually
     */
    public function testCreateManually()
    {
        $autoSession = new AutoSession($this->store);
        $session = $autoSession->createManually(['expiryTime' => 9999999999, 'mode' => 'cookie']);

        $this->assertNull($session->token());
        $this->assertSame(9999999999, $session->expiryTime());
        $this->assertSame('manual', $session->mode());
    }

    /**
     * @covers ::getManually
     */
    public function testGetManually()
    {
        $autoSession = new AutoSession($this->store);

        $session = $autoSession->getManually('9999999999.valid.' . $this->store->validKey);
        $this->assertSame('manual', $session->mode());
        $this->assertSame('9999999999.valid.' . $this->store->validKey, $session->token());
    }

    /**
     * @covers ::collectGarbage
     */
    public function testCollectGarbage()
    {
        $this->store->collectedGarbage = false;
        $autoSession = new AutoSession($this->store, ['gcInterval' => false]);
        $this->assertFalse($this->store->collectedGarbage);

        $autoSession->collectGarbage();
        $this->assertTrue($this->store->collectedGarbage);
    }
}
