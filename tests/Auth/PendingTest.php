<?php

namespace Kirby\Auth;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Pending::class)]
class PendingTest extends TestCase
{
	public function testPublic(): void
	{
		$pending = new Pending();
		$this->assertNull($pending->public());

		$pending = new Pending(public: ['foo' => 'bar']);
		$this->assertSame(['foo' => 'bar'], $pending->public());
	}

	public function testSecret(): void
	{
		$pending = new Pending();
		$this->assertNull($pending->secret());

		$pending = new Pending(secret: 'top-secret');
		$this->assertSame('top-secret', $pending->secret());
	}

	public function testToArray(): void
	{
		$data     = new Pending();
		$expected = ['public' => null, 'secret' => null];
		$this->assertSame($expected, $data->toArray());

		$data = new Pending(
			public: $public = ['foo' => 'bar'],
			secret: $secret = 'top-secret'
		);
		$expected = ['public' => $public, 'secret' => $secret];
		$this->assertSame($expected, $data->toArray());
	}

	public function testFrom(): void
	{
		$session = ['public' => 'shown', 'secret' => 'hidden'];
		$pending = Pending::from($session);

		$this->assertSame('shown', $pending->public());
		$this->assertSame('hidden', $pending->secret());

		$pending = Pending::from();
		$this->assertNull($pending->public());
		$this->assertNull($pending->secret());
	}
}
