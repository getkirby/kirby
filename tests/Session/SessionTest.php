<?php

namespace Kirby\Session;

use Kirby\Exception\BadMethodCallException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Cookie;
use Kirby\TestCase;
use Kirby\Toolkit\Obj;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\SymmetricCrypto;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;
use TypeError;

#[CoversClass(Session::class)]
class SessionTest extends TestCase
{
	protected SessionStore $store;
	protected Sessions $sessions;

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

	public function testConstructInvalidToken(): void
	{
		$this->expectException(InvalidArgumentException::class);

		new Session($this->sessions, 123, []);
	}

	public function testCreate(): void
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
		$this->assertTrue($session->renewable());
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
		$this->assertNull($activityProperty->getValue($session));
		$this->assertFalse($session->renewable());
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

	public function testCreateInvalidExpiry(): void
	{
		$this->expectException(InvalidArgumentException::class);

		new Session($this->sessions, null, [
			'startTime'  => time() - 3600,
			'expiryTime' => time() - 1800
		]);
	}

	public function testCreateInvalidDuration(): void
	{
		$this->expectException(InvalidArgumentException::class);

		new Session($this->sessions, null, [
			'startTime'  => time() + 60,
			'expiryTime' => time()
		]);
	}

	public function testCreateInvalidTimeout(): void
	{
		$this->expectException(TypeError::class);

		new Session($this->sessions, null, [
			'timeout' => 'at some point'
		]);
	}

	public function testCreateInvalidRenewable(): void
	{
		$this->expectException(TypeError::class);

		new Session($this->sessions, null, [
			'renewable' => ['maybe']
		]);
	}

	public function testMode(): void
	{
		$session = new Session($this->sessions, null, [
			'mode' => 'manual'
		]);

		$this->assertSame('manual', $session->mode());
		$this->assertSame('cookie', $session->mode('cookie'));
		$this->assertSame('cookie', $session->mode());
	}

	public function testModeStartedSession(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$session = new Session($this->sessions, null, [
			'mode' => 'manual'
		]);
		$session->data()->set('someKey', 'someValue');

		$this->assertSame('manual', $session->mode());
		$session->mode('cookie');
	}

	public function testExpiryTime(): void
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

	public function testExpiryTimeInvalidType(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$session = new Session($this->sessions, null, [
			'startTime'  => 1000000000,
			'expiryTime' => 7777777777
		]);
		$session->expiryTime(false);
	}

	public function testExpiryTimeInvalidString(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$session = new Session($this->sessions, null, [
			'startTime'  => 1000000000,
			'expiryTime' => 7777777777
		]);
		$session->expiryTime('some gibberish that is definitely no valid time');
	}

	public function testExpiryTimeInvalidTime1(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$session = new Session($this->sessions, null, [
			'startTime'  => 1000000000,
			'expiryTime' => 7777777777
		]);
		$session->expiryTime(time() - 1);
	}

	public function testExpiryTimeInvalidTime2(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$session = new Session($this->sessions, null, [
			'startTime'  => 1000000000,
			'expiryTime' => 7777777777
		]);
		$session->expiryTime(time());
	}

	public function testDuration(): void
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

	public function testDurationInvalidTime1(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$session = new Session($this->sessions, null, [
			'startTime'  => 1000000000,
			'expiryTime' => 7777777777
		]);
		$session->duration(-1);
	}

	public function testDurationInvalidTime2(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$session = new Session($this->sessions, null, [
			'startTime'  => 1000000000,
			'expiryTime' => 7777777777
		]);
		$session->duration(0);
	}

	public function testTimeout(): void
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
		$this->assertNull($activityProperty->getValue($session));
		$this->assertWriteMode(true, $session);
	}

	public function testTimeoutInvalidType(): void
	{
		$this->expectException(TypeError::class);

		$session = new Session($this->sessions, null, [
			'timeout' => 1234
		]);
		$session->timeout('after an hour');
	}

	public function testTimeoutInvalidTimeout1(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$session = new Session($this->sessions, null, [
			'timeout' => 1234
		]);
		$session->timeout(-10);
	}

	public function testTimeoutInvalidTimeout2(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$session = new Session($this->sessions, null, [
			'timeout' => 1234
		]);
		$session->timeout(0);
	}

	public function testRenewable(): void
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

	public function testDataMethods(): void
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

	public function testInvalidMethod(): void
	{
		$this->expectException(BadMethodCallException::class);

		$session = new Session($this->sessions, null, []);
		$session->someGibberish();
	}

	public function testCommit(): void
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
		$this->assertFalse($session->renewable());
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

	public function testDestroy(): void
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

	public function testRenew(): void
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
		$expected = [
			'startTime'  => 1000000000,
			'expiryTime' => 1337000000 + 30, // timestamp is from mock
			'newSession' => $newTokenParts[0] . '.' . $newTokenParts[1]
		];
		$actual = $this->store->sessions['9999999999.' . $oldTokenParts[1]];
		if (SymmetricCrypto::isAvailable() === true) {
			$crypto = new SymmetricCrypto(secretKey: hex2bin($oldTokenParts[2]));
			$this->assertSame($newTokenParts[2], $crypto->decrypt($actual['newSessionKey']));

			// the actual value contains random parts, accept it in the check below
			$expected['newSessionKey'] = $actual['newSessionKey'];
		}
		$this->assertSame($expected, $actual);
	}

	public function testRenewNotRenewable(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionCode('error.session.notRenewable');

		$token = '2000000000.nonRenewable.' . $this->store->validKey;

		$session = new Session($this->sessions, $token, []);
		$session->renew();
	}

	public function testRegenerateToken(): void
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
		$expected = [
			'startTime'  => 0,
			'expiryTime' => 1337000000 + 30, // timestamp is from mock
			'newSession' => $newTokenParts[0] . '.' . $newTokenParts[1]
		];
		$actual = $this->store->sessions['9999999999.valid'];
		if (SymmetricCrypto::isAvailable() === true) {
			$crypto = new SymmetricCrypto(secretKey: hex2bin($this->store->validKey));
			$this->assertSame($newTokenParts[2], $crypto->decrypt($actual['newSessionKey']));

			// the actual value contains random parts, accept it in the check below
			$expected['newSessionKey'] = $actual['newSessionKey'];
		}
		$this->assertSame($expected, $actual);

		// validate that a cookie has been set
		$this->assertSame($newToken, Cookie::get('kirby_session'));

		// validate that the new session is cached in the $sessions object
		$this->assertArrayHasKey($newToken, $cache->getValue($this->sessions));
		$this->assertSame($session, $cache->getValue($this->sessions)[$newToken]);
	}

	public function testRegenerateTokenHeaderMode(): void
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

	public function testDestruct(): void
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

	public function testPrepareForWriting(): void
	{
		$token = '9999999999.valid.' . $this->store->validKey;
		$session = new Session($this->sessions, $token, []);

		$this->assertWriteMode(false, $session);
		$this->assertFalse(isset($this->store->isLocked['9999999999.valid']));
		$this->assertNull($session->data()->get('someId'));

		// manually overwrite some data like another thread would do
		$this->store->sessions['9999999999.valid']['data']['someId'] = 123;
		$this->assertWriteMode(false, $session);
		$this->assertFalse(isset($this->store->isLocked['9999999999.valid']));
		$this->assertNull($session->data()->get('someId'));

		// now trigger a reload of the session by setting a value
		$session->data()->increment('someId', 1);
		$this->assertWriteMode(true, $session);
		$this->assertTrue(isset($this->store->isLocked['9999999999.valid']));
		$this->assertSame(124, $session->data()->get('someId'));
	}

	public function testPrepareForWritingReadonly(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionCode('error.session.readonly');

		$token = '9999999999.moved.' . $this->store->validKey;
		$session = new Session($this->sessions, $token, []);

		$session->data()->set('someId', 1);
	}

	public function testParseToken(): void
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

	public function testParseTokenInvalidToken1(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$token = '9999999999.thisIsNotAValidToken';
		$session = new Session($this->sessions, $token, []);
	}

	public function testParseTokenInvalidToken2(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$token = 'something.thisIsNotAValidToken.abcdefabcdef';
		$session = new Session($this->sessions, $token, []);
	}

	public function testParseTokenInvalidToken3(): void
	{
		$this->expectException(InvalidArgumentException::class);

		$reflector = new ReflectionClass(Session::class);
		$parseToken = $reflector->getMethod('parseToken');
		$parseToken->setAccessible(true);

		$session = new Session($this->sessions, null, []);
		$this->assertNull($session->token());

		$parseToken->invoke($session, '1234567890.thisIsMyAwesomeId.' . $this->store->validKey, true);
	}

	public function testTimeToTimestamp(): void
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

	public function testTimeToTimestampInvalidParam(): void
	{
		$this->expectException(TypeError::class);

		$reflector = new ReflectionClass(Session::class);
		$timeToTimestamp = $reflector->getMethod('timeToTimestamp');
		$timeToTimestamp->setAccessible(true);

		$timeToTimestamp->invoke(null, ['tomorrow']);
	}

	public function testTimeToTimestampInvalidTimeString(): void
	{
		$this->expectException(TypeError::class);

		$reflector = new ReflectionClass(Session::class);
		$timeToTimestamp = $reflector->getMethod('timeToTimestamp');
		$timeToTimestamp->setAccessible(true);

		$timeToTimestamp->invoke(null, ['some gibberish that is definitely no valid time']);
	}

	public function testInit(): void
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

	public function testInitSerializedObject(): void
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
		$this->assertEquals($obj, $session->data()->get('obj')); // cannot use strict assertion (serialized data)
		$this->assertFalse($obj === $session->data()->get('obj'));
	}

	public function testInitNonExisting(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionCode('error.session.notFound');

		$token = '9999999999.nonExisting.' . $this->store->validKey;
		new Session($this->sessions, $token, []);
	}

	public function testInitWrongKey(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionCode('error.session.invalid');

		$token = '9999999999.valid.' . $this->store->invalidKey;
		new Session($this->sessions, $token, []);
	}

	public function testInitMissingKey(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid argument "$token" in method "Session::parseToken"');

		$token = '9999999999.valid';
		new Session($this->sessions, $token, []);
	}

	public function testInitInvalidSerialization(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionCode('error.session.invalid');

		$token = '9999999999.invalidSerialization.' . $this->store->validKey;
		new Session($this->sessions, $token, []);
	}

	public function testInitInvalidStructure(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionCode('error.session.invalid');

		$token = '9999999999.invalidStructure.' . $this->store->validKey;
		new Session($this->sessions, $token, []);
	}

	public function testInitExpired(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionCode('error.session.invalid');

		$token = '1000000000.expired.' . $this->store->validKey;
		new Session($this->sessions, $token, []);
	}

	public function testInitNotStarted(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionCode('error.session.invalid');

		$token = '9999999999.notStarted.' . $this->store->validKey;
		new Session($this->sessions, $token, []);
	}

	public function testInitMoved(): void
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

	public function testInitMovedRenewal(): void
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

	public function testInitMovedManualRenewal(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionCode('error.session.readonly');

		$token = '9999999999.movedRenewal.' . $this->store->validKey;

		$session = new Session($this->sessions, $token, []);
		$session->renew();
	}

	public function testInitMovedRenewalWithKey(): void
	{
		if (SymmetricCrypto::isAvailable() !== true) {
			$this->markTestSkipped('PHP sodium extension is not available');
			return;
		}

		// moved session: data should be identical to the actual one
		$token = '9999999999.movedRenewalWithKey.' . $this->store->validKey;

		$session = new Session($this->sessions, $token, []);
		$this->assertSame(0, $session->startTime());
		$this->assertSame(2000000000, $session->duration());
		$this->assertFalse($session->timeout());
		$this->assertTrue($session->renewable());
		$this->assertSame('renewal', $session->data()->get('id'));

		// new session should be renewed because the new session has its key
		$this->assertWriteMode(true, $session);
	}

	public function testInitTimeoutActivity(): void
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

	public function testInitTimeoutActivityWithKey(): void
	{
		if (SymmetricCrypto::isAvailable() !== true) {
			$this->markTestSkipped('PHP sodium extension is not available');
			return;
		}

		// moved session: data should be identical to the actual one
		$token = '9999999999.movedTimeoutActivityWithKey.' . $this->store->validKey;

		$session = new Session($this->sessions, $token, []);
		$this->assertSame(0, $session->startTime());
		$this->assertSame(9999999999, $session->duration());
		$this->assertSame(3600, $session->timeout());
		$this->assertFalse($session->renewable());
		$this->assertSame('timeoutActivity2', $session->data()->get('id'));

		// new session should be refreshed because the new session has its key
		$this->assertWriteMode(true, $session);
	}

	public function testInitMovedExpired(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionCode('error.session.invalid');

		$token = '1000000000.movedExpired.' . $this->store->validKey;
		new Session($this->sessions, $token, []);
	}

	public function testInitMovedInvalid(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionCode('error.session.notFound');

		$token = '9999999999.movedInvalid.' . $this->store->validKey;
		new Session($this->sessions, $token, []);
	}

	public function testInitMovedWrongKey(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionCode('error.session.invalid');

		$token = '9999999999.moved.' . $this->store->invalidKey;
		new Session($this->sessions, $token, []);
	}

	public function testInitExpiredTimeout(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionCode('error.session.invalid');

		$token = '9999999999.timeout.' . $this->store->validKey;
		new Session($this->sessions, $token, []);
	}

	public function testInitAutoActivity1(): void
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

	public function testInitAutoActivity2(): void
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

	public function testInitAutoRenew(): void
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

	public function testInitNonRenewable(): void
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
	 */
	protected function assertWriteMode(bool $expected, Session $session): void
	{
		$reflector = new ReflectionClass(Session::class);
		$writeModeProperty = $reflector->getProperty('writeMode');
		$writeModeProperty->setAccessible(true);

		$this->assertSame($expected, $writeModeProperty->getValue($session));
	}
}
