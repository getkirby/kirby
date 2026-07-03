<?php

namespace Kirby\Session;

use Kirby\Cms\App;
use Kirby\Http\Cookie as HttpCookie;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Cookie::class)]
class CookieTest extends TestCase
{
	protected string $key;
	protected array $cookies;

	protected function setUp(): void
	{
		$this->key     = HttpCookie::$key;
		$this->cookies = $_COOKIE;
	}

	protected function tearDown(): void
	{
		HttpCookie::$key = $this->key;
		$_COOKIE         = $this->cookies;

		App::destroy();
	}

	public function testConstruct(): void
	{
		// defaults
		$cookie = new Cookie();
		$this->assertSame('kirby_session', $cookie->name());
		$this->assertNull($cookie->domain());

		// custom values
		$cookie = new Cookie('my_session', 'getkirby.com');
		$this->assertSame('my_session', $cookie->name());
		$this->assertSame('getkirby.com', $cookie->domain());
	}

	public function testDomain(): void
	{
		$cookie = new Cookie();
		$this->assertNull($cookie->domain());

		$cookie = new Cookie(domain: 'getkirby.com');
		$this->assertSame('getkirby.com', $cookie->domain());
	}

	public function testGet(): void
	{
		$cookie = new Cookie('my_session');

		// no cookie set yet
		$this->assertNull($cookie->get());

		// reads from the configured cookie name
		HttpCookie::set('my_session', 'theToken');
		$this->assertSame('theToken', $cookie->get());
	}

	public function testName(): void
	{
		$cookie = new Cookie();
		$this->assertSame('kirby_session', $cookie->name());

		$cookie = new Cookie('my_session');
		$this->assertSame('my_session', $cookie->name());
	}

	public function testRemove(): void
	{
		$cookie = new Cookie('my_session');

		HttpCookie::set('my_session', 'theToken');
		$this->assertTrue(HttpCookie::exists('my_session'));

		$cookie->remove();
		$this->assertFalse(HttpCookie::exists('my_session'));
	}

	public function testSet(): void
	{
		$cookie = new Cookie('my_session');
		$cookie->set('theToken', 9999999999);

		$this->assertTrue(HttpCookie::exists('my_session'));
		$this->assertSame('theToken', HttpCookie::get('my_session'));
	}
}
