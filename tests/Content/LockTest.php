<?php

namespace Kirby\Content;

use Kirby\Cms\App;
use Kirby\Cms\User;

/**
 * @coversDefaultClass \Kirby\Content\Lock
 * @covers ::__construct
 */
class LockTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Content.LockTest';

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

	/**
	 * @covers ::for
	 */
	public function testForWithAuthenticatedUser()
	{
		$this->app->impersonate('admin');

		$version = new Version(
			model: $this->app->page('test'),
			id: VersionId::changes()
		);

		$version->create([
			'title' => 'Test'
		]);

		$lock = Lock::for($version);

		$this->assertTrue($lock->isActive());
		$this->assertFalse($lock->isLocked());
		$this->assertSame($this->app->user('admin'), $lock->user());
	}

	/**
	 * @covers ::for
	 */
	public function testForWithDifferentUser()
	{
		// create the version with the admin user
		$this->app->impersonate('admin');

		$version = new Version(
			model: $this->app->page('test'),
			id: VersionId::changes()
		);

		$version->create([
			'title' => 'Test'
		]);

		// switch to a different user to simulate locked content
		$this->app->impersonate('editor');

		$lock = Lock::for($version);

		$this->assertTrue($lock->isActive());
		$this->assertTrue($lock->isLocked());
		$this->assertSame($this->app->user('admin'), $lock->user());
	}

	/**
	 * @covers ::for
	 */
	public function testForWithoutUser()
	{
		// create the version with the admin user
		$this->app->impersonate('admin');

		// the published version won't have a user id
		$version = new Version(
			model: $this->app->page('test'),
			id: VersionId::published()
		);

		$version->create([
			'title' => 'Test'
		]);

		$lock = Lock::for($version);

		$this->assertNull($lock->user());
	}

	/**
	 * @covers ::isActive
	 */
	public function testIsActive()
	{
		// just modified
		$lock = new Lock(
			modified: time()
		);

		$this->assertTrue($lock->isActive());
	}

	/**
	 * @covers ::isActive
	 */
	public function testIsActiveWithOldModificationTimestamp()
	{
		// create a lock that has not been modified for 20 minutes
		$lock = new Lock(
			modified: time() - 60 * 20
		);

		$this->assertFalse($lock->isActive());
	}

	/**
	 * @covers ::isActive
	 */
	public function testIsActiveWithoutModificationTimestamp()
	{
		// a lock without modification time should also be inactive
		$lock = new Lock();
		$this->assertFalse($lock->isActive());
	}

	/**
	 * @covers ::isEnabled
	 */
	public function testIsEnabled()
	{
		$this->assertTrue(Lock::isEnabled());
	}

	/**
	 * @covers ::isEnabled
	 */
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

	/**
	 * @covers ::isLocked
	 */
	public function testIsLocked()
	{
		$lock = new Lock();
		$this->assertFalse($lock->isLocked());
	}

	/**
	 * @covers ::isLocked
	 */
	public function testIsLockedWithCurrentUser()
	{
		$this->app->impersonate('admin');

		$lock = new Lock(
			modified: time(),
			user: $this->app->user('admin')
		);

		$this->assertFalse($lock->isLocked());
	}

	/**
	 * @covers ::isLocked
	 */
	public function testIsLockedWithDifferentUser()
	{
		$this->app->impersonate('admin');

		$lock = new Lock(
			modified: time(),
			user: $this->app->user('editor')
		);

		$this->assertTrue($lock->isLocked());
	}

	/**
	 * @covers ::isLocked
	 */
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

	/**
	 * @covers ::isLocked
	 */
	public function testIsLockedWithDifferentUserAndOldTimestamp()
	{
		$this->app->impersonate('admin');

		$lock = new Lock(
			modified: time() - 60 * 20,
			user: $this->app->user('editor')
		);

		$this->assertFalse($lock->isLocked());
	}

	/**
	 * @covers ::modified
	 */
	public function testModified()
	{
		$lock = new Lock(
			modified: $modified = time()
		);

		$this->assertSame($modified, $lock->modified());
		$this->assertSame(date('c', $modified), $lock->modified('c'));
	}

	/**
	 * @covers ::toArray
	 */
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
			'isLocked' => true,
			'modified' => date('c', $modified),
			'user'     => [
				'id'    => 'test',
				'email' => 'test@getkirby.com'
			]
		], $lock->toArray());
	}

	/**
	 * @covers ::user
	 */
	public function testUser()
	{
		$lock = new Lock(
			user: $user = $this->app->user('admin')
		);

		$this->assertSame($user, $lock->user());
	}

	/**
	 * @covers ::user
	 */
	public function testUserWithoutUser()
	{
		$lock = new Lock();
		$this->assertNull($lock->user());
	}
}
