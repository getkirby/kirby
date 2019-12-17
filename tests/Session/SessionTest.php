<?php

namespace Kirby\Session;

use Kirby\Http\Cookie;
use Kirby\Toolkit\Obj;
use Kirby\Toolkit\Str;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

require_once __DIR__ . '/mocks.php';

/**
 * @coversDefaultClass \Kirby\Session\Session
 */
class SessionTest extends TestCase
{
    protected $store;
    protected $sessions;

    public function setUp(): void
    {
        $this->store    = new TestSessionStore();
        $this->sessions = new Sessions($this->store);

        MockTime::$time = 1337000000;
    }

    public function tearDown(): void
    {
        unset($this->sessions, $this->store);
    }

    /**
     * @covers ::__construct
     */
    public function testConstructInvalidToken()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');

        new Session($this->sessions, 123, []);
    }

    /**
     * @covers ::__construct
     * @covers ::token
     * @covers ::startTime
     * @covers ::data
     * @covers ::destroy
     * @covers ::ensureToken
     */
    public function testCreate()
    {
        $reflector = new ReflectionClass(Session::class);
        $activityProperty = $reflector->getProperty('lastActivity');
        $activityProperty->setAccessible(true);

        // defaults
        $session = new Session($this->sessions, null, []);
        $this->assertSame(1337000000, $session->startTime()); // timestamp is from mock
        $this->assertSame(1337000000 + 7200, $session->expiryTime()); // timestamp is from mock
        $this->assertSame(7200, $session->duration());
        $this->assertSame(1800, $session->timeout());
        $this->assertSame(1337000000, $activityProperty->getValue($session)); // timestamp is from mock
        $this->assertSame(true, $session->renewable());
        $this->assertSame([], $session->data()->get());
        $this->assertNull($session->token());
        $this->assertWriteMode(false, $session);

        // custom values
        $session = new Session($this->sessions, null, [
            'startTime'  => 1337000000 + 60, // timestamp is from mock
            'expiryTime' => '+ 1 hour',
            'timeout'    => false,
            'renewable'  => false
        ]);
        $this->assertSame(1337000000 + 60, $session->startTime()); // timestamp is from mock
        $this->assertSame(1337000000 + 3660, $session->expiryTime()); // timestamp is from mock
        $this->assertSame(3600, $session->duration());
        $this->assertFalse($session->timeout());
        $this->assertSame(null, $activityProperty->getValue($session));
        $this->assertSame(false, $session->renewable());
        $this->assertSame([], $session->data()->get());
        $this->assertNull($session->token());
        $this->assertWriteMode(false, $session);

        // changing any value shouldn't initialize the session
        $session->expiryTime(9999999999);
        $session->duration(1000);
        $session->timeout(60);
        $session->renewable(true);
        $this->assertNull($session->token());
        $this->assertWriteMode(false, $session);
        $session->data()->get('someKey');
        $session->data()->pull('someKey');
        $session->data()->remove('someKey');
        $session->clear();
        $this->assertNull($session->token());
        $this->assertWriteMode(false, $session);
        $session->renew();
        $this->assertNull($session->token());
        $this->assertWriteMode(false, $session);
        $session->destroy();
        $this->assertNull($session->token());
        $this->assertWriteMode(false, $session);

        // but setting a new value should
        $session->data()->set('someKey', 'someValue');
        $this->assertNotNull($session->token());
        $this->assertIsString($session->token());
        $this->assertWriteMode(true, $session);

        $token = explode('.', $session->token());
        $this->assertTrue(isset($this->store->sessions[$token[0] . '.' . $token[1]]));
    }

    /**
     * @covers ::__construct
     */
    public function testCreateInvalidExpiry()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');

        new Session($this->sessions, null, [
            'startTime'  => time() - 3600,
            'expiryTime' => time() - 1800
        ]);
    }

    /**
     * @covers ::__construct
     */
    public function testCreateInvalidDuration()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');

        new Session($this->sessions, null, [
            'startTime'  => time() + 60,
            'expiryTime' => time()
        ]);
    }

    /**
     * @covers ::__construct
     */
    public function testCreateInvalidTimeout()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');

        new Session($this->sessions, null, [
            'timeout' => 'at some point'
        ]);
    }

    /**
     * @covers ::__construct
     */
    public function testCreateInvalidRenewable()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');

        new Session($this->sessions, null, [
            'renewable' => 'maybe'
        ]);
    }

    /**
     * @covers ::mode
     */
    public function testMode()
    {
        $session = new Session($this->sessions, null, [
            'mode' => 'manual'
        ]);

        $this->assertSame('manual', $session->mode());
        $this->assertSame('cookie', $session->mode('cookie'));
        $this->assertSame('cookie', $session->mode());
    }

    /**
     * @covers ::mode
     * @covers ::data
     */
    public function testModeStartedSession()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');

        $session = new Session($this->sessions, null, [
            'mode' => 'manual'
        ]);
        $session->data()->set('someKey', 'someValue');

        $this->assertSame('manual', $session->mode());
        $session->mode('cookie');
    }

    /**
     * @covers ::expiryTime
     * @covers ::token
     * @covers ::commit
     * @covers ::regenerateToken
     * @covers ::regenerateTokenIfNotNew
     */
    public function testExpiryTime()
    {
        $session = new Session($this->sessions, null, [
            'startTime'  => 1000000000,
            'expiryTime' => 7777777777
        ]);
        $session->regenerateToken();
        $session->commit();
        $originalToken = $session->token();

        $this->assertSame(7777777777, $session->expiryTime());
        $this->assertSame(6777777777, $session->duration());
        $this->assertWriteMode(false, $session);
        $this->assertSame(9999999999, $session->expiryTime(9999999999));
        $this->assertSame(9999999999, $session->expiryTime());
        $this->assertWriteMode(true, $session);
        $this->assertNotSame($originalToken, $session->token());
        $originalToken = $session->token();
        $this->assertSame(1337000000 + 3600, $newExpiry = $session->expiryTime('+ 1 hour')); // timestamp is from mock
        $this->assertSame($newExpiry, $session->expiryTime());
        $this->assertSame(3600, $session->duration());
        $this->assertWriteMode(true, $session);
        $this->assertNotSame($originalToken, $session->token());
    }

    /**
     * @covers ::expiryTime
     */
    public function testExpiryTimeInvalidType()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');

        $session = new Session($this->sessions, null, [
            'startTime'  => 1000000000,
            'expiryTime' => 7777777777
        ]);
        $session->expiryTime(false);
    }

    /**
     * @covers ::expiryTime
     */
    public function testExpiryTimeInvalidString()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');

        $session = new Session($this->sessions, null, [
            'startTime'  => 1000000000,
            'expiryTime' => 7777777777
        ]);
        $session->expiryTime('some gibberish that is definitely no valid time');
    }

    /**
     * @covers ::expiryTime
     */
    public function testExpiryTimeInvalidTime1()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');

        $session = new Session($this->sessions, null, [
            'startTime'  => 1000000000,
            'expiryTime' => 7777777777
        ]);
        $session->expiryTime(time() - 1);
    }

    /**
     * @covers ::expiryTime
     */
    public function testExpiryTimeInvalidTime2()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');

        $session = new Session($this->sessions, null, [
            'startTime'  => 1000000000,
            'expiryTime' => 7777777777
        ]);
        $session->expiryTime(time());
    }

    /**
     * @covers ::duration
     * @covers ::token
     * @covers ::commit
     * @covers ::regenerateToken
     * @covers ::regenerateTokenIfNotNew
     */
    public function testDuration()
    {
        $session = new Session($this->sessions, null, [
            'startTime'  => 1000000000,
            'expiryTime' => 7777777777
        ]);
        $session->regenerateToken();
        $session->commit();
        $originalToken = $session->token();

        $this->assertSame(7777777777, $session->expiryTime());
        $this->assertSame(6777777777, $session->duration());
        $this->assertWriteMode(false, $session);
        $this->assertSame(3600, $session->duration(3600));
        $this->assertSame(3600, $session->duration());
        $this->assertSame(1337000000 + 3600, $session->expiryTime()); // timestamp is from mock
        $this->assertWriteMode(true, $session);
        $this->assertNotSame($originalToken, $session->token());
    }

    /**
     * @covers ::duration
     */
    public function testDurationInvalidTime1()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');

        $session = new Session($this->sessions, null, [
            'startTime'  => 1000000000,
            'expiryTime' => 7777777777
        ]);
        $session->duration(-1);
    }

    /**
     * @covers ::duration
     */
    public function testDurationInvalidTime2()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');

        $session = new Session($this->sessions, null, [
            'startTime'  => 1000000000,
            'expiryTime' => 7777777777
        ]);
        $session->duration(0);
    }

    /**
     * @covers ::timeout
     * @covers ::commit
     * @covers ::regenerateToken
     */
    public function testTimeout()
    {
        $reflector = new ReflectionClass(Session::class);
        $activityProperty = $reflector->getProperty('lastActivity');
        $activityProperty->setAccessible(true);

        $session = new Session($this->sessions, null, [
            'timeout' => 1234
        ]);
        $session->regenerateToken();
        $session->commit();

        $this->assertSame(1234, $session->timeout());
        $this->assertWriteMode(false, $session);
        $this->assertSame(4321, $session->timeout(4321));
        $this->assertSame(4321, $session->timeout());
        $this->assertSame(time(), $activityProperty->getValue($session));
        $this->assertWriteMode(true, $session);
        $this->assertFalse($session->timeout(false));
        $this->assertFalse($session->timeout());
        $this->assertSame(null, $activityProperty->getValue($session));
        $this->assertWriteMode(true, $session);
    }

    /**
     * @covers ::timeout
     */
    public function testTimeoutInvalidType()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');

        $session = new Session($this->sessions, null, [
            'timeout' => 1234
        ]);
        $session->timeout('after an hour');
    }

    /**
     * @covers ::timeout
     */
    public function testTimeoutInvalidTimeout1()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');

        $session = new Session($this->sessions, null, [
            'timeout' => 1234
        ]);
        $session->timeout(-10);
    }

    /**
     * @covers ::timeout
     */
    public function testTimeoutInvalidTimeout2()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');

        $session = new Session($this->sessions, null, [
            'timeout' => 1234
        ]);
        $session->timeout(0);
    }

    /**
     * @covers ::renewable
     * @covers ::token
     * @covers ::commit
     * @covers ::regenerateToken
     * @covers ::autoRenew
     * @covers ::needsRenewal
     */
    public function testRenewable()
    {
        $session = new Session($this->sessions, null, [
            'expiryTime' => '+ 1 minute',
            'renewable'  => true
        ]);
        $session->regenerateToken();
        $session->commit();
        $originalToken = $session->token();

        $this->assertTrue($session->renewable());
        $this->assertWriteMode(false, $session);
        $this->assertFalse($session->renewable(false));
        $this->assertFalse($session->renewable());
        $this->assertWriteMode(true, $session);
        $this->assertSame($originalToken, $session->token());

        // re-enabling and disabling shouldn't do anything at first
        $this->assertTrue($session->renewable(true));
        $this->assertTrue($session->renewable());
        $this->assertWriteMode(true, $session);
        $this->assertSame($originalToken, $session->token());
        $this->assertFalse($session->renewable(false));
        $this->assertFalse($session->renewable());
        $this->assertWriteMode(true, $session);
        $this->assertSame($originalToken, $session->token());

        // make time pass by more than half the session duration
        MockTime::$time = 1337000040;

        // verify that automatic renewing happens when re-enabling
        $this->assertTrue($session->renewable(true));
        $this->assertTrue($session->renewable());
        $this->assertWriteMode(true, $session);
        $this->assertNotSame($originalToken, $newToken = $session->token());
        $this->assertTrue($session->renewable(true));
        $this->assertTrue($session->renewable());
        $this->assertWriteMode(true, $session);
        $this->assertSame($newToken, $session->token());
    }

    /**
     * @covers ::__call
     * @covers ::data
     */
    public function testDataMethods()
    {
        $session = new Session($this->sessions, null, []);
        $session->data()->reload(['someString' => 'someValue', 'someInt' => 123]);

        // get
        $this->assertSame('someValue', $session->get('someString', 'some default'));
        $this->assertSame('some default', $session->get('someOtherString', 'some default'));

        // set
        $session->set('someString', 'someOtherValue');
        $this->assertSame('someOtherValue', $session->data()->get('someString'));

        // increment
        $session->increment('someInt', 10);
        $this->assertSame(133, $session->data()->get('someInt'));

        // decrement
        $session->decrement('someInt', 20);
        $this->assertSame(113, $session->data()->get('someInt'));

        // pull
        $this->assertSame('someOtherValue', $session->pull('someString', 'some default'));
        $this->assertSame('some default', $session->data()->get('someString', 'some default'));

        // remove
        $session->remove('someInt');
        $this->assertNull($session->data()->get('someInt'));

        // clear
        $session->data()->reload(['someString' => 'someValue']);
        $this->assertSame(['someString' => 'someValue'], $session->get());
        $session->clear();
        $this->assertSame([], $session->get());
    }

    /**
     * @covers ::__call
     */
    public function testInvalidMethod()
    {
        $this->expectException('Kirby\Exception\BadMethodCallException');

        $session = new Session($this->sessions, null, []);
        $session->someGibberish();
    }

    /**
     * @covers ::commit
     * @covers ::data
     * @covers ::commit
     */
    public function testCommit()
    {
        $token = '9999999999.valid.' . $this->store->validKey;
        $session = new Session($this->sessions, $token, []);

        $this->assertWriteMode(false, $session);
        $this->assertFalse(isset($this->store->isLocked['9999999999.valid']));
        $session->renewable(true);
        $session->timeout(3600);
        $session->data()->set('id', 'someOtherId');
        $session->data()->set('someKey', 'someValue');
        $this->assertWriteMode(true, $session);
        $this->assertTrue(isset($this->store->isLocked['9999999999.valid']));

        $session->commit();
        $this->assertWriteMode(false, $session);
        $this->assertFalse(isset($this->store->isLocked['9999999999.valid']));

        $this->assertSame([
            'startTime'    => 0,
            'expiryTime'   => 9999999999,
            'duration'     => 9999999999,
            'timeout'      => 3600,
            'lastActivity' => 1337000000, // timestamp is from mock
            'renewable'    => true,
            'data' => [
                'id'      => 'someOtherId',
                'someKey' => 'someValue'
            ]
        ], $this->store->sessions['9999999999.valid']);

        // set different data like in a "different thread"
        $this->store->sessions['9999999999.valid'] = [
            'startTime'    => 0,
            'expiryTime'   => 9999999999,
            'duration'     => 9999999999,
            'timeout'      => 1234,
            'lastActivity' => 1337000000, // timestamp is from mock
            'renewable'    => true,
            'data' => [
                'id'      => 'someOtherId',
                'someKey' => 'aDifferentValue'
            ]
        ];
        unset($this->store->hmacs['9999999999.valid']);

        // re-init test after committing
        $session->renewable(false);
        $this->assertWriteMode(true, $session);
        $this->assertTrue(isset($this->store->isLocked['9999999999.valid']));
        $this->assertSame(1234, $session->timeout());
        $this->assertSame(false, $session->renewable());
        $this->assertSame('aDifferentValue', $session->data()->get('someKey'));

        $session->commit();
        $this->assertWriteMode(false, $session);
        $this->assertFalse(isset($this->store->isLocked['9999999999.valid']));

        $this->assertSame([
            'startTime'    => 0,
            'expiryTime'   => 9999999999,
            'duration'     => 9999999999,
            'timeout'      => 1234,
            'lastActivity' => 1337000000, // timestamp is from mock
            'renewable'    => false,
            'data' => [
                'id'      => 'someOtherId',
                'someKey' => 'aDifferentValue'
            ]
        ], $this->store->sessions['9999999999.valid']);
    }

    /**
     * @covers ::destroy
     * @covers ::data
     * @covers ::commit
     * @covers ::regenerateToken
     */
    public function testDestroy()
    {
        $token = '9999999999.valid2.' . $this->store->validKey;
        Cookie::set('kirby_session', $token);
        $session = new Session($this->sessions, $token, []);

        $this->assertTrue(isset($this->store->sessions['9999999999.valid2']));
        $this->assertTrue(Cookie::exists('kirby_session'));
        $session->destroy();
        $this->assertFalse(isset($this->store->sessions['9999999999.valid2']));
        $this->assertFalse(Cookie::exists('kirby_session'));

        // the instance should now be inactive
        $session->data()->set('someKey', 'someValue');
        $session->regenerateToken();
        $session->expiryTime('tomorrow');
        $session->renew();
        $session->commit();
        $this->assertFalse(isset($this->store->sessions['9999999999.valid2']));

        // the cookie shouldn't be deleted in header/manual mode
        $token = '9999999999.valid.' . $this->store->validKey;
        Cookie::set('kirby_session', $token);
        $session = new Session($this->sessions, $token, ['mode' => 'manual']);
        $session->destroy();
        $this->assertTrue(Cookie::exists('kirby_session'));
        $this->assertFalse(isset($this->store->sessions['9999999999.valid']));
    }

    /**
     * @covers ::renew
     * @covers ::token
     * @covers ::commit
     * @covers ::regenerateToken
     * @covers ::regenerateTokenIfNotNew
     */
    public function testRenew()
    {
        $session = new Session($this->sessions, null, [
            'startTime'  => 1000000000,
            'expiryTime' => 9999999999
        ]);
        $session->regenerateToken();
        $session->commit();
        $originalToken = $session->token();
        $this->assertWriteMode(false, $session);

        $this->assertSame(9999999999, $session->expiryTime());
        $this->assertSame(8999999999, $session->duration());
        $session->renew();
        $this->assertSame(1337000000 + 8999999999, $session->expiryTime()); // timestamp is from mock
        $this->assertSame(8999999999, $session->duration());
        $this->assertWriteMode(true, $session);
        $this->assertNotSame($originalToken, $session->token());

        // validate that the old session now references the new one
        $oldTokenParts = explode('.', $originalToken);
        $newTokenParts = explode('.', $session->token());
        $this->assertSame([
            'startTime'  => 1000000000,
            'expiryTime' => 1337000000 + 30, // timestamp is from mock
            'newSession' => $newTokenParts[0] . '.' . $newTokenParts[1]
        ], $this->store->sessions['9999999999.' . $oldTokenParts[1]]);
    }

    /**
     * @covers ::renew
     */
    public function testRenewNotRenewable()
    {
        $this->expectException('Kirby\Exception\LogicException');
        $this->expectExceptionCode('error.session.notRenewable');

        $token = '2000000000.nonRenewable.' . $this->store->validKey;

        $session = new Session($this->sessions, $token, []);
        $session->renew();
    }

    /**
     * @covers ::regenerateToken
     * @covers ::token
     * @covers ::startTime
     */
    public function testRegenerateToken()
    {
        $sessionsReflector = new ReflectionClass(Sessions::class);
        $cache = $sessionsReflector->getProperty('cache');
        $cache->setAccessible(true);

        $token = '9999999999.valid.' . $this->store->validKey;
        $session = new Session($this->sessions, $token, []);

        $this->assertWriteMode(false, $session);
        $session->regenerateToken();
        $this->assertNotSame($token, $newToken = $session->token());
        $this->assertSame(0, $session->startTime());
        $this->assertSame(9999999999, $session->expiryTime());
        $this->assertWriteMode(true, $session);

        // validate that all parts of the token have been regenerated
        $newTokenParts = explode('.', $newToken);
        $this->assertSame('9999999999', $newTokenParts[0]);
        $this->assertNotSame('valid', $newTokenParts[1]);
        $this->assertStringMatchesFormat('%x', $newTokenParts[1]);
        $this->assertNotSame($this->store->validKey, $newTokenParts[2]);

        // validate that the old session now references the new one
        $this->assertSame([
            'startTime'  => 0,
            'expiryTime' => 1337000000 + 30, // timestamp is from mock
            'newSession' => $newTokenParts[0] . '.' . $newTokenParts[1]
        ], $this->store->sessions['9999999999.valid']);

        // validate that a cookie has been set
        $this->assertSame($newToken, Cookie::get('kirby_session'));

        // validate that the new session is cached in the $sessions object
        $this->assertArrayHasKey($newToken, $cache->getValue($this->sessions));
        $this->assertSame($session, $cache->getValue($this->sessions)[$newToken]);
    }

    /**
     * @covers ::regenerateToken
     * @covers ::needsRetransmission
     */
    public function testRegenerateTokenHeaderMode()
    {
        $sessionsReflector = new ReflectionClass(Sessions::class);
        $cache = $sessionsReflector->getProperty('cache');
        $cache->setAccessible(true);

        $token = '9999999999.valid.' . $this->store->validKey;
        $session = new Session($this->sessions, $token, ['mode' => 'header']);

        Cookie::remove('kirby_session');
        $this->assertFalse($session->needsRetransmission());
        $session->regenerateToken();
        $this->assertNotSame($token, $newToken = $session->token());
        $this->assertTrue($session->needsRetransmission());
        $this->assertNull(Cookie::get('kirby_session'));

        // validate that the new session is cached in the $sessions object
        $this->assertArrayHasKey($newToken, $cache->getValue($this->sessions));
        $this->assertSame($session, $cache->getValue($this->sessions)[$newToken]);
    }

    /**
     * @covers ::__destruct
     * @covers ::data
     */
    public function testDestruct()
    {
        $token = '9999999999.valid.' . $this->store->validKey;
        $session = new Session($this->sessions, $token, []);

        $this->assertWriteMode(false, $session);
        $session->data()->set('someId', 1);
        $this->assertWriteMode(true, $session);

        $this->assertFalse(isset($this->store->sessions['9999999999.valid']['data']['someId']));
        $session->__destruct(); // actually destructing is not possible in the test (we need to access the store later)
        $this->assertSame(1, $this->store->sessions['9999999999.valid']['data']['someId']);
    }

    /**
     * @covers ::prepareForWriting
     * @covers ::data
     */
    public function testPrepareForWriting()
    {
        $token = '9999999999.valid.' . $this->store->validKey;
        $session = new Session($this->sessions, $token, []);

        $this->assertWriteMode(false, $session);
        $this->assertFalse(isset($this->store->isLocked['9999999999.valid']));
        $this->assertSame(null, $session->data()->get('someId'));

        // manually overwrite some data like another thread would do
        $this->store->sessions['9999999999.valid']['data']['someId'] = 123;
        $this->assertWriteMode(false, $session);
        $this->assertFalse(isset($this->store->isLocked['9999999999.valid']));
        $this->assertSame(null, $session->data()->get('someId'));

        // now trigger a reload of the session by setting a value
        $session->data()->increment('someId', 1);
        $this->assertWriteMode(true, $session);
        $this->assertTrue(isset($this->store->isLocked['9999999999.valid']));
        $this->assertSame(124, $session->data()->get('someId'));
    }

    /**
     * @covers ::prepareForWriting
     * @covers ::data
     */
    public function testPrepareForWritingReadonly()
    {
        $this->expectException('Kirby\Exception\LogicException');
        $this->expectExceptionCode('error.session.readonly');

        $token = '9999999999.moved.' . $this->store->validKey;
        $session = new Session($this->sessions, $token, []);

        $session->data()->set('someId', 1);
    }

    /**
     * @covers ::parseToken
     * @covers ::token
     */
    public function testParseToken()
    {
        $reflector = new ReflectionClass(Session::class);
        $parseToken = $reflector->getMethod('parseToken');
        $parseToken->setAccessible(true);
        $tokenKey = $reflector->getProperty('tokenKey');
        $tokenKey->setAccessible(true);

        $session = new Session($this->sessions, null, []);
        $this->assertNull($session->token());

        // full token
        $parseToken->invoke($session, '1234567890.thisIsMyAwesomeId.' . $this->store->validKey);
        $this->assertSame('1234567890.thisIsMyAwesomeId.' . $this->store->validKey, $session->token());
        $this->assertSame($this->store->validKey, $tokenKey->getValue($session));

        // token without key
        $parseToken->invoke($session, '1234567890.thisIsMyAwesomeId', true);
        $this->assertSame('1234567890.thisIsMyAwesomeId', $session->token());
        $this->assertNull($tokenKey->getValue($session));
    }

    /**
     * @covers ::parseToken
     */
    public function testParseTokenInvalidToken1()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');

        $token = '9999999999.thisIsNotAValidToken';
        $session = new Session($this->sessions, $token, []);
    }

    /**
     * @covers ::parseToken
     */
    public function testParseTokenInvalidToken2()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');

        $token = 'something.thisIsNotAValidToken.abcdefabcdef';
        $session = new Session($this->sessions, $token, []);
    }

    /**
     * @covers ::parseToken
     * @covers ::token
     */
    public function testParseTokenInvalidToken3()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');

        $reflector = new ReflectionClass(Session::class);
        $parseToken = $reflector->getMethod('parseToken');
        $parseToken->setAccessible(true);

        $session = new Session($this->sessions, null, []);
        $this->assertNull($session->token());

        $parseToken->invoke($session, '1234567890.thisIsMyAwesomeId.' . $this->store->validKey, true);
    }

    /**
     * @covers ::timeToTimestamp
     */
    public function testTimeToTimestamp()
    {
        $reflector = new ReflectionClass(Session::class);
        $timeToTimestamp = $reflector->getMethod('timeToTimestamp');
        $timeToTimestamp->setAccessible(true);

        $this->assertSame(1234567890, $timeToTimestamp->invoke(null, 1234567890));
        $this->assertSame(1234567890, $timeToTimestamp->invoke(null, 1234567890, 1357924680));
        $this->assertSame(1514764800, $timeToTimestamp->invoke(null, '2018-01-01T00:00:00+00:00', 1357924680));
        $this->assertSame(strtotime('tomorrow', 1337000000), $timeToTimestamp->invoke(null, 'tomorrow')); // timestamp is from mock
        $this->assertSame(strtotime('tomorrow', 1357924680), $timeToTimestamp->invoke(null, 'tomorrow', 1357924680));
    }

    /**
     * @covers ::timeToTimestamp
     */
    public function testTimeToTimestampInvalidParam()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');

        $reflector = new ReflectionClass(Session::class);
        $timeToTimestamp = $reflector->getMethod('timeToTimestamp');
        $timeToTimestamp->setAccessible(true);

        $timeToTimestamp->invoke(null, ['tomorrow']);
    }

    /**
     * @covers ::timeToTimestamp
     */
    public function testTimeToTimestampInvalidTimeString()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');

        $reflector = new ReflectionClass(Session::class);
        $timeToTimestamp = $reflector->getMethod('timeToTimestamp');
        $timeToTimestamp->setAccessible(true);

        $timeToTimestamp->invoke(null, 'some gibberish that is definitely no valid time');
    }

    /**
     * @covers ::__construct
     * @covers ::init
     * @covers ::token
     * @covers ::startTime
     * @covers ::data
     * @covers ::autoRenew
     * @covers ::needsRenewal
     */
    public function testInit()
    {
        $token = '9999999999.valid.' . $this->store->validKey;

        $session = new Session($this->sessions, $token, []);
        $this->assertSame($token, $session->token());
        $this->assertSame(0, $session->startTime());
        $this->assertSame(9999999999, $session->expiryTime());
        $this->assertSame(9999999999, $session->duration());
        $this->assertFalse($session->timeout());
        $this->assertFalse($session->renewable());
        $this->assertSame('valid', $session->data()->get('id'));
    }

    /**
     * @covers ::__construct
     * @covers ::init
     * @covers ::data
     * @covers ::commit
     */
    public function testInitSerializedObject()
    {
        $token = '9999999999.valid.' . $this->store->validKey;

        $obj = new Obj([
            'test-key' => 'test-value'
        ]);

        $session = new Session($this->sessions, $token, []);
        $session->data()->set('name', 'test-session');
        $session->data()->set('obj', $obj);
        $this->assertWriteMode(true, $session);
        $this->assertInstanceOf(Obj::class, $session->data()->get('obj'));
        $this->assertSame($obj, $session->data()->get('obj'));
        $this->assertTrue($obj === $session->data()->get('obj'));
        $session->commit();
        $this->assertWriteMode(false, $session);

        $session = new Session($this->sessions, $token, []);
        $this->assertSame('test-session', $session->data()->get('name'));
        $this->assertInstanceOf(Obj::class, $session->data()->get('obj'));
        $this->assertEquals($obj, $session->data()->get('obj')); // cannot use strict test
        $this->assertFalse($obj === $session->data()->get('obj'));
    }

    /**
     * @covers ::__construct
     * @covers ::init
     */
    public function testInitNonExisting()
    {
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionCode('error.session.notFound');

        $token = '9999999999.nonExisting.' . $this->store->validKey;
        new Session($this->sessions, $token, []);
    }

    /**
     * @covers ::__construct
     * @covers ::init
     */
    public function testInitWrongKey()
    {
        $this->expectException('Kirby\Exception\LogicException');
        $this->expectExceptionCode('error.session.invalid');

        $token = '9999999999.valid.' . $this->store->invalidKey;
        new Session($this->sessions, $token, []);
    }

    /**
     * @covers ::__construct
     * @covers ::init
     */
    public function testInitMissingKey()
    {
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid argument "$token" in method "Session::parseToken"');

        $token = '9999999999.valid';
        new Session($this->sessions, $token, []);
    }

    /**
     * @covers ::__construct
     * @covers ::init
     */
    public function testInitInvalidSerialization()
    {
        $this->expectException('Kirby\Exception\LogicException');
        $this->expectExceptionCode('error.session.invalid');

        $token = '9999999999.invalidSerialization.' . $this->store->validKey;
        new Session($this->sessions, $token, []);
    }

    /**
     * @covers ::__construct
     * @covers ::init
     */
    public function testInitInvalidStructure()
    {
        $this->expectException('Kirby\Exception\LogicException');
        $this->expectExceptionCode('error.session.invalid');

        $token = '9999999999.invalidStructure.' . $this->store->validKey;
        new Session($this->sessions, $token, []);
    }

    /**
     * @covers ::__construct
     * @covers ::init
     */
    public function testInitExpired()
    {
        $this->expectException('Kirby\Exception\LogicException');
        $this->expectExceptionCode('error.session.invalid');

        $token = '1000000000.expired.' . $this->store->validKey;
        new Session($this->sessions, $token, []);
    }

    /**
     * @covers ::__construct
     * @covers ::init
     */
    public function testInitNotStarted()
    {
        $this->expectException('Kirby\Exception\LogicException');
        $this->expectExceptionCode('error.session.invalid');

        $token = '9999999999.notStarted.' . $this->store->validKey;
        new Session($this->sessions, $token, []);
    }

    /**
     * @covers ::__construct
     * @covers ::init
     * @covers ::token
     * @covers ::startTime
     * @covers ::data
     */
    public function testInitMoved()
    {
        // moved session: data should be identical to the actual one
        $token = '9999999999.moved.' . $this->store->validKey;

        $session = new Session($this->sessions, $token, []);
        $this->assertSame('9999999999.valid', $session->token());
        $this->assertSame(0, $session->startTime());
        $this->assertSame(9999999999, $session->expiryTime());
        $this->assertSame(9999999999, $session->duration());
        $this->assertFalse($session->timeout());
        $this->assertFalse($session->renewable());
        $this->assertSame('valid', $session->data()->get('id'));
    }

    /**
     * @covers ::__construct
     * @covers ::init
     * @covers ::token
     * @covers ::startTime
     * @covers ::data
     */
    public function testInitMovedRenewal()
    {
        // moved session: data should be identical to the actual one
        $token = '9999999999.movedRenewal.' . $this->store->validKey;

        $session = new Session($this->sessions, $token, []);
        $this->assertSame('2000000000.renewal', $session->token());
        $this->assertSame(0, $session->startTime());
        $this->assertSame(2000000000, $session->expiryTime());
        $this->assertSame(2000000000, $session->duration());
        $this->assertFalse($session->timeout());
        $this->assertTrue($session->renewable());
        $this->assertSame('renewal', $session->data()->get('id'));

        // new session should *not* be renewed because the new session is read-only
        $this->assertWriteMode(false, $session);
    }

    /**
     * @covers ::__construct
     * @covers ::init
     */
    public function testInitMovedManualRenewal()
    {
        $this->expectException('Kirby\Exception\LogicException');
        $this->expectExceptionCode('error.session.readonly');

        $token = '9999999999.movedRenewal.' . $this->store->validKey;

        $session = new Session($this->sessions, $token, []);
        $session->renew();
    }

    /**
     * @covers ::__construct
     * @covers ::init
     * @covers ::token
     * @covers ::startTime
     * @covers ::data
     */
    public function testInitTimeoutActivity()
    {
        // moved session: data should be identical to the actual one
        $token = '9999999999.movedTimeoutActivity.' . $this->store->validKey;

        $session = new Session($this->sessions, $token, []);
        $this->assertSame('9999999999.timeoutActivity2', $session->token());
        $this->assertSame(0, $session->startTime());
        $this->assertSame(9999999999, $session->expiryTime());
        $this->assertSame(9999999999, $session->duration());
        $this->assertSame(3600, $session->timeout());
        $this->assertFalse($session->renewable());
        $this->assertSame('timeoutActivity2', $session->data()->get('id'));

        // new session should *not* be refreshed because the new session is read-only
        $this->assertWriteMode(false, $session);
    }

    /**
     * @covers ::__construct
     * @covers ::init
     */
    public function testInitMovedExpired()
    {
        $this->expectException('Kirby\Exception\LogicException');
        $this->expectExceptionCode('error.session.invalid');

        $token = '1000000000.movedExpired.' . $this->store->validKey;
        new Session($this->sessions, $token, []);
    }

    /**
     * @covers ::__construct
     * @covers ::init
     */
    public function testInitMovedInvalid()
    {
        $this->expectException('Kirby\Exception\NotFoundException');
        $this->expectExceptionCode('error.session.notFound');

        $token = '9999999999.movedInvalid.' . $this->store->validKey;
        new Session($this->sessions, $token, []);
    }

    /**
     * @covers ::__construct
     * @covers ::init
     */
    public function testInitMovedWrongKey()
    {
        $this->expectException('Kirby\Exception\LogicException');
        $this->expectExceptionCode('error.session.invalid');

        $token = '9999999999.moved.' . $this->store->invalidKey;
        new Session($this->sessions, $token, []);
    }

    /**
     * @covers ::__construct
     * @covers ::init
     */
    public function testInitExpiredTimeout()
    {
        $this->expectException('Kirby\Exception\LogicException');
        $this->expectExceptionCode('error.session.invalid');

        $token = '9999999999.timeout.' . $this->store->validKey;
        new Session($this->sessions, $token, []);
    }

    /**
     * @covers ::__construct
     * @covers ::init
     * @covers ::token
     * @covers ::startTime
     * @covers ::data
     */
    public function testInitAutoActivity1()
    {
        $token = '9999999999.timeoutActivity1.' . $this->store->validKey;

        $session = new Session($this->sessions, $token, []);
        $this->assertSame($token, $session->token());
        $this->assertSame(0, $session->startTime());
        $this->assertSame(9999999999, $session->expiryTime());
        $this->assertSame(9999999999, $session->duration());
        $this->assertSame(3600, $session->timeout());
        $this->assertFalse($session->renewable());
        $this->assertSame('timeoutActivity1', $session->data()->get('id'));

        $reflector = new ReflectionClass(Session::class);
        $activityProperty = $reflector->getProperty('lastActivity');
        $activityProperty->setAccessible(true);
        $this->assertSame($session->data()->get('expectedActivity'), $activityProperty->getValue($session));
    }

    /**
     * @covers ::__construct
     * @covers ::init
     * @covers ::token
     * @covers ::startTime
     * @covers ::data
     */
    public function testInitAutoActivity2()
    {
        $token = '9999999999.timeoutActivity2.' . $this->store->validKey;

        $session = new Session($this->sessions, $token, []);
        $this->assertSame($token, $session->token());
        $this->assertSame(0, $session->startTime());
        $this->assertSame(9999999999, $session->expiryTime());
        $this->assertSame(9999999999, $session->duration());
        $this->assertSame(3600, $session->timeout());
        $this->assertFalse($session->renewable());
        $this->assertSame('timeoutActivity2', $session->data()->get('id'));

        $reflector = new ReflectionClass(Session::class);
        $activityProperty = $reflector->getProperty('lastActivity');
        $activityProperty->setAccessible(true);
        $newActivity = $activityProperty->getValue($session);
        $this->assertGreaterThan($session->data()->get('expectedActivity') - 5, $newActivity);
        $this->assertLessThan($session->data()->get('expectedActivity') + 5, $newActivity);
    }

    /**
     * @covers ::__construct
     * @covers ::init
     * @covers ::token
     * @covers ::startTime
     * @covers ::data
     */
    public function testInitAutoRenew()
    {
        $token = '.renewal.' . $this->store->validKey;

        $session = new Session($this->sessions, '2000000000' . $token, []);
        $newToken = $session->token();
        $newTokenExpiry = (int)Str::before($newToken, '.');

        $this->assertSame(1337000000 + 2000000000, $newTokenExpiry); // timestamp is from mock
        $this->assertSame(0, $session->startTime());
        $this->assertSame($newTokenExpiry, $session->expiryTime());
        $this->assertSame(2000000000, $session->duration());
        $this->assertFalse($session->timeout());
        $this->assertTrue($session->renewable());
        $this->assertSame('renewal', $session->data()->get('id'));
    }

    /**
     * @covers ::__construct
     * @covers ::init
     * @covers ::token
     * @covers ::startTime
     * @covers ::data
     */
    public function testInitNonRenewable()
    {
        $token = '2000000000.nonRenewable.' . $this->store->validKey;

        $session = new Session($this->sessions, $token, []);
        $this->assertSame($token, $session->token());
        $this->assertSame(0, $session->startTime());
        $this->assertSame(2000000000, $session->expiryTime());
        $this->assertSame(2000000000, $session->duration());
        $this->assertFalse($session->timeout());
        $this->assertFalse($session->renewable());
        $this->assertSame('nonRenewable', $session->data()->get('id'));
    }

    /**
     * Asserts the state of the write mode of the given session
     *
     * @param bool $expected Whether the write mode should be true or false right now
     * @param Session $session
     * @return void
     */
    protected function assertWriteMode(bool $expected, Session $session)
    {
        $reflector = new ReflectionClass(Session::class);
        $writeModeProperty = $reflector->getProperty('writeMode');
        $writeModeProperty->setAccessible(true);

        $this->assertSame($expected, $writeModeProperty->getValue($session));
    }
}
