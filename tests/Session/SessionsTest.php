<?php

namespace Kirby\Session;

use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Cookie;
use Kirby\TestCase;
use Kirby\Tests\MockTime;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;
use TypeError;

#[CoversClass(Sessions::class)]
class SessionsTest extends TestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures/store';

	protected Store $store;
	protected Sessions $sessions;

	protected function setUp(): void
	{
		$this->store    = new TestStore();
		$this->sessions = Sessions::factory($this->store);

		MockTime::$time = 1337000000;
	}

	protected function tearDown(): void
	{
		unset($this->sessions, $this->store);
		App::destroy();
	}

	public function testConstructorStores(): void
	{
		// mock store
		$this->assertSame($this->store, $this->sessions->store());

		// custom store
		$store    = new FileStore(static::FIXTURES);
		$sessions = Sessions::factory($store);
		$this->assertSame($store, $sessions->store());

		// custom path
		$path     = static::FIXTURES;
		$sessions = Sessions::factory($path);

		$reflector = new ReflectionClass(FileStore::class);
		$pathProperty = $reflector->getProperty('path');
		$this->assertSame($path, $pathProperty->getValue($sessions->store()));
	}

	public function testConstructorInvalidStore(): void
	{
		$this->expectException(TypeError::class);
		Sessions::factory(new InvalidStore());
	}

	public function testConstructorOptions(): void
	{
		$sessions = Sessions::factory(static::FIXTURES, [
			'mode'         => 'header',
			'cookieDomain' => 'getkirby.com',
			'cookieName'   => 'my_cookie_name'
		]);

		$this->assertSame('getkirby.com', $sessions->cookieDomain());
		$this->assertSame('my_cookie_name', $sessions->cookieName());

		$reflector = new ReflectionClass(Sessions::class);
		$modeProperty = $reflector->getProperty('mode');
		$this->assertSame('header', $modeProperty->getValue($sessions));
	}

	public function testConstructorInvalidMode(): void
	{
		$this->expectException(InvalidArgumentException::class);

		Sessions::factory(static::FIXTURES, ['mode' => 'invalid']);
	}

	public function testConstructorInvalidCookieDomain(): void
	{
		$this->expectException(TypeError::class);

		Sessions::factory(static::FIXTURES, ['cookieDomain' => ['foo']]);
	}

	public function testConstructorInvalidCookieName(): void
	{
		$this->expectException(TypeError::class);

		Sessions::factory(static::FIXTURES, ['cookieName' => ['foo']]);
	}

	public function testConstructorGarbageCollector(): void
	{
		// collect garbage every time
		$this->store->collectedGarbage = false;
		$sessions = Sessions::factory($this->store, ['gcInterval' => 1]);
		$this->assertTrue($this->store->collectedGarbage);

		// never collect garbage
		$this->store->collectedGarbage = false;
		$sessions = Sessions::factory($this->store, ['gcInterval' => false]);
		$this->assertFalse($this->store->collectedGarbage);
	}

	public function testConstructorInvalidGcInterval(): void
	{
		$this->expectException(InvalidArgumentException::class);

		Sessions::factory(static::FIXTURES, ['gcInterval' => 0]);
	}

	public function testCreate(): void
	{
		$sessions = Sessions::factory($this->store, ['mode' => 'header']);
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

	public function testFind(): void
	{
		$sessions = Sessions::factory($this->store, ['mode' => 'header']);
		$session = $sessions->find('9999999999.valid.' . $this->store->validKey);
		$this->assertSame('header', $session->mode());
		$this->assertSame('9999999999.valid.' . $this->store->validKey, $session->token());

		$session1 = $sessions->find('9999999999.valid2.' . $this->store->validKey, 'manual');
		$this->assertSame('manual', $session1->mode());
		$this->assertSame('9999999999.valid2.' . $this->store->validKey, $session1->token());

		$session2 = $sessions->find('9999999999.valid2.' . $this->store->validKey, 'header');
		$this->assertSame($session1, $session2);
		$session1->data()->set('someKey', 'someValue');
		$this->assertSame('someValue', $session2->data()->get('someKey'));
	}

	public function testFindInvalid(): void
	{
		$this->expectException(NotFoundException::class);
		$this->expectExceptionCode('error.session.notFound');

		$this->sessions->find('9999999999.doesNotExist.' . $this->store->validKey);
	}

	public function testGet(): void
	{
		Cookie::set('kirby_session', '9999999999.valid.' . $this->store->validKey);
		$this->setAuthorization('Session 9999999999.valid2.' . $this->store->validKey);
		$sessions = Sessions::factory($this->store);

		// default: no detection
		$session = $sessions->get();
		$this->assertSame('9999999999.valid.' . $this->store->validKey, $session->token());

		// use detection
		$session = $sessions->get(['detect' => true]);
		$this->assertSame('9999999999.valid2.' . $this->store->validKey, $session->token());

		// newly created session
		Cookie::remove('kirby_session');
		$this->setAuthorization('');
		$session = $sessions->get();
		$this->assertNull($session->token());
		$this->assertSame('cookie', $session->mode());
		$this->assertSame(1337000000, $session->startTime()); // timestamp is from mock
		$this->assertSame(7200, $session->duration());
		$this->assertSame(1337000000 + 7200, $session->expiryTime()); // timestamp is from mock
		$this->assertSame(1800, $session->timeout());
		$this->assertTrue($session->renewable());

		// session needs to be the same one each time
		$this->assertTrue($session === $sessions->get());

		// custom create mode
		$sessions = Sessions::factory($this->store);
		$session = $sessions->get(['createMode' => 'manual']);
		$this->assertNull($session->token());
		$this->assertSame('manual', $session->mode());

		// getting a session with the default createMode shouldn't change the mode
		$session = $sessions->get();
		$this->assertNull($session->token());
		$this->assertSame('manual', $session->mode());

		// but in the other direction it should
		$sessions = Sessions::factory($this->store);
		$session = $sessions->get();
		$this->assertNull($session->token());
		$this->assertSame('cookie', $session->mode());
		$session = $sessions->get(['createMode' => 'manual']);
		$this->assertNull($session->token());
		$this->assertSame('manual', $session->mode());

		// but not if the session has already been initialized
		$sessions = Sessions::factory($this->store);
		$session = $sessions->get();
		$this->assertNull($session->token());
		$this->assertSame('cookie', $session->mode());
		$session->data()->set('someKey', 'someValue');
		$this->assertNotNull($session->token());
		$session = $sessions->get(['createMode' => 'manual']);
		$this->assertNotNull($session->token());
		$this->assertSame('cookie', $session->mode());

		// long session defaults
		$sessions = Sessions::factory($this->store);
		$session = $sessions->get(['long' => true]);
		$this->assertNull($session->token());
		$this->assertSame('cookie', $session->mode());
		$this->assertSame(1337000000, $session->startTime()); // timestamp is from mock
		$this->assertSame(1209600, $session->duration());
		$this->assertSame(1337000000 + 1209600, $session->expiryTime()); // timestamp is from mock
		$this->assertFalse($session->timeout());
		$this->assertTrue($session->renewable());

		// session config update when switching to long session
		$sessions = Sessions::factory($this->store);
		$session = $sessions->get();
		$this->assertSame(7200, $session->duration());
		$this->assertSame(1800, $session->timeout());
		$session->data()->set('id', 'awesome session');
		$session->commit();
		Cookie::set('kirby_session', $session->token());
		$session = $sessions->get(['long' => true]);
		$this->assertSame('awesome session', $session->data()->get('id'));
		$this->assertSame(1209600, $session->duration());
		$this->assertFalse($session->timeout());
		Cookie::remove('kirby_session');

		// custom duration and timeout (normal session)
		$sessions = Sessions::factory($this->store, [
			'durationNormal' => 1,
			'durationLong'   => 5,
			'timeout'        => 1234
		]);
		$session = $sessions->get();
		$this->assertNull($session->token());
		$this->assertSame('cookie', $session->mode());
		$this->assertSame(1337000000, $session->startTime()); // timestamp is from mock
		$this->assertSame(1, $session->duration());
		$this->assertSame(1337000000 + 1, $session->expiryTime()); // timestamp is from mock
		$this->assertSame(1234, $session->timeout());
		$this->assertTrue($session->renewable());

		// custom duration and timeout (long session)
		$session = $sessions->get(['long' => true]);
		$this->assertNull($session->token());
		$this->assertSame('cookie', $session->mode());
		$this->assertSame(1337000000, $session->startTime()); // timestamp is from mock
		$this->assertSame(5, $session->duration());
		$this->assertSame(1337000000 + 5, $session->expiryTime()); // timestamp is from mock
		$this->assertFalse($session->timeout());
		$this->assertTrue($session->renewable());

		// session config update when the configuration changed
		$sessions = Sessions::factory($this->store);
		$session = $sessions->get();
		$this->assertSame(7200, $session->duration());
		$this->assertSame(1800, $session->timeout());
		$session->data()->set('id', 'awesome session');
		$session->commit();
		Cookie::set('kirby_session', $session->token());

		// lower values: shouldn't change anything
		$sessions = Sessions::factory($this->store, ['durationNormal' => 7100, 'timeout' => 1000]);
		$session = $sessions->get();
		$this->assertSame('awesome session', $session->data()->get('id'));
		$this->assertSame(7200, $session->duration());
		$this->assertSame(1800, $session->timeout());
		$session->commit();

		// higher values: should update
		$sessions = Sessions::factory($this->store, ['durationNormal' => 7300, 'timeout' => 1900]);
		$session = $sessions->get();
		$this->assertSame('awesome session', $session->data()->get('id'));
		$this->assertSame(7300, $session->duration());
		$this->assertSame(1900, $session->timeout());
		$session->commit();

		// remove timeout: should update
		$sessions = Sessions::factory($this->store, ['timeout' => false]);
		$session = $sessions->get();
		$this->assertSame('awesome session', $session->data()->get('id'));
		$this->assertSame(7300, $session->duration());
		$this->assertFalse($session->timeout());
		Cookie::remove('kirby_session');

		// timeout for the first time: shouldn't change anything
		$sessions = Sessions::factory($this->store);
		$session = $sessions->get(['long' => true]);
		$this->assertSame(1209600, $session->duration());
		$this->assertFalse($session->timeout());
		$session->data()->set('id', 'awesome session');
		$session->commit();
		Cookie::set('kirby_session', $session->token());
		$session = $sessions->get();
		$this->assertSame('awesome session', $session->data()->get('id'));
		$this->assertSame(1209600, $session->duration());
		$this->assertFalse($session->timeout());
		$session->commit();
	}

	public function testCurrent(): void
	{
		Cookie::set('kirby_session', '9999999999.valid.' . $this->store->validKey);
		$this->setAuthorization('Session 9999999999.valid2.' . $this->store->validKey);

		$sessions = Sessions::factory($this->store, ['mode' => 'cookie']);
		$session = $sessions->current();
		$this->assertSame('cookie', $session->mode());
		$this->assertSame('9999999999.valid.' . $this->store->validKey, $session->token());

		$sessions = Sessions::factory($this->store, ['mode' => 'header']);
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

	public function testCurrentManualMode(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionCode('error.session.sessions.manualMode');

		$sessions = Sessions::factory($this->store, ['mode' => 'manual']);
		$sessions->current();
	}

	public function testCurrentDetected(): void
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

	public function testCollectGarbage(): void
	{
		$this->store->collectedGarbage = false;
		$this->sessions->collectGarbage();
		$this->assertTrue($this->store->collectedGarbage);
	}

	public function testUpdate(): void
	{
		$sessionsReflector = new ReflectionClass(Sessions::class);
		$cache = $sessionsReflector->getProperty('cache');

		$sessionReflector = new ReflectionClass(Session::class);
		$token = $sessionReflector->getProperty('token');

		$sessions = Sessions::factory($this->store, ['mode' => 'header']);
		$session = $sessions->find('9999999999.valid.' . $this->store->validKey);
		$token->setValue($session, new Token(9999999999, 'valid', 'new-key'));

		$this->assertArrayNotHasKey('9999999999.valid.new-key', $cache->getValue($sessions));
		$sessions->update($session);
		$this->assertArrayHasKey('9999999999.valid.new-key', $cache->getValue($sessions));
		$this->assertSame($session, $cache->getValue($sessions)['9999999999.valid.new-key']);
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
