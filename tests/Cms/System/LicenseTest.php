<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\F;
use Kirby\Toolkit\Str;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;

#[CoversClass(License::class)]
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

	public function testActivation(): void
	{
		$license = new License(
			activation: $date = '2023-12-01'
		);

		$this->assertSame(strtotime($date), $license->activation());
		$this->assertSame($date, $license->activation('Y-m-d'));
		$this->assertSame('1/12/2023 00:00', $license->activation('d/M/yyyy HH:mm', 'intl'));
	}

	public function testCode(): void
	{
		$license = new License(
			code: $code = $this->code(LicenseType::Enterprise)
		);

		$this->assertSame($code, $license->code());
		$this->assertSame('K-ENT-1234XXXXXXXXXXXXXXXXXXXXXX', $license->code(true));
	}

	public function testContent(): void
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

	public function testDate(): void
	{
		$license = new License(
			date: $date = '2023-12-01'
		);

		$this->assertSame(strtotime($date), $license->date());
		$this->assertSame($date, $license->date('Y-m-d'));
		$this->assertSame('1/12/2023 00:00', $license->date('d/M/yyyy HH:mm', 'intl'));
	}

	public function testDelete(): void
	{
		$license = new License();
		F::write($license->root(), 'test');

		$this->assertFileExists($license->root());
		$this->assertTrue($license->delete());
		$this->assertFileDoesNotExist($license->root());
	}

	public function testDomain(): void
	{
		$license = new License(
			domain: $domain = 'getkirby.com'
		);

		$this->assertSame($domain, $license->domain());
	}

	public function testEmail(): void
	{
		$license = new License(
			email: $email = 'mail@getkirby.com'
		);

		$this->assertSame($email, $license->email());
	}

	public function testHasValidEmailAddress(): void
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

	public function testHub(): void
	{
		$this->assertSame('https://hub.getkirby.com', License::hub());
	}

	public function testIsComplete(): void
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

	public function testIsInactive(): void
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

	public function testIsLegacy(): void
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

	public function testIsOnCorrectDomain(): void
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

	public function testLabel(): void
	{
		$license = new License();
		$this->assertSame('Unregistered', $license->label());
	}

	public function testNormalize(): void
	{
		$code = $this->code(LicenseType::Enterprise);

		$license = new License(
			code : '   ' . $code . ' ',
			email: '   mail@getkirby.com '
		);

		$this->assertSame($code, $license->code());
		$this->assertSame('mail@getkirby.com', $license->email());
	}

	#[DataProvider('providerForLicenseUrls')]
	public function testNormalizeDomain(string $domain, string $expected): void
	{
		$reflector = new ReflectionClass(License::class);
		$normalize = $reflector->getMethod('normalizeDomain');

		$license = new License();

		$this->assertSame($expected, $normalize->invoke($license, $domain));
	}

	public function testOrder(): void
	{
		$license = new License(
			order: $order = '123456'
		);

		$this->assertSame($order, $license->order());
	}

	public function testPolyfill(): void
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

	public function testRead(): void
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

	public function testRegisterWithInvalidDomain(): void
	{
		$license = new License(
			code: $this->code(),
			email: 'mail@getkirby.com'
		);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The domain for the license is missing');

		$license->register();
	}

	public function testRegisterWithInvalidEmail(): void
	{
		$license = new License(
			code: $this->code(),
			email: 'invalid'
		);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid email address');

		$license->register();
	}

	public function testRegisterWithInvalidLicenseKey(): void
	{
		$license = new License();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Please enter a valid license code');

		$license->register();
	}

	public function testRenewal(): void
	{
		// activated
		$license = new License(
			activation: '2023-12-01'
		);

		$this->assertSame(strtotime('2026-12-01'), $license->renewal());
		$this->assertSame('2026-12-01', $license->renewal('Y-m-d'));
		$this->assertSame('1/12/2026 00:00', $license->renewal('d/M/yyyy HH:mm', 'intl'));

		// not activated
		$license = new License();
		$this->assertNull($license->renewal('Y-m-d'));
	}

	public function testRoot(): void
	{
		$this->assertSame(
			App::instance()->root('license'),
			License::root()
		);
	}

	public function testSaveWhenNotActivatable(): void
	{
		$license = new License();

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The license could not be verified');

		$license->save();
	}

	public function testSignature(): void
	{
		$license = new License(
			signature: 'secret'
		);

		$this->assertSame('secret', $license->signature());
	}

	public function testStatus(): void
	{
		$license = new License();
		$this->assertSame(LicenseStatus::Missing, $license->status());
	}

	public function testTypeKirby3(): void
	{
		$license = new License(
			code: $this->code(LicenseType::Legacy)
		);

		$this->assertSame(LicenseType::Legacy, $license->type());
	}

	public function testTypeKirbyBasic(): void
	{
		$license = new License(
			code: $this->code()
		);

		$this->assertSame(LicenseType::Basic, $license->type());
	}

	public function testTypeKirbyEnterprise(): void
	{
		$license = new License(
			code: $this->code(LicenseType::Enterprise)
		);

		$this->assertSame(LicenseType::Enterprise, $license->type());
	}

	public function testTypeUnregistered(): void
	{
		$license = new License();

		$this->assertSame(LicenseType::Invalid, $license->type());
	}
}
