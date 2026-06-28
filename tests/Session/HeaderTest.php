<?php

namespace Kirby\Session;

use Kirby\Cms\App;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Header::class)]
class HeaderTest extends TestCase
{
	public function testGet(): void
	{
		$header = new Header();

		// no Authorization header
		$this->assertNull($header->get());

		// correct "Session" scheme
		$this->setAuthorization('Session amazingSessionIdFromHeader');
		$this->assertSame('amazingSessionIdFromHeader', $header->get());

		// wrong scheme
		$this->setAuthorization('Bearer amazingSessionIdFromHeader');
		$this->assertNull($header->get());
	}

	public function testValue(): void
	{
		$header = new Header();
		$this->assertSame('Session theToken', $header->value('theToken'));
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
