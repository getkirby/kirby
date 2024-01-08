<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Str;
use ReflectionClass;

/**
 * @coversDefaultClass \Kirby\Cms\License
 */
class LicenseTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/LicenseTest';
	public const TMP      = KIRBY_TMP_DIR . '/Cms.License';

	public function code(LicenseType $type = LicenseType::Basic): string
	{
		return $type->prefix() . '1234' . Str::random(28);
	}

	public static function providerForLicenseUrls(): array
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

	/**
	 * @covers ::activation
	 */
	public function testActivation()
	{
		$license = new License(
			activation: $date = '2023-12-01'
		);

		$this->assertSame(strtotime($date), $license->activation());
		$this->assertSame($date, $license->activation('Y-m-d'));
	}

	/**
	 * @covers ::code
	 */
	public function testCode()
	{
		$license = new License(
			code: $code = $this->code(LicenseType::Enterprise)
		);

		$this->assertSame($code, $license->code());
		$this->assertSame('K-ENT-1234XXXXXXXXXXXXXXXXXXXXXX', $license->code(true));
	}

	/**
	 * @covers ::__construct
	 * @covers ::content
	 */
	public function testContent()
	{
		$license = new License(
			code: $code = $this->code(LicenseType::Enterprise)
		);

		$this->assertSame([
			'activation' => null,
			'code'       => $code,
			'date'       => null,
			'domain'     => null,
			'email'      => null,
			'order'      => null,
			'signature'  => null,
		], $license->content());
	}

	/**
	 * @covers ::date
	 */
	public function testDate()
	{
		$license = new License(
			date: $date = '2023-12-01'
		);

		$this->assertSame(strtotime($date), $license->date());
		$this->assertSame($date, $license->date('Y-m-d'));
	}

	/**
	 * @covers ::domain
	 */
	public function testDomain()
	{
		$license = new License(
			domain: $domain = 'getkirby.com'
		);

		$this->assertSame($domain, $license->domain());
	}

	/**
	 * @covers ::email
	 */
	public function testEmail()
	{
		$license = new License(
			email: $email = 'mail@getkirby.com'
		);

		$this->assertSame($email, $license->email());
	}

	/**
	 * @covers ::hasValidEmailAddress
	 */
	public function testHasValidEmailAddress()
	{
		$license = new License(
			email: 'mail@getkirby.com'
		);

		$this->assertTrue($license->hasValidEmailAddress());

		$license = new License(
			email: 'mail@getkirby'
		);

		$this->assertFalse($license->hasValidEmailAddress());
	}

	/**
	 * @covers ::hub
	 */
	public function testHub()
	{
		$this->assertSame('https://hub.getkirby.com', License::hub());
	}

	/**
	 * @covers ::isComplete
	 */
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

	/**
	 * @covers ::isInactive
	 */
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

	/**
	 * @covers ::isLegacy
	 */
	public function testIsLegacy()
	{
		// legacy license type
		$license = new License(
			code: $this->code(LicenseType::Legacy)
		);

		$this->assertTrue($license->isLegacy());

		// current license type, but never activated
		$license = new License(
			code: $this->code(LicenseType::Basic)
		);

		$this->assertTrue($license->isLegacy());

		// current license type, but activated longer than 3 years ago
		$license = new License(
			code: $this->code(LicenseType::Basic),
			activation: '2020-01-01'
		);

		$this->assertTrue($license->isLegacy());

		// current license type, but activated today
		$license = new License(
			code: $this->code(LicenseType::Basic),
			activation: date('Y-m-d')
		);

		$this->assertFalse($license->isLegacy());
	}

	/**
	 * @covers ::isOnCorrectDomain
	 */
	public function testIsOnCorrectDomain()
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'options' => [
				'url' => 'https://getkirby.com'
			]
		]);

		// no domain
		$license = new License();
		$this->assertFalse($license->isOnCorrectDomain());

		// invalid domain
		$license = new License(
			domain: 'foo.bar'
		);
		$this->assertFalse($license->isOnCorrectDomain());

		// valid domain
		$license = new License(
			domain: 'getkirby.com'
		);

		$this->assertTrue($license->isOnCorrectDomain());
	}

	/**
	 * @covers ::label
	 */
	public function testLabel()
	{
		$license = new License();
		$this->assertSame('Unregistered', $license->label());
	}

	/**
	 * @covers ::normalizeDomain
	 * @dataProvider providerForLicenseUrls
	 */
	public function testNormalizeDomain(string $domain, string $expected)
	{
		$reflector = new ReflectionClass(License::class);
		$normalize = $reflector->getMethod('normalizeDomain');
		$normalize->setAccessible(true);

		$license = new License();

		$this->assertSame($expected, $normalize->invoke($license, $domain));
	}

	/**
	 * @covers ::order
	 */
	public function testOrder()
	{
		$license = new License(
			order: $order = '123456'
		);

		$this->assertSame($order, $license->order());
	}

	/**
	 * @covers ::polyfill
	 */
	public function testPolyfill()
	{
		$this->assertSame([
			'activation' => null,
			'code'       => 'abc',
			'date'       => null,
			'domain'     => null,
			'email'      => null,
			'order'      => null,
			'signature'  => null,

		], License::polyfill([
			'license' => 'abc',
		]));
	}

	/**
	 * @covers ::read
	 */
	public function testRead()
	{
		// existing license
		$this->app = new App([
			'roots' => [
				'license' => static::FIXTURES . '/.license'
			]
		]);

		$license = License::read();
		$this->assertSame('K-BAS-DOTKFOOYKBWYGSD4BYP2EXHJJJRLBFOO', $license->code());
		$this->assertSame('getkirby.com', $license->domain());

		// non-existing license root
		$this->app = new App([
			'roots' => [
				'license' => static::FIXTURES . '/foo'
			]
		]);

		$license = License::read();
		$this->assertNull($license->code());
	}

	/**
	 * @covers ::register
	 */
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

	/**
	 * @covers ::register
	 */
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

	/**
	 * @covers ::register
	 */
	public function testRegisterWithInvalidLicenseKey()
	{
		$license = new License();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid license code');

		$license->register();
	}

	/**
	 * @covers ::renewal
	 */
	public function testRenewal()
	{
		// activated
		$license = new License(
			activation: '2023-12-01'
		);

		$this->assertSame(strtotime('2026-12-01'), $license->renewal());
		$this->assertSame('2026-12-01', $license->renewal('Y-m-d'));

		// not activated
		$license = new License();
		$this->assertNull($license->renewal('Y-m-d'));
	}

	/**
	 * @covers ::save
	 */
	public function testSaveWhenNotActivatable()
	{
		$license = new License();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The license could not be verified');

		$license->save();
	}

	/**
	 * @covers ::signature
	 */
	public function testSignature()
	{
		$license = new License(
			signature: 'secret'
		);

		$this->assertSame('secret', $license->signature());
	}

	/**
	 * @covers ::status
	 */
	public function testStatus()
	{
		$license = new License();
		$this->assertSame(LicenseStatus::Missing, $license->status());
	}

	/**
	 * @covers ::type
	 */
	public function testTypeKirby3()
	{
		$license = new License(
			code: $this->code(LicenseType::Legacy)
		);

		$this->assertSame(LicenseType::Legacy, $license->type());
	}

	/**
	 * @covers ::type
	 */
	public function testTypeKirbyBasic()
	{
		$license = new License(
			code: $this->code()
		);

		$this->assertSame(LicenseType::Basic, $license->type());
	}

	/**
	 * @covers ::type
	 */
	public function testTypeKirbyEnterprise()
	{
		$license = new License(
			code: $this->code(LicenseType::Enterprise)
		);

		$this->assertSame(LicenseType::Enterprise, $license->type());
	}

	/**
	 * @covers ::type
	 */
	public function testTypeUnregistered()
	{
		$license = new License();

		$this->assertSame(LicenseType::Invalid, $license->type());
	}
}
