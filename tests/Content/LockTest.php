<?php

namespace Kirby\Content;

use Kirby\Cms\App;
use Kirby\Cms\Language;
use Kirby\Cms\User;
use Kirby\Data\Data;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Lock::class)]
class LockTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Content.LockTest';

	protected function createChangesVersion(
		Language|string $language = 'default'
	): Version {
		$version = new Version(
			model: $this->app->page('test'),
			id: VersionId::changes()
		);

		$version->create([
			'title' => 'Test'
		], $language);

		return $version;
	}

	protected function createLatestVersion(
		Language|string $language = 'default'
	): Version {
		$latest = new Version(
			model: $this->app->page('test'),
			id: VersionId::latest()
		);

		$latest->create([
			'title' => 'Test'
		], $language);

		return $latest;
	}

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test'
					]
				]
			],
			'users' => [
				[
					'email' => 'admin@getkirby.com',
					'id'    => 'admin',
				],
				[
					'email' => 'editor@getkirby.com',
					'id'    => 'editor',
				]
			]
		]);
	}

	public function testForWithAuthenticatedUser()
	{
		$this->app->impersonate('admin');

		$latest  = $this->createLatestVersion();
		$changes = $this->createChangesVersion();
		$lock    = Lock::for($changes);

		$this->assertTrue($lock->isActive());
		$this->assertFalse($lock->isLocked());
		$this->assertSame($this->app->user('admin'), $lock->user());
	}

	public function testForWithDifferentUser()
	{
		// create the version with the admin user
		$this->app->impersonate('admin');

		$latest  = $this->createLatestVersion();
		$changes = $this->createChangesVersion();

		// switch to a different user to simulate locked content
		$this->app->impersonate('editor');

		$lock = Lock::for($changes);

		$this->assertTrue($lock->isActive());
		$this->assertTrue($lock->isLocked());
		$this->assertSame($this->app->user('admin'), $lock->user());
	}

	public function testForWithoutUser()
	{
		// create the version with the admin user
		$this->app->impersonate('admin');

		$latest = $this->createLatestVersion();
		$lock   = Lock::for($latest);

		$this->assertNull($lock->user());
	}

	public function testForWithLanguageWildcard()
	{
		$this->app = $this->app->clone([
			'languages' => [
				[
					'code'    => 'en',
					'default' => true
				],
				[
					'code' => 'de'
				]
			]
		]);

		// create the version with the admin user
		$this->app->impersonate('admin');

		$this->createLatestVersion('en');
		$this->createLatestVersion('de');

		$this->createChangesVersion('de');

		// switch to a different user to simulate locked content
		$this->app->impersonate('editor');

		$changes = $this->app->page('test')->version('changes');
		$lock    = Lock::for($changes, '*');

		$this->assertSame('admin', $lock->user()->id());
	}

	public function testForWithLegacyLock()
	{
		$page = $this->app->page('test');
		$file = $page->root() . '/.lock';

		Data::write($file, [
			'/' . $page->id() => [
				'lock' => [
					'user' => 'editor',
					'time' => $time = time()
				]
			]
		], 'yml');

		$lock = Lock::for($page->version('changes'));
		$this->assertInstanceOf(Lock::class, $lock);
		$this->assertTrue($lock->isLocked());
	}

	public function testIsActive()
	{
		// just modified
		$lock = new Lock(
			modified: time()
		);

		$this->assertTrue($lock->isActive());
	}

	public function testIsActiveWithOldModificationTimestamp()
	{
		// create a lock that has not been modified for 20 minutes
		$lock = new Lock(
			modified: time() - 60 * 20
		);

		$this->assertFalse($lock->isActive());
	}

	public function testIsActiveWithoutModificationTimestamp()
	{
		// a lock without modification time should also be inactive
		$lock = new Lock();
		$this->assertFalse($lock->isActive());
	}

	public function testIsEnabled()
	{
		$this->assertTrue(Lock::isEnabled());
	}

	public function testIsEnabledWhenDisabled()
	{
		$this->app = $this->app->clone([
			'options' => [
				'content' => [
					'locking' => false,
				]
			]
		]);

		$this->assertFalse(Lock::isEnabled());
	}

	public function testIsLegacy()
	{
		$lock = new Lock();
		$this->assertFalse($lock->isLegacy());

		$lock = new Lock(legacy: true);
		$this->assertTrue($lock->isLegacy());
	}

	public function testIsLocked()
	{
		$lock = new Lock();
		$this->assertFalse($lock->isLocked());
	}

	public function testIsLockedWithCurrentUser()
	{
		$this->app->impersonate('admin');

		$lock = new Lock(
			modified: time(),
			user: $this->app->user('admin')
		);

		$this->assertFalse($lock->isLocked());
	}

	public function testIsLockedWithDifferentUser()
	{
		$this->app->impersonate('admin');

		$lock = new Lock(
			modified: time(),
			user: $this->app->user('editor')
		);

		$this->assertTrue($lock->isLocked());
	}

	public function testIsLockedWhenDisabled()
	{
		$this->app = $this->app->clone([
			'options' => [
				'content' => [
					'locking' => false
				]
			]
		]);

		$this->app->impersonate('admin');

		$lock = new Lock(
			modified: time(),
			user: $this->app->user('editor')
		);

		$this->assertFalse($lock->isLocked());
	}

	public function testIsLockedWithDifferentUserAndOldTimestamp()
	{
		$this->app->impersonate('admin');

		$lock = new Lock(
			modified: time() - 60 * 20,
			user: $this->app->user('editor')
		);

		$this->assertFalse($lock->isLocked());
	}

	public function testLegacy()
	{
		$page = $this->app->page('test');
		$file = $page->root() . '/.lock';

		Data::write($file, [
			'/' . $page->id() => [
				'lock' => [
					'user' => 'editor',
					'time' => $time = time()
				]
			]
		], 'yml');

		$lock = Lock::legacy($page);

		$this->assertInstanceOf(Lock::class, $lock);
		$this->assertTrue($lock->isLocked());
		$this->assertTrue($lock->isLegacy());
		$this->assertSame($this->app->user('editor'), $lock->user());
		$this->assertSame($time, $lock->modified());
	}

	public function testLegacyWithoutLockInfo()
	{
		$page = $this->app->page('test');
		$file = $page->root() . '/.lock';

		Data::write($file, [], 'yml');

		$lock = Lock::legacy($page);
		$this->assertNull($lock);
	}

	public function testLegacyWithOutdatedFile()
	{
		$page = $this->app->page('test');
		$file = $page->root() . '/.lock';

		Data::write($file, [
			'/' . $page->id() => [
				'lock' => [
					'user' => 'editor',
					'time' => time() - 60 * 60 * 24
				],
			]
		], 'yml');

		$lock = Lock::legacy($page);

		$this->assertInstanceOf(Lock::class, $lock);
		$this->assertFalse($lock->isLocked());
	}

	public function testLegacyWithUnlockedFile()
	{
		$page = $this->app->page('test');
		$file = $page->root() . '/.lock';

		Data::write($file, [
			'/' . $page->id() => [
				'lock' => [
					'user' => 'editor',
					'time' => time()
				],
				'unlock' => ['admin']
			]
		], 'yml');

		$lock = Lock::legacy($page);
		$this->assertNull($lock);
	}

	public function testLegacyFile()
	{
		$page = $this->app->page('test');
		$expected = $page->root() . '/.lock';

		$this->assertSame($expected, Lock::legacyFile($page));
	}

	public function testModified()
	{
		$lock = new Lock(
			modified: $modified = time()
		);

		$this->assertSame($modified, $lock->modified());
		$this->assertSame(date('c', $modified), $lock->modified('c'));
	}

	public function testToArray()
	{
		$lock = new Lock(
			user: $user = new User([
				'email' => 'test@getkirby.com',
				'id'    => 'test'
			]),
			modified: $modified = time()
		);

		$this->assertSame([
			'isLegacy' => false,
			'isLocked' => true,
			'modified' => date('c', $modified),
			'user'     => [
				'id'    => 'test',
				'email' => 'test@getkirby.com'
			]
		], $lock->toArray());
	}

	public function testUser()
	{
		$lock = new Lock(
			user: $user = $this->app->user('admin')
		);

		$this->assertSame($user, $lock->user());
	}

	public function testUserWithoutUser()
	{
		$lock = new Lock();
		$this->assertNull($lock->user());
	}
}
