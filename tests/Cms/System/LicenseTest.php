<?php

namespace Kirby\Cms;

/**
 * @coversDefaultClass Kirby\Cms\License
 */
class LicenseTest extends TestCase
{

	public function testActivated()
	{
		$license = new License(
			activated: $date = '2023-12-01'
		);

		$this->assertSame(strtotime($date), $license->activated());
		$this->assertSame($date, $license->activated('Y-m-d'));
	}

	public function testCode()
	{
		$license = new License(
			code: $code = 'K-ENT-12345678910123456789101234'
		);

		$this->assertSame($code, $license->code());
		$this->assertSame('K-ENT-1234XXXXXXXXXXXXXXXXXXXXXX', $license->code(true));
	}

	public function testDomain()
	{
		$license = new License(
			domain: $domain = 'getkirby.com'
		);

		$this->assertSame($domain, $license->domain());
	}

	public function testEmail()
	{
		$license = new License(
			email: $email = 'mail@getkirby.com'
		);

		$this->assertSame($email, $license->email());
	}

	public function testHub()
	{
		$this->assertSame('https://hub.getkirby.com', License::hub());
	}

	public function testIsComplete()
	{
		// incomplete
		$license = new License();
		$this->assertFalse($license->isComplete());

		// complete
		$license = new License(
			code: 'K-ENT-1234',
			domain: 'getkirby.com',
			email: 'mail@getkirby.com',
			order: '1234',
			purchased: '2023-12-01',
			signature: 'secret',
		);

		$this->assertTrue($license->isComplete());
	}

	public function testIsInactive()
	{
		MockTime::$time = strtotime('now');

		// active
		$license = new License(
			activated: date('Y-m-d')
		);

		$this->assertFalse($license->isInactive());

		// inactive
		$license = new License(
			activated: date('Y-m-d', strtotime('-4 years'))
		);

		$this->assertTrue($license->isInactive());

		MockTime::reset();
	}

	public function testIsOnCorrectDomain()
	{
		$this->app = new App([
			'options' => [
				'url' => 'https://getkirby.com'
			]
		]);

		// invalid domain
		$license = new License();
		$this->assertFalse($license->isOnCorrectDomain());

		// valid domain
		$license = new License(
			domain: 'getkirby.com'
		);

		$this->assertTrue($license->isOnCorrectDomain());
	}

	public function testLabelWhenUnregistered()
	{
		$license = new License();
		$this->assertSame('Unregistered', $license->label());
	}

	public function testOrder()
	{
		$license = new License(
			order: $order = '123456'
		);

		$this->assertSame($order, $license->order());
	}

	public function testPurchased()
	{
		$license = new License(
			purchased: $date = '2023-12-01'
		);

		$this->assertSame(strtotime($date), $license->purchased());
		$this->assertSame($date, $license->purchased('Y-m-d'));
	}

	public function testRenewal()
	{
		$license = new License(
			activated: '2023-12-01'
		);

		$this->assertSame(strtotime('2026-12-01'), $license->renewal());
		$this->assertSame('2026-12-01', $license->renewal('Y-m-d'));
	}

	public function testSignature()
	{
		$license = new License(
			signature: 'secret'
		);

		$this->assertSame('secret', $license->signature());
	}

	public function testStatus()
	{
		$license = new License();

		$this->assertTrue($license->status() === LicenseStatus::Missing);
	}

	public function testTypeKirby3()
	{
		$license = new License(
			code: 'K3-PRO-1234'
		);

		$this->assertSame('Kirby 3', $license->type());
	}

	public function testTypeKirbyBasic()
	{
		$license = new License(
			code: 'K-BAS-1234'
		);

		$this->assertSame('Kirby Basic', $license->type());
	}

	public function testTypeKirbyEnterprise()
	{
		$license = new License(
			code: 'K-ENT-1234'
		);

		$this->assertSame('Kirby Enterprise', $license->type());
	}

	public function testTypeUnregistered()
	{
		$license = new License();

		$this->assertNull($license->type());
	}

}
