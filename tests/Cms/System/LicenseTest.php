<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Str;
use ReflectionClass;

/**
 * @coversDefaultClass Kirby\Cms\License
 */
class LicenseTest extends TestCase
{
	public function code(LicenseType $type = LicenseType::Basic): string
	{
		return $type->prefix() . '1234' . Str::random(28);
	}

	public function providerForLicenseUrls()
	{
		return [
			['example.com', 'example.com'],
			['www.example.com', 'example.com'],
			['dev.example.com', 'example.com'],
			['test.example.com', 'example.com'],
			['staging.example.com', 'example.com'],
			['sub.example.com', 'sub.example.com'],
			['www.example.com/test', 'www.example.com/test'],
			['dev.example.com/test', 'dev.example.com/test'],
			['test.example.com/test', 'test.example.com/test'],
			['staging.example.com/test', 'staging.example.com/test'],
			['sub.example.com/test', 'sub.example.com/test'],
		];
	}

	public function testActivation()
	{
		$license = new License(
			activation: $date = '2023-12-01'
		);

		$this->assertSame(strtotime($date), $license->activation());
		$this->assertSame($date, $license->activation('Y-m-d'));
	}

	public function testCode()
	{
		$license = new License(
			code: $code = $this->code(LicenseType::Enterprise)
		);

		$this->assertSame($code, $license->code());
		$this->assertSame('K-ENT-1234XXXXXXXXXXXXXXXXXXXXXX', $license->code(true));
	}

	public function testDate()
	{
		$license = new License(
			date: $date = '2023-12-01'
		);

		$this->assertSame(strtotime($date), $license->date());
		$this->assertSame($date, $license->date('Y-m-d'));
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
			code: $this->code(LicenseType::Enterprise),
			date: '2023-12-01',
			domain: 'getkirby.com',
			email: 'mail@getkirby.com',
			order: '1234',
			signature: 'secret',
		);

		$this->assertTrue($license->isComplete());
	}

	public function testIsInactive()
	{
		MockTime::$time = strtotime('now');

		// active
		$license = new License(
			activation: date('Y-m-d')
		);

		$this->assertFalse($license->isInactive());

		// inactive
		$license = new License(
			activation: date('Y-m-d', strtotime('-4 years'))
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

	public function testRegisterWithInvalidDomain()
	{
		$license = new License(
			code: $this->code(),
			email: 'mail@getkirby.com'
		);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The domain for the license is missing');

		$license->register();
	}

	public function testRegisterWithInvalidEmail()
	{
		$license = new License(
			code: $this->code(),
			email: 'invalid'
		);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid email address');

		$license->register();
	}

	public function testRegisterWithInvalidLicenseKey()
	{
		$license = new License();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid license key');

		$license->register();
	}

	public function testRenewal()
	{
		$license = new License(
			activation: '2023-12-01'
		);

		$this->assertSame(strtotime('2026-12-01'), $license->renewal());
		$this->assertSame('2026-12-01', $license->renewal('Y-m-d'));
	}

	/**
	 * @dataProvider providerForLicenseUrls
	 */
	public function testSanitizeDomain(string $domain, string $expected)
	{
		$reflector = new ReflectionClass(License::class);
		$sanitize = $reflector->getMethod('sanitizeDomain');
		$sanitize->setAccessible(true);

		$license = new License();

		$this->assertSame($expected, $sanitize->invoke($license, $domain));
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
			code: $this->code(LicenseType::Legacy)
		);

		$this->assertSame(LicenseType::Legacy, $license->type());
	}

	public function testTypeKirbyBasic()
	{
		$license = new License(
			code: $this->code()
		);

		$this->assertSame(LicenseType::Basic, $license->type());
	}

	public function testTypeKirbyEnterprise()
	{
		$license = new License(
			code: $this->code(LicenseType::Enterprise)
		);

		$this->assertSame(LicenseType::Enterprise, $license->type());
	}

	public function testTypeUnregistered()
	{
		$license = new License();

		$this->assertSame(LicenseType::Invalid, $license->type());
	}
}
