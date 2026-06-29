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

	protected function setUp(): void
	{
		parent::setUp();
		MockTime::reset();
	}

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

		$license = new License(
			code: $code = LicenseType::Free->prefix()
		);

		$this->assertNUll($license->code());
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
			'expires'    => null,
			'signature'  => null,
		], $license->content());

		// the reissue backoff is not persisted
		// while no retry cycle is running
		$this->assertArrayNotHasKey('failures', $license->content());
		$this->assertArrayNotHasKey('checked', $license->content());
	}

	public function testContentWithReissueBackoff(): void
	{
		$license = new License(
			code: $code = $this->code(LicenseType::Enterprise),
			failures: 2,
			checked: '2024-01-01 00:00:00'
		);

		$this->assertSame([
			'activation' => null,
			'code'       => $code,
			'date'       => null,
			'domain'     => null,
			'email'      => null,
			'order'      => null,
			'expires'    => null,
			'signature'  => null,
			'failures'   => 2,
			'checked'    => '2024-01-01 00:00:00',
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

	public function testIsExpired(): void
	{
		$license = new License();
		$this->assertFalse($license->isExpired());

		$license = new License(
			expires: '9999-12-01',
		);
		$this->assertFalse($license->isExpired());

		$license = new License(
			expires: '2000-12-01',
		);
		$this->assertTrue($license->isExpired());
	}

	public function testIsFree(): void
	{
		$license = new License(
			code: $this->code(LicenseType::Basic),
		);

		$this->assertFalse($license->isFree());

		$license = new License(
			code: LicenseType::Free->prefix()
		);

		$this->assertTrue($license->isFree());
	}

	public function testIsFreeAndLocal(): void
	{
		// local
		$this->app->clone([
			'server' => [
				'REMOTE_ADDR' => '127.0.0.1',
			]
		]);

		$license = new License(
			code: LicenseType::Free->prefix()
		);

		$this->assertTrue($license->isFreeAndLocal());

		$license = new License(
			code: $this->code(LicenseType::Basic)
		);

		$this->assertFalse($license->isFreeAndLocal());

		// not local
		$this->app->clone([
			'server' => [
				'REMOTE_ADDR' => '1.2.3.4',
			]
		]);

		$license = new License(
			code: LicenseType::Free->prefix()
		);

		$this->assertFalse($license->isFreeAndLocal());
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
			'expires'    => null,
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

		// reads the reissue backoff bookkeeping from the file
		$this->app = new App([
			'roots' => [
				'license' => $root = static::TMP . '/.license'
			]
		]);

		F::write($root, json_encode([
			'code'     => $this->code(LicenseType::Enterprise),
			'failures' => 3,
			'checked'  => '2024-01-01 00:00:00',
		]));

		$content = License::read()->content();
		$this->assertSame(3, $content['failures']);
		$this->assertSame('2024-01-01 00:00:00', $content['checked']);
	}

	public function testRegisterFreeAndLocal(): void
	{
		$this->app->clone([
			'options' => [
				'url' => 'https://sandbox.test',
			],
			'server' => [
				'REMOTE_ADDR' => '127.0.0.1',
			]
		]);

		$license = new License(
			code:   LicenseType::Free->prefix(),
			domain: 'sandbox.test'
		);

		$license->register();

		$system  = new System($this->app);
		$license = $system->license();

		$this->assertSame('sandbox.test', $license->domain());
		$this->assertSame(LicenseStatus::Acknowledged, $license->status());
		$this->assertSame(LicenseType::Free, $license->type());
		$this->assertTrue($license->isComplete());
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

	public function testReissue(): void
	{
		$this->app = new App([
			'roots' => [
				'index'   => static::TMP,
				'license' => static::TMP . '/.license'
			]
		]);

		// a valid (non-expired) license is left untouched
		$license = new License();
		$this->assertFalse($license->isExpired());
		$license->reissue();
		$this->assertFalse($license->isExpired());

		// a failed reissue keeps the expired license alive and
		// records the failure so the next requests can back off
		// (here the reissue fails on the missing email address)
		$license = new License(
			code: $this->code(LicenseType::Enterprise),
			domain: 'getkirby.com',
			expires: '2000-01-01 00:00:00'
		);

		$license->reissue();
		$this->assertTrue($license->isExpired());
		$this->assertSame(1, $license->content()['failures']);
		$this->assertSame(1, License::read()->content()['failures']);

		// an expired license that has exhausted all reissue attempts
		// drops its license file so the activation banner reappears
		$license = new License(
			code: $this->code(LicenseType::Enterprise),
			domain: 'getkirby.com',
			expires: '2000-01-01 00:00:00',
			failures: 6,
			checked: '2000-01-01 00:00:00'
		);

		F::write($license->root(), 'test');
		$license->reissue();
		$this->assertFileDoesNotExist($license->root());

		// an expired free + local license is reissued by self-signing,
		// which also clears the backoff state on success
		$this->app->clone([
			'options' => [
				'url' => 'https://sandbox.test',
			],
			'server' => [
				'REMOTE_ADDR' => '127.0.0.1',
			]
		]);

		$license = new License(
			code: LicenseType::Free->prefix(),
			domain: 'sandbox.test',
			expires: '2000-01-01 00:00:00',
			failures: 2,
			checked: '2000-01-01 00:00:00'
		);

		$license->reissue();

		$this->assertFalse($license->isExpired());
		$this->assertArrayNotHasKey('failures', $license->content());
	}

	public function testReissueBackoff(): void
	{
		$now = MockTime::$time;

		$this->app = new App([
			'roots' => [
				'license' => static::TMP . '/.license'
			]
		]);

		// a license whose reissue always fails (missing email), so
		// that each actual hub attempt is observable as an incremented
		// failure counter, while a skipped attempt leaves it untouched
		$attempt = function (int $failures, string|null $checked) {
			$license = new License(
				code: $this->code(LicenseType::Enterprise),
				domain: 'getkirby.com',
				expires: '2000-01-01 00:00:00',
				failures: $failures,
				checked: $checked
			);

			$license->reissue();

			return $license->content()['failures'] ?? 0;
		};

		// first attempt after expiry: check immediately → attempt
		$this->assertSame(1, $attempt(0, null));

		// within the backoff window of the first failure (5 min) → skip
		$this->assertSame(1, $attempt(1, date('Y-m-d H:i:s', $now - 4 * 60)));

		// backoff window of the first failure has passed → attempt
		$this->assertSame(2, $attempt(1, date('Y-m-d H:i:s', $now - 5 * 60)));

		// within the backoff window of the fifth failure (24 h) → skip
		$this->assertSame(5, $attempt(5, date('Y-m-d H:i:s', $now - 1439 * 60)));

		// backoff window of the fifth failure has passed → attempt
		$this->assertSame(6, $attempt(5, date('Y-m-d H:i:s', $now - 1440 * 60)));
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

	public function testSignatureData(): void
	{
		$reflector = new ReflectionClass(License::class);
		$salt      = $reflector->getConstant('SALT');

		$license = new License(
			activation: '2024-01-01 12:00:00',
			code: $code = $this->code(LicenseType::Enterprise),
			date: '2024-02-02 12:00:00',
			domain: 'getkirby.com',
			email: $email = 'mail@getkirby.com',
			order: '123456',
			expires: '2025-01-01 00:00:00'
		);

		$this->assertSame([
			'activation' => '2024-01-01 12:00:00',
			'code'       => $code,
			'date'       => '2024-02-02 12:00:00',
			'domain'     => 'getkirby.com',
			'email'      => hash('sha256', $email . $salt),
			'order'      => '123456',
			'expires'    => '2025-01-01 00:00:00',
		], $license->signatureData());


		// without expiry
		$license = new License(
			activation: '2024-01-01 12:00:00',
			code: $code = $this->code(LicenseType::Enterprise),
			date: '2024-02-02 12:00:00',
			domain: 'getkirby.com',
			email: $email = 'mail@getkirby.com',
			order: '123456'
		);

		$this->assertSame([
			'activation' => '2024-01-01 12:00:00',
			'code'       => $code,
			'date'       => '2024-02-02 12:00:00',
			'domain'     => 'getkirby.com',
			'email'      => hash('sha256', $email . $salt),
			'order'      => '123456',
		], $license->signatureData());

		// legacy
		$license = new License(
			code: $code = $this->code(LicenseType::Legacy),
			date: '2021-01-01 00:00:00',
			domain: 'legacy.getkirby.com',
			email: $email = 'legacy@getkirby.com',
			order: '87654321'
		);

		$this->assertSame([
			'license' => $code,
			'order'   => '87654321',
			'email'   => hash('sha256', $email . $salt),
			'domain'  => 'legacy.getkirby.com',
			'date'    => '2021-01-01 00:00:00',
		], $license->signatureData());
	}

	public function testStatus(): void
	{
		$license = new License();
		$this->assertSame(LicenseStatus::Missing, $license->status());
	}

	public function testTypeFree(): void
	{
		$license = new License(
			code: 'FREE'
		);

		$this->assertSame(LicenseType::Free, $license->type());
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
