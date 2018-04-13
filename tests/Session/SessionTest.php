<?php

namespace Kirby\Session;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Kirby\Http\Cookie;
use Kirby\Util\Str;

require_once(__DIR__ . '/mocks.php');

class SessionTest extends TestCase
{
    protected $store;
    protected $sessions;

    public function setUp()
    {
        $this->store    = new TestSessionStore();
        $this->sessions = new Sessions($this->store);
    }

    public function tearDown()
    {
        unset($this->sessions);
        unset($this->store);
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testConstructInvalidToken()
    {
        new Session($this->sessions, 123, []);
    }

    public function testCreate()
    {
        $time = time();
        $reflector = new ReflectionClass(Session::class);
        $activityProperty = $reflector->getProperty('lastActivity');
        $activityProperty->setAccessible(true);

        // defaults
        $session = new Session($this->sessions, null, []);
        $this->assertEquals($time, $session->startTime());
        $this->assertEquals($time + 7200, $session->expiryTime());
        $this->assertEquals(7200, $session->duration());
        $this->assertEquals(1800, $session->timeout());
        $this->assertEquals($time, $activityProperty->getValue($session));
        $this->assertEquals(true, $session->renewable());
        $this->assertEquals([], $session->data()->get());
        $this->assertNull($session->token());
        $this->assertWriteMode(false, $session);

        // custom values
        $session = new Session($this->sessions, null, [
            'startTime'  => $time + 60,
            'expiryTime' => '+ 1 hour',
            'timeout'    => false,
            'renewable'  => false
        ]);
        $this->assertEquals($time + 60, $session->startTime());
        $this->assertEquals($time + 3660, $session->expiryTime());
        $this->assertEquals(3600, $session->duration());
        $this->assertFalse($session->timeout());
        $this->assertEquals(null, $activityProperty->getValue($session));
        $this->assertEquals(false, $session->renewable());
        $this->assertEquals([], $session->data()->get());
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
        $this->assertInternalType('string', $session->token());
        $this->assertWriteMode(true, $session);

        $token = explode('.', $session->token());
        $this->assertTrue(isset($this->store->sessions[$token[0] . '.' . $token[1]]));
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testCreateInvalidExpiry()
    {
        new Session($this->sessions, null, [
            'startTime'  => time() - 3600,
            'expiryTime' => time() - 1800
        ]);
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testCreateInvalidDuration()
    {
        new Session($this->sessions, null, [
            'startTime'  => time() + 60,
            'expiryTime' => time()
        ]);
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testCreateInvalidTimeout()
    {
        new Session($this->sessions, null, [
            'timeout' => 'at some point'
        ]);
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testCreateInvalidRenewable()
    {
        new Session($this->sessions, null, [
            'renewable' => 'maybe'
        ]);
    }

    public function testMode()
    {
        $session = new Session($this->sessions, null, [
            'mode' => 'manual'
        ]);

        $this->assertEquals('manual', $session->mode());
        $this->assertEquals('cookie', $session->mode('cookie'));
        $this->assertEquals('cookie', $session->mode());
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testModeStartedSession()
    {
        $session = new Session($this->sessions, null, [
            'mode' => 'manual'
        ]);
        $session->data()->set('someKey', 'someValue');

        $this->assertEquals('manual', $session->mode());
        $session->mode('cookie');
    }

    public function testExpiryTime()
    {
        $session = new Session($this->sessions, null, [
            'startTime'  => 1000000000,
            'expiryTime' => 7777777777
        ]);
        $session->regenerateToken();
        $session->commit();
        $originalToken = $session->token();

        $this->assertEquals(7777777777, $session->expiryTime());
        $this->assertEquals(6777777777, $session->duration());
        $this->assertWriteMode(false, $session);
        $this->assertEquals(9999999999, $session->expiryTime(9999999999));
        $this->assertWriteMode(true, $session);
        $this->assertNotEquals($originalToken, $session->token());
        $originalToken = $session->token();
        $this->assertEquals(9999999999, $session->expiryTime());
        $this->assertEquals(time() + 3600, $session->expiryTime('+ 1 hour'));
        $this->assertEquals(time() + 3600, $session->expiryTime());
        $this->assertEquals(3600, $session->duration());
        $this->assertWriteMode(true, $session);
        $this->assertNotEquals($originalToken, $session->token());
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testExpiryTimeInvalidType()
    {
        $session = new Session($this->sessions, null, [
            'startTime'  => 1000000000,
            'expiryTime' => 7777777777
        ]);
        $session->expiryTime(false);
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testExpiryTimeInvalidString()
    {
        $session = new Session($this->sessions, null, [
            'startTime'  => 1000000000,
            'expiryTime' => 7777777777
        ]);
        $session->expiryTime('some gibberish that is definitely no valid time');
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testExpiryTimeInvalidTime1()
    {
        $session = new Session($this->sessions, null, [
            'startTime'  => 1000000000,
            'expiryTime' => 7777777777
        ]);
        $session->expiryTime(time() - 1);
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testExpiryTimeInvalidTime2()
    {
        $session = new Session($this->sessions, null, [
            'startTime'  => 1000000000,
            'expiryTime' => 7777777777
        ]);
        $session->expiryTime(time());
    }

    public function testDuration()
    {
        $session = new Session($this->sessions, null, [
            'startTime'  => 1000000000,
            'expiryTime' => 7777777777
        ]);
        $session->regenerateToken();
        $session->commit();
        $originalToken = $session->token();

        $this->assertEquals(7777777777, $session->expiryTime());
        $this->assertEquals(6777777777, $session->duration());
        $this->assertWriteMode(false, $session);
        $this->assertEquals(3600, $session->duration(3600));
        $this->assertEquals(3600, $session->duration());
        $this->assertEquals(time() + 3600, $session->expiryTime());
        $this->assertWriteMode(true, $session);
        $this->assertNotEquals($originalToken, $session->token());
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testDurationInvalidTime1()
    {
        $session = new Session($this->sessions, null, [
            'startTime'  => 1000000000,
            'expiryTime' => 7777777777
        ]);
        $session->duration(-1);
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testDurationInvalidTime2()
    {
        $session = new Session($this->sessions, null, [
            'startTime'  => 1000000000,
            'expiryTime' => 7777777777
        ]);
        $session->duration(0);
    }

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

        $this->assertEquals(1234, $session->timeout());
        $this->assertWriteMode(false, $session);
        $this->assertEquals(4321, $session->timeout(4321));
        $this->assertEquals(4321, $session->timeout());
        $this->assertEquals(time(), $activityProperty->getValue($session));
        $this->assertWriteMode(true, $session);
        $this->assertFalse($session->timeout(false));
        $this->assertFalse($session->timeout());
        $this->assertEquals(null, $activityProperty->getValue($session));
        $this->assertWriteMode(true, $session);
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testTimeoutInvalidType()
    {
        $session = new Session($this->sessions, null, [
            'timeout' => 1234
        ]);
        $session->timeout('after an hour');
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testTimeoutInvalidTimeout1()
    {
        $session = new Session($this->sessions, null, [
            'timeout' => 1234
        ]);
        $session->timeout(-10);
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testTimeoutInvalidTimeout2()
    {
        $session = new Session($this->sessions, null, [
            'timeout' => 1234
        ]);
        $session->timeout(0);
    }

    public function testRenewable()
    {
        $session = new Session($this->sessions, null, [
            'expiryTime' => '+ 1 second',
            'renewable'  => true
        ]);
        $session->regenerateToken();
        $session->commit();
        $originalToken = $session->token();
        sleep(1); // make sure that the session is at least half expired

        $this->assertTrue($session->renewable());
        $this->assertWriteMode(false, $session);
        $this->assertFalse($session->renewable(false));
        $this->assertFalse($session->renewable());
        $this->assertWriteMode(true, $session);
        $this->assertEquals($originalToken, $session->token());

        // verify that automatic renewing happens when re-enabling
        $this->assertTrue($session->renewable(true));
        $this->assertTrue($session->renewable());
        $this->assertWriteMode(true, $session);
        $this->assertNotEquals($originalToken, $session->token());
        $newToken = $session->token();
        $this->assertTrue($session->renewable(true));
        $this->assertTrue($session->renewable());
        $this->assertWriteMode(true, $session);
        $this->assertEquals($newToken, $session->token());
    }

    public function testDataMethods()
    {
        $session = new Session($this->sessions, null, []);
        $session->data()->reload(['someString' => 'someValue', 'someInt' => 123]);

        // get
        $this->assertEquals('someValue', $session->get('someString', 'some default'));
        $this->assertEquals('some default', $session->get('someOtherString', 'some default'));

        // set
        $session->set('someString', 'someOtherValue');
        $this->assertEquals('someOtherValue', $session->data()->get('someString'));

        // increment
        $session->increment('someInt', 10);
        $this->assertEquals(133, $session->data()->get('someInt'));

        // decrement
        $session->decrement('someInt', 20);
        $this->assertEquals(113, $session->data()->get('someInt'));

        // pull
        $this->assertEquals('someOtherValue', $session->pull('someString', 'some default'));
        $this->assertEquals('some default', $session->data()->get('someString', 'some default'));

        // remove
        $session->remove('someInt');
        $this->assertNull($session->data()->get('someInt'));

        // clear
        $session->data()->reload(['someString' => 'someValue']);
        $this->assertEquals(['someString' => 'someValue'], $session->get());
        $session->clear();
        $this->assertEquals([], $session->get());
    }

    /**
     * @expectedException Kirby\Exception\BadMethodCallException
     */
    public function testInvalidMethod()
    {
        $session = new Session($this->sessions, null, []);
        $session->someGibberish();
    }

    public function testCommit()
    {
        $token = '9999999999.valid.' . $this->store->validKey;
        $session = new Session($this->sessions, $token, []);

        $this->assertWriteMode(false, $session);
        $this->assertFalse(isset($this->store->isLocked['9999999999.valid']));
        $session->renewable(true);
        $time = time();
        $session->timeout(3600);
        $session->data()->set('id', 'someOtherId');
        $session->data()->set('someKey', 'someValue');
        $this->assertWriteMode(true, $session);
        $this->assertTrue(isset($this->store->isLocked['9999999999.valid']));

        $session->commit();
        $this->assertWriteMode(false, $session);
        $this->assertFalse(isset($this->store->isLocked['9999999999.valid']));

        $data = $this->store->get(9999999999, 'valid');
        $data = Crypto::decrypt($data, $this->store->keyObject, true);
        $data = json_decode($data, true);
        $this->assertEquals([
            'startTime'    => 0,
            'expiryTime'   => 9999999999,
            'duration'     => 9999999999,
            'timeout'      => 3600,
            'lastActivity' => $time,
            'renewable'    => true,
            'data' => [
                'id'      => 'someOtherId',
                'someKey' => 'someValue'
            ]
        ], $data);

        // set different data like in a "different thread"
        $this->store->sessions['9999999999.valid'] = [
            'startTime'    => 0,
            'expiryTime'   => 9999999999,
            'duration'     => 9999999999,
            'timeout'      => 1234,
            'lastActivity' => $time,
            'renewable'    => true,
            'data' => [
                'id'      => 'someOtherId',
                'someKey' => 'aDifferentValue'
            ]
        ];

        // re-init test after committing
        $session->renewable(false);
        $this->assertWriteMode(true, $session);
        $this->assertTrue(isset($this->store->isLocked['9999999999.valid']));
        $this->assertEquals(1234, $session->timeout());
        $this->assertEquals(false, $session->renewable());
        $this->assertEquals('aDifferentValue', $session->data()->get('someKey'));

        $session->commit();
        $this->assertWriteMode(false, $session);
        $this->assertFalse(isset($this->store->isLocked['9999999999.valid']));

        $data = $this->store->get(9999999999, 'valid');
        $data = Crypto::decrypt($data, $this->store->keyObject, true);
        $data = json_decode($data, true);
        $this->assertEquals([
            'startTime'    => 0,
            'expiryTime'   => 9999999999,
            'duration'     => 9999999999,
            'timeout'      => 1234,
            'lastActivity' => $time,
            'renewable'    => false,
            'data' => [
                'id'      => 'someOtherId',
                'someKey' => 'aDifferentValue'
            ]
        ], $data);
    }

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

        $this->assertEquals(9999999999, $session->expiryTime());
        $this->assertEquals(8999999999, $session->duration());
        $time = time();
        $session->renew();
        $this->assertEquals($time + 8999999999, $session->expiryTime());
        $this->assertEquals(8999999999, $session->duration());
        $this->assertWriteMode(true, $session);
        $this->assertNotEquals($originalToken, $session->token());

        // validate that the old session now references the new one
        $oldTokenParts = explode('.', $originalToken);
        $oldSession = $this->store->get(9999999999, $oldTokenParts[1]);
        $oldSession = Crypto::decrypt($oldSession, Key::loadFromAsciiSafeString($oldTokenParts[2]), true);
        $oldSession = json_decode($oldSession, true);
        $this->assertEquals([
            'startTime'  => 1000000000,
            'expiryTime' => $time + 30,
            'newSession' => $session->token()
        ], $oldSession);
    }

    /**
     * @expectedException     Kirby\Exception\LogicException
     * @expectedExceptionCode error.session.notRenewable
     */
    public function testRenewNotRenewable()
    {
        $token = '3000000000.nonRenewable.' . $this->store->validKey;

        $session = new Session($this->sessions, $token, []);
        $session->renew();
    }

    public function testRegenerateToken()
    {
        $token = '9999999999.valid.' . $this->store->validKey;
        $session = new Session($this->sessions, $token, []);

        $this->assertWriteMode(false, $session);
        $time = time();
        $session->regenerateToken();
        $this->assertNotEquals($token, $newToken = $session->token());
        $this->assertEquals(0, $session->startTime());
        $this->assertEquals(9999999999, $session->expiryTime());
        $this->assertWriteMode(true, $session);

        // validate that all parts of the token have been regenerated
        $newTokenParts = explode('.', $newToken);
        $this->assertEquals(9999999999, $newTokenParts[0]);
        $this->assertNotEquals('valid', $newTokenParts[1]);
        $this->assertStringMatchesFormat('%x', $newTokenParts[1]);
        $this->assertNotEquals($this->store->validKey, $newTokenParts[2]);

        // validate that the old session now references the new one
        $oldSession = $this->store->get(9999999999, 'valid');
        $oldSession = Crypto::decrypt($oldSession, $this->store->keyObject, true);
        $oldSession = json_decode($oldSession, true);
        $this->assertEquals([
            'startTime'  => 0,
            'expiryTime' => $time + 30,
            'newSession' => $newToken
        ], $oldSession);

        // validate that a cookie has been set
        $this->assertEquals($newToken, Cookie::get('kirby_session'));
    }

    public function testRegenerateTokenHeaderMode()
    {
        $token = '9999999999.valid.' . $this->store->validKey;
        $session = new Session($this->sessions, $token, ['mode' => 'header']);

        Cookie::remove('kirby_session');
        $this->assertFalse($session->needsRetransmission());
        $session->regenerateToken();
        $this->assertTrue($session->needsRetransmission());
        $this->assertNull(Cookie::get('kirby_session'));
    }

    public function testPrepareForWriting()
    {
        $token = '9999999999.valid.' . $this->store->validKey;
        $session = new Session($this->sessions, $token, []);

        $this->assertWriteMode(false, $session);
        $this->assertFalse(isset($this->store->isLocked['9999999999.valid']));
        $this->assertEquals(null, $session->data()->get('someId'));

        // manually overwrite some data like another thread would do
        $this->store->sessions['9999999999.valid']['data']['someId'] = 123;
        $this->assertWriteMode(false, $session);
        $this->assertFalse(isset($this->store->isLocked['9999999999.valid']));
        $this->assertEquals(null, $session->data()->get('someId'));

        // now trigger a reload of the session by setting a value
        $session->data()->increment('someId', 1);
        $this->assertWriteMode(true, $session);
        $this->assertTrue(isset($this->store->isLocked['9999999999.valid']));
        $this->assertEquals(124, $session->data()->get('someId'));
    }

    public function testParseToken()
    {
        $reflector = new ReflectionClass(Session::class);
        $parseToken = $reflector->getMethod('parseToken');
        $parseToken->setAccessible(true);
        $keyObjectProperty = $reflector->getProperty('keyObject');
        $keyObjectProperty->setAccessible(true);

        $session = new Session($this->sessions, null, []);
        $this->assertNull($session->token());

        $parseToken->invoke($session, '1234567890.thisIsMyAwesomeId.' . $this->store->validKey);
        $this->assertEquals('1234567890.thisIsMyAwesomeId.' . $this->store->validKey, $session->token());

        $keyObject = $keyObjectProperty->getValue($session);
        $this->assertInstanceOf(Key::class, $keyObject);
        $this->assertEquals($this->store->validKey, $keyObject->saveToAsciiSafeString());
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testParseTokenInvalidToken1()
    {
        $token = '9999999999.thisIsNotAValidToken';
        $session = new Session($this->sessions, $token, []);
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testParseTokenInvalidToken2()
    {
        $token = 'something.thisIsNotAValidToken.abcdefabcdef';
        $session = new Session($this->sessions, $token, []);
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testParseTokenInvalidToken3()
    {
        $token = '9999999999.thisIsNotAValidToken.thisIsDefinitelyNotHexadecimal';
        $session = new Session($this->sessions, $token, []);
    }

    public function testTimeToTimestamp()
    {
        $reflector = new ReflectionClass(Session::class);
        $timeToTimestamp = $reflector->getMethod('timeToTimestamp');
        $timeToTimestamp->setAccessible(true);

        $this->assertEquals(1234567890, $timeToTimestamp->invoke(null, 1234567890));
        $this->assertEquals(1234567890, $timeToTimestamp->invoke(null, 1234567890, 1357924680));
        $this->assertEquals(1514761200, $timeToTimestamp->invoke(null, '2018-01-01', 1357924680));
        $this->assertEquals(strtotime('tomorrow'), $timeToTimestamp->invoke(null, 'tomorrow'));
        $this->assertEquals(strtotime('tomorrow', 1357924680), $timeToTimestamp->invoke(null, 'tomorrow', 1357924680));
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testTimeToTimestampInvalidParam()
    {
        $reflector = new ReflectionClass(Session::class);
        $timeToTimestamp = $reflector->getMethod('timeToTimestamp');
        $timeToTimestamp->setAccessible(true);

        $timeToTimestamp->invoke(null, ['tomorrow']);
    }

    /**
     * @expectedException Kirby\Exception\InvalidArgumentException
     */
    public function testTimeToTimestampInvalidTimeString()
    {
        $reflector = new ReflectionClass(Session::class);
        $timeToTimestamp = $reflector->getMethod('timeToTimestamp');
        $timeToTimestamp->setAccessible(true);

        $timeToTimestamp->invoke(null, 'some gibberish that is definitely no valid time');
    }

    public function testInit()
    {
        $token = '9999999999.valid.' . $this->store->validKey;

        $session = new Session($this->sessions, $token, []);
        $this->assertEquals($token, $session->token());
        $this->assertEquals(0, $session->startTime());
        $this->assertEquals(9999999999, $session->expiryTime());
        $this->assertEquals(9999999999, $session->duration());
        $this->assertFalse($session->timeout());
        $this->assertFalse($session->renewable());
        $this->assertEquals('valid', $session->data()->get('id'));
    }

    /**
     * @expectedException     Kirby\Exception\NotFoundException
     * @expectedExceptionCode error.session.notFound
     */
    public function testInitNonExisting()
    {
        $token = '9999999999.nonExisting.' . $this->store->validKey;
        new Session($this->sessions, $token, []);
    }

    /**
     * @expectedException     Kirby\Exception\LogicException
     * @expectedExceptionCode error.session.invalid
     */
    public function testInitInvalidEncryption()
    {
        $token = '9999999999.valid.' . $this->store->invalidKey;
        new Session($this->sessions, $token, []);
    }

    /**
     * @expectedException     Kirby\Exception\LogicException
     * @expectedExceptionCode error.session.invalid
     */
    public function testInitExpired()
    {
        $token = '1000000000.expired.' . $this->store->validKey;
        new Session($this->sessions, $token, []);
    }

    /**
     * @expectedException     Kirby\Exception\LogicException
     * @expectedExceptionCode error.session.invalid
     */
    public function testInitNotStarted()
    {
        $token = '9999999999.notStarted.' . $this->store->validKey;
        new Session($this->sessions, $token, []);
    }

    public function testInitMoved()
    {
        // moved session: data should be identical to the actual one
        $token    = '9999999999.moved.' . $this->store->validKey;
        $tokenNew = '9999999999.valid.' . $this->store->validKey;

        $session = new Session($this->sessions, $token, []);
        $this->assertEquals($tokenNew, $session->token());
        $this->assertEquals(0, $session->startTime());
        $this->assertEquals(9999999999, $session->expiryTime());
        $this->assertEquals(9999999999, $session->duration());
        $this->assertFalse($session->timeout());
        $this->assertFalse($session->renewable());
        $this->assertEquals('valid', $session->data()->get('id'));
    }

    /**
     * @expectedException     Kirby\Exception\LogicException
     * @expectedExceptionCode error.session.invalid
     */
    public function testInitMovedExpired()
    {
        $token = '1000000000.movedExpired.' . $this->store->validKey;
        new Session($this->sessions, $token, []);
    }

    /**
     * @expectedException     Kirby\Exception\NotFoundException
     * @expectedExceptionCode error.session.notFound
     */
    public function testInitMovedInvalid()
    {
        $token = '9999999999.movedInvalid.' . $this->store->validKey;
        new Session($this->sessions, $token, []);
    }

    /**
     * @expectedException     Kirby\Exception\LogicException
     * @expectedExceptionCode error.session.invalid
     */
    public function testInitExpiredTimeout()
    {
        $token = '9999999999.timeout.' . $this->store->validKey;
        new Session($this->sessions, $token, []);
    }

    public function testInitAutoActivity1()
    {
        $token = '9999999999.timeoutActivity1.' . $this->store->validKey;

        $session = new Session($this->sessions, $token, []);
        $this->assertEquals($token, $session->token());
        $this->assertEquals(0, $session->startTime());
        $this->assertEquals(9999999999, $session->expiryTime());
        $this->assertEquals(9999999999, $session->duration());
        $this->assertEquals(3600, $session->timeout());
        $this->assertFalse($session->renewable());
        $this->assertEquals('timeoutActivity1', $session->data()->get('id'));

        $reflector = new ReflectionClass(Session::class);
        $activityProperty = $reflector->getProperty('lastActivity');
        $activityProperty->setAccessible(true);
        $this->assertEquals($session->data()->get('expectedActivity'), $activityProperty->getValue($session));
    }

    public function testInitAutoActivity2()
    {
        $token = '9999999999.timeoutActivity2.' . $this->store->validKey;

        $session = new Session($this->sessions, $token, []);
        $this->assertEquals($token, $session->token());
        $this->assertEquals(0, $session->startTime());
        $this->assertEquals(9999999999, $session->expiryTime());
        $this->assertEquals(9999999999, $session->duration());
        $this->assertEquals(3600, $session->timeout());
        $this->assertFalse($session->renewable());
        $this->assertEquals('timeoutActivity2', $session->data()->get('id'));

        $reflector = new ReflectionClass(Session::class);
        $activityProperty = $reflector->getProperty('lastActivity');
        $activityProperty->setAccessible(true);
        $newActivity = $activityProperty->getValue($session);
        $this->assertGreaterThan($session->data()->get('expectedActivity') - 5, $newActivity);
        $this->assertLessThan($session->data()->get('expectedActivity') + 5, $newActivity);
    }

    public function testInitAutoRenew()
    {
        $token = '.renewal.' . $this->store->validKey;
        $time = time();

        $session = new Session($this->sessions, '3000000000' . $token, []);
        $newToken = $session->token();
        $newTokenExpiry = (int)Str::before($newToken, '.');

        $this->assertGreaterThan($time + 2999999995, $newTokenExpiry);
        $this->assertLessThan($time + 3000000005, $newTokenExpiry);
        $this->assertEquals(0, $session->startTime());
        $this->assertGreaterThan($time + 2999999995, $session->expiryTime());
        $this->assertLessThan($time + 3000000005, $session->expiryTime());
        $this->assertEquals(3000000000, $session->duration());
        $this->assertFalse($session->timeout());
        $this->assertTrue($session->renewable());
        $this->assertEquals('renewal', $session->data()->get('id'));
    }

    public function testInitNonRenewable()
    {
        $token = '3000000000.nonRenewable.' . $this->store->validKey;

        $session = new Session($this->sessions, $token, []);
        $this->assertEquals($token, $session->token());
        $this->assertEquals(0, $session->startTime());
        $this->assertEquals(3000000000, $session->expiryTime());
        $this->assertEquals(3000000000, $session->duration());
        $this->assertFalse($session->timeout());
        $this->assertFalse($session->renewable());
        $this->assertEquals('nonRenewable', $session->data()->get('id'));
    }

    /**
     * Asserts the state of the write mode of the given session
     *
     * @param  boolean $expected Whether the write mode should be true or false right now
     * @param  Session $session
     * @return void
     */
    protected function assertWriteMode(bool $expected, Session $session)
    {
        $reflector = new ReflectionClass(Session::class);
        $writeModeProperty = $reflector->getProperty('writeMode');
        $writeModeProperty->setAccessible(true);

        $this->assertEquals($expected, $writeModeProperty->getValue($session));
    }
}
