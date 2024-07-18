<?php

namespace Kirby\Email;

use Kirby\Cms\User;
use Kirby\Cms\Users;
use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Email\Address
 * @covers ::__construct
 */
class AddressTest extends TestCase
{
	/**
	 * @covers ::email
	 */
	public function testEmail(): void
	{
		$address = new Address(email: $email = 'homer@simpson.com');
		$this->assertSame($email, $address->email());
	}

	/**
	 * @covers ::email
	 */
	public function testEmailInvalid(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('"homer@" is not a valid email address');
		new Address(email: 'homer@');
	}

	/**
	 * @covers ::factory
	 */
	public function testFactory(): void
	{
		$address = Address::factory($email = 'homer@simpson.com');
		$this->assertSame($email, $address->email());

		$address = Address::factory([$email => $name = 'Homer Simpson']);
		$this->assertSame($email, $address->email());
		$this->assertSame($name, $address->name());

		$user    = new User(['email' => $email, 'name' => $name]);
		$address = Address::factory($user);
		$this->assertSame($email, $address->email());
		$this->assertSame($name, $address->name());


		$address = Address::factory([$email => $name = 'Homer Simpson'], true);
		$this->assertSame($email, $address[0]->email());
		$this->assertSame($name, $address[0]->name());

		$address = Address::factory([$a = 'a@getkirby.com', $b = 'b@getkirby.com'], true);
		$this->assertSame($a, $address[0]->email());
		$this->assertSame($b, $address[1]->email());

		$users = new Users([
			new User(['email' => $a = 'ceo@company.com']),
			new User(['email' => $b = 'marketing@company.com'])
		]);
		$address = Address::factory($users, true);
		$this->assertSame($a, $address[0]->email());
		$this->assertSame($b, $address[1]->email());
	}

	/**
	 * @covers ::name
	 */
	public function testName(): void
	{
		$address = new Address(email: 'homer@simpson.com');
		$this->assertNull($address->name());

		$address = new Address(
			email: 'homer@simpson.com',
			name: $name = 'Homer Simpson'
		);
		$this->assertSame($name, $address->name());
	}
}
