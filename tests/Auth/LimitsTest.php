<?php

namespace Kirby\Auth;

use Kirby\Auth\Exception\RateLimitException;
use Kirby\Filesystem\Dir;
use Kirby\Tests\MockTime;

class LimitsTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Auth.Limits';

	protected Limits $limits;
	public string|null $failedEmail = null;

	protected function setUp(): void
	{
		parent::setUp();
		$self      = $this;
		$this->app = $this->app->clone([
			'roots' => [
				'index' => static::TMP
			],
			'users' => [
				['email' => 'marge@simpsons.com'],
				['email' => 'homer@simpsons.com'],
				['email' => 'test@exämple.com']
			],
			'hooks' => [
				'user.login:failed' => function ($email) use ($self) {
					$self->failedEmail = $email;
				}
			]
		]);

		Dir::make(static::TMP . '/site/accounts');
		$this->limits = new Limits($this->app);
	}

	public function testEnsure(): void
	{
		copy(
			static::FIXTURES . '/logins.json',
			static::TMP . '/site/accounts/.logins'
		);

		$this->app->visitor()->ip('10.1.123.234');

		try {
			$this->limits->ensure('homer@simpsons.com');
			$this->fail('Expected a RateLimitException');
		} catch (RateLimitException) {
			// expected
		}

		// the user.login:failed hook must fire before the exception
		$this->assertSame('homer@simpsons.com', $this->failedEmail);
	}

	public function testEnsurePasses(): void
	{
		copy(
			static::FIXTURES . '/logins.json',
			static::TMP . '/site/accounts/.logins'
		);

		// IP 10.1 (87084f, 9 trials) and marge are both below
		// the limit, so ensure() must not throw or fire the hook
		$this->app->visitor()->ip('10.1.123.234');
		$this->limits->ensure('marge@simpsons.com');

		$this->assertNull($this->failedEmail);
	}

	public function testFile(): void
	{
		$this->assertSame(
			static::TMP . '/site/accounts/.logins',
			$this->limits->file()
		);
	}

	public function testIsBlocked(): void
	{
		copy(
			static::FIXTURES . '/logins.json',
			static::TMP . '/site/accounts/.logins'
		);

		$this->app->visitor()->ip('10.1.123.234');
		$this->assertFalse($this->limits->isBlocked('marge@simpsons.com'));
		$this->assertTrue($this->limits->isBlocked('homer@simpsons.com'));
		$this->assertFalse($this->limits->isBlocked('lisa@simpsons.com'));

		$this->app->visitor()->ip('10.2.123.234');
		$this->assertTrue($this->limits->isBlocked('marge@simpsons.com'));
		$this->assertTrue($this->limits->isBlocked('homer@simpsons.com'));
		$this->assertTrue($this->limits->isBlocked('lisa@simpsons.com'));
	}

	public function testIsBlockedWithCustomTrialsOption(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'auth' => ['trials' => 5]
			]
		]);
		$this->limits = new Limits($this->app);

		file_put_contents($this->limits->file(), json_encode([
			'by-ip' => [
				'87084f11690867b977a611dd2c943a918c3197f4c02b25ab59' => [
					'time'   => 9999999999,
					'trials' => 5
				]
			],
			'by-email' => []
		]));

		$this->app->visitor()->ip('10.1.123.234');

		// 5 trials reaches the custom limit of 5
		$this->assertTrue($this->limits->isBlocked('marge@simpsons.com'));
	}

	public function testIsBlockedByEmailForUnknownUser(): void
	{
		// IP 10.1 (87084f) is not present, so the only possible
		// block comes from the email-based limit
		file_put_contents($this->limits->file(), json_encode([
			'by-ip' => [],
			'by-email' => [
				'ghost@simpsons.com' => [
					'time'   => 9999999999,
					'trials' => 10
				]
			]
		]));

		$this->app->visitor()->ip('10.1.123.234');

		// ghost is not a registered user, but the email-based limit
		// still applies because isBlocked() does no user lookup
		$this->assertTrue($this->limits->isBlocked('ghost@simpsons.com'));
	}

	public function testLog(): void
	{
		copy(
			static::FIXTURES . '/logins.cleanup.json',
			static::TMP . '/site/accounts/.logins'
		);

		// should delete expired and old entries and add by-email array
		$expected = [
			'by-ip' => [
				'38f0a08519792a17e18a251008f3a116977907f921b0b287c8' => [
					'time'   => 9999999999,
					'trials' => 5
				]
			],
			'by-email' => []
		];

		$this->assertSame($expected, $this->limits->log());
		$this->assertFileEquals(
			static::FIXTURES . '/logins.cleanup-cleaned.json',
			static::TMP . '/site/accounts/.logins'
		);

		// should handle missing .logins file
		unlink(static::TMP . '/site/accounts/.logins');

		$expected = [
			'by-ip'    => [],
			'by-email' => []
		];

		$this->assertSame($expected, $this->limits->log());
		$this->assertFileDoesNotExist(static::TMP . '/site/accounts/.logins');

		// should handle invalid .logins file
		file_put_contents(
			static::TMP . '/site/accounts/.logins',
			'some gibberish'
		);

		$this->assertSame($expected, $this->limits->log());
		$this->assertFileDoesNotExist(static::TMP . '/site/accounts/.logins');
	}

	public function testLogRemovesLegacyKeysWithoutExpiredEntries(): void
	{
		$file = $this->limits->file();

		// a fresh (non-expired) entry plus an unknown top-level
		// key from old log structure
		file_put_contents($file, json_encode([
			'by-ip' => [
				'87084f11690867b977a611dd2c943a918c3197f4c02b25ab59' => [
					'time'   => 9999999999,
					'trials' => 5
				]
			],
			'legacy' => [
				'foo' => 'bar'
			]
		]));

		$expected = [
			'by-ip' => [
				'87084f11690867b977a611dd2c943a918c3197f4c02b25ab59' => [
					'time'   => 9999999999,
					'trials' => 5
				]
			],
			'by-email' => []
		];

		// the legacy key is stripped from the return value ...
		$this->assertSame($expected, $this->limits->log());

		// ... and rewritten to disk even though nothing expired
		$this->assertSame(json_encode($expected), file_get_contents($file));
	}

	public function testTrack(): void
	{
		copy(
			static::FIXTURES . '/logins.json',
			static::TMP . '/site/accounts/.logins'
		);

		$this->app->visitor()->ip('10.1.123.234');
		$this->assertTrue($this->limits->track('homer@simpsons.com'));
		$this->assertTrue($this->limits->track('homer@simpsons.com'));
		$this->assertTrue($this->limits->track('marge@simpsons.com'));
		$this->assertTrue($this->limits->track('lisa@simpsons.com'));
		$this->assertTrue($this->limits->track('lisa@simpsons.com'));

		$this->app->visitor()->ip('10.2.123.234');
		$this->assertTrue($this->limits->track('homer@simpsons.com'));
		$this->assertTrue($this->limits->track('marge@simpsons.com'));
		$this->assertTrue($this->limits->track('lisa@simpsons.com'));

		$this->app->visitor()->ip('10.3.123.234');
		$this->assertTrue($this->limits->track('homer@simpsons.com'));
		$this->assertTrue($this->limits->track('marge@simpsons.com'));
		$this->assertTrue($this->limits->track('lisa@simpsons.com', false));
		$this->assertSame('marge@simpsons.com', $this->failedEmail);

		$this->assertTrue($this->limits->track(null));
		$this->assertNull($this->failedEmail);

		$expected = [
			'by-ip' => [
				'87084f11690867b977a611dd2c943a918c3197f4c02b25ab59' => [
					'time'   => MockTime::$time,
					'trials' => 14,
				],
				'38f0a08519792a17e18a251008f3a116977907f921b0b287c8' => [
					'time'   => MockTime::$time,
					'trials' => 13,
				],
				'85a06e36d926cb901f05d1167913ebd7ec3d8f5bce4551f5da' => [
					'trials' => 4,
					'time'   => MockTime::$time,
				]
			],
			'by-email' => [
				'homer@simpsons.com' => [
					'time'   => MockTime::$time,
					'trials' => 14,
				],
				'marge@simpsons.com' => [
					'trials' => 3,
					'time'   => MockTime::$time,
				]
			]
		];
		$this->assertSame($expected, $this->limits->log());
		$this->assertSame(
			json_encode($expected),
			file_get_contents(static::TMP . '/site/accounts/.logins')
		);
	}

	public function testTrackCreatesFile(): void
	{
		$file = $this->limits->file();
		$this->assertFileDoesNotExist($file);

		$this->app->visitor()->ip('10.1.123.234');
		$this->assertTrue($this->limits->track('homer@simpsons.com'));

		$this->assertFileExists($file);

		$log = $this->limits->log();
		$this->assertSame(
			1,
			$log['by-ip']['87084f11690867b977a611dd2c943a918c3197f4c02b25ab59']['trials']
		);
		$this->assertSame(1, $log['by-email']['homer@simpsons.com']['trials']);
	}

	public function testTrackWithCorruptedFile(): void
	{
		file_put_contents($this->limits->file(), 'some gibberish');

		$this->app->visitor()->ip('10.1.123.234');
		$this->assertTrue($this->limits->track('homer@simpsons.com'));

		// the unreadable contents are discarded and a fresh log
		// is written instead of throwing
		$log = $this->limits->log();
		$this->assertSame(
			1,
			$log['by-ip']['87084f11690867b977a611dd2c943a918c3197f4c02b25ab59']['trials']
		);
		$this->assertSame(1, $log['by-email']['homer@simpsons.com']['trials']);
	}
}
