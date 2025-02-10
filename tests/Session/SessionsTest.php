<?php

namespace Kirby\Session;

use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Cookie;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;
use TypeError;

#[CoversClass(Sessions::class)]
class SessionsTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/store';

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
		App::destroy();
	}

	public function testConstructorStores()
	{
		// mock store
		$this->assertSame($this->store, $this->sessions->store());

		// custom store
		$store    = new FileSessionStore(static::FIXTURES);
		$sessions = new Sessions($store);
		$this->assertSame($store, $sessions->store());

		// custom path
		$path     = static::FIXTURES;
		$sessions = new Sessions($path);

		$reflector = new ReflectionClass(FileSessionStore::class);
		$pathProperty = $reflector->getProperty('path');
		$pathProperty->setAccessible(true);
		$this->assertSame($path, $pathProperty->getValue($sessions->store()));
	}

	public function testConstructorInvalidStore()
	{
		$this->expectException(TypeError::class);
		new Sessions(new InvalidSessionStore());
	}

	public function testConstructorOptions()
	{
		$sessions = new Sessions(static::FIXTURES, [
			'mode'       => 'header',
			'cookieName' => 'my_cookie_name'
		]);

		$this->assertSame('my_cookie_name', $sessions->cookieName());

		$reflector = new ReflectionClass(Sessions::class);
		$modeProperty = $reflector->getProperty('mode');
		$modeProperty->setAccessible(true);
		$this->assertSame('header', $modeProperty->getValue($sessions));
	}

	public function testConstructorInvalidMode()
	{
		$this->expectException(InvalidArgumentException::class);

		new Sessions(static::FIXTURES, ['mode' => 'invalid']);
	}

	public function testConstructorInvalidCookieName()
	{
		$this->expectException(TypeError::class);

		new Sessions(static::FIXTURES, ['cookieName' => ['foo']]);
	}

	public function testConstructorGarbageCollector()
	{
		// collect garbage every time
		$this->store->collectedGarbage = false;
		$sessions = new Sessions($this->store, ['gcInterval' => 1]);
		$this->assertTrue($this->store->collectedGarbage);

		// never collect garbage
		$this->store->collectedGarbage = false;
		$sessions = new Sessions($this->store, ['gcInterval' => false]);
		$this->assertFalse($this->store->collectedGarbage);
	}

	public function testConstructorInvalidGcInterval()
	{
		$this->expectException(InvalidArgumentException::class);

		new Sessions(static::FIXTURES, ['gcInterval' => 0]);
	}

	public function testCreate()
	{
		$sessions = new Sessions($this->store, ['mode' => 'header']);
		$session = $sessions->create();
		$this->assertSame('header', $session->mode());
		$this->assertNull($session->token());
		$this->assertSame(1337000000, $session->startTime()); // timestamp is from mock
		$this->assertSame(7200, $session->duration());
		$this->assertSame(1337000000 + 7200, $session->expiryTime()); // timestamp is from mock
		$this->assertSame(1800, $session->timeout());
		$this->assertTrue($session->renewable());

		$session = $sessions->create([
			'mode'       => 'manual',
			'startTime'  => '+ 1 hour',
			'expiryTime' => '+ 10 hours',
			'timeout'    => false,
			'renewable'  => false
		]);
		$this->assertSame('manual', $session->mode());
		$this->assertNull($session->token());
		$this->assertSame(1337000000 + 3600, $session->startTime()); // timestamp is from mock
		$this->assertSame(36000, $session->duration());
		$this->assertSame(1337000000 + 39600, $session->expiryTime()); // timestamp is from mock
		$this->assertFalse($session->timeout());
		$this->assertFalse($session->renewable());
	}

	public function testGet()
	{
		$sessions = new Sessions($this->store, ['mode' => 'header']);
		$session = $sessions->get('9999999999.valid.' . $this->store->validKey);
		$this->assertSame('header', $session->mode());
		$this->assertSame('9999999999.valid.' . $this->store->validKey, $session->token());

		$session1 = $sessions->get('9999999999.valid2.' . $this->store->validKey, 'manual');
		$this->assertSame('manual', $session1->mode());
		$this->assertSame('9999999999.valid2.' . $this->store->validKey, $session1->token());

		$session2 = $sessions->get('9999999999.valid2.' . $this->store->validKey, 'header');
		$this->assertSame($session1, $session2);
		$session1->data()->set('someKey', 'someValue');
		$this->assertSame('someValue', $session2->data()->get('someKey'));
	}

	public function testGetInvalid()
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionCode('error.session.notFound');

		$this->sessions->get('9999999999.doesNotExist.' . $this->store->validKey);
	}

	public function testCurrent()
	{
		Cookie::set('kirby_session', '9999999999.valid.' . $this->store->validKey);
		$this->setAuthorization('Session 9999999999.valid2.' . $this->store->validKey);

		$sessions = new Sessions($this->store, ['mode' => 'cookie']);
		$session = $sessions->current();
		$this->assertSame('cookie', $session->mode());
		$this->assertSame('9999999999.valid.' . $this->store->validKey, $session->token());

		$sessions = new Sessions($this->store, ['mode' => 'header']);
		$session = $sessions->current();
		$this->assertSame('header', $session->mode());
		$this->assertSame('9999999999.valid2.' . $this->store->validKey, $session->token());

		$this->setAuthorization('');
		$this->assertNull($sessions->current());

		$this->setAuthorization('Session 9999999999.valid2.' . $this->store->invalidKey);
		$this->assertNull($sessions->current());

		// test self-check: should work again
		$this->setAuthorization('Session 9999999999.valid2.' . $this->store->validKey);
		$session = $sessions->current();
		$this->assertSame('header', $session->mode());
		$this->assertSame('9999999999.valid2.' . $this->store->validKey, $session->token());
	}

	public function testCurrentManualMode()
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionCode('error.session.sessions.manualMode');

		$sessions = new Sessions($this->store, ['mode' => 'manual']);
		$sessions->current();
	}

	public function testCurrentDetected()
	{
		Cookie::set('kirby_session', '9999999999.valid.' . $this->store->validKey);
		$this->setAuthorization('Session 9999999999.valid2.' . $this->store->validKey);

		$session = $this->sessions->currentDetected();
		$this->assertSame('header', $session->mode());
		$this->assertSame('9999999999.valid2.' . $this->store->validKey, $session->token());

		$this->setAuthorization('');
		$session = $this->sessions->currentDetected();
		$this->assertSame('cookie', $session->mode());
		$this->assertSame('9999999999.valid.' . $this->store->validKey, $session->token());

		Cookie::remove('kirby_session');
		$this->assertNull($this->sessions->currentDetected());

		Cookie::set('kirby_session', '9999999999.valid.' . $this->store->validKey);
		$this->setAuthorization('Session 9999999999.valid2.' . $this->store->invalidKey);
		$this->assertNull($this->sessions->currentDetected());

		// test self-check: should work again
		$this->setAuthorization('Session 9999999999.valid2.' . $this->store->validKey);
		$session = $this->sessions->currentDetected();
		$this->assertSame('header', $session->mode());
		$this->assertSame('9999999999.valid2.' . $this->store->validKey, $session->token());
	}

	public function testCollectGarbage()
	{
		$this->store->collectedGarbage = false;
		$this->sessions->collectGarbage();
		$this->assertTrue($this->store->collectedGarbage);
	}

	public function testUpdateCache()
	{
		$sessionsReflector = new ReflectionClass(Sessions::class);
		$cache = $sessionsReflector->getProperty('cache');
		$cache->setAccessible(true);

		$sessionReflector = new ReflectionClass(Session::class);
		$tokenKey = $sessionReflector->getProperty('tokenKey');
		$tokenKey->setAccessible(true);

		$sessions = new Sessions($this->store, ['mode' => 'header']);
		$session = $sessions->get('9999999999.valid.' . $this->store->validKey);
		$tokenKey->setValue($session, 'new-key');

		$this->assertArrayNotHasKey('9999999999.valid.new-key', $cache->getValue($sessions));
		$sessions->updateCache($session);
		$this->assertArrayHasKey('9999999999.valid.new-key', $cache->getValue($sessions));
		$this->assertSame($session, $cache->getValue($sessions)['9999999999.valid.new-key']);
	}

	public function testTokenFromCookie()
	{
		$reflector = new ReflectionClass(Sessions::class);
		$tokenFromCookie = $reflector->getMethod('tokenFromCookie');
		$tokenFromCookie->setAccessible(true);

		Cookie::remove('kirby_session');
		$this->assertNull($tokenFromCookie->invoke($this->sessions));

		Cookie::set('kirby_session', 'amazingSessionIdFromCookie');
		$this->assertSame('amazingSessionIdFromCookie', $tokenFromCookie->invoke($this->sessions));

		Cookie::remove('kirby_session');
	}

	public function testTokenFromHeader()
	{
		$reflector = new ReflectionClass(Sessions::class);
		$tokenFromHeader = $reflector->getMethod('tokenFromHeader');
		$tokenFromHeader->setAccessible(true);

		$this->assertNull($tokenFromHeader->invoke($this->sessions));

		$this->setAuthorization('Session amazingSessionIdFromHeader');
		$this->assertSame('amazingSessionIdFromHeader', $tokenFromHeader->invoke($this->sessions));

		$this->setAuthorization('Bearer amazingSessionIdFromHeader');
		$this->assertNull($tokenFromHeader->invoke($this->sessions));
	}

	protected function setAuthorization(string $value): void
	{
		new App([
			'server' => [
				'HTTP_AUTHORIZATION' => $value
			]
		]);
	}
}
