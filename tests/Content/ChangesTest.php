<?php

namespace Kirby\Content;

use Kirby\Cache\Cache;
use Kirby\Cms\App;
use Kirby\TestCase;
use Kirby\Uuid\Uuids;

/**
 * @coversDefaultClass \Kirby\Content\Changes
 */
class ChangesTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Content.Changes';

	public function setUp(): void
	{
		parent::setUpTmp();

		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug' => 'test',
						'content' => [
							'uuid' => 'test'
						],
						'files' => [
							[
								'filename' => 'test.jpg',
								'content'  => [
									'uuid' => 'test'
								],
							]
						]
					]
				]
			],
			'users' => [
				[
					'id' => 'test',
				]
			]
		]);

		$this->app->impersonate('kirby');

		Uuids::populate();
	}

	public function tearDown(): void
	{
		parent::tearDownTmp();
	}

	/**
	 * @covers ::cache
	 */
	public function testCache()
	{
		$cache = $this->app->cache('changes');

		$this->assertInstanceOf(Cache::class, $cache);
	}

	/**
	 * @covers ::files
	 * @covers ::ensure
	 */
	public function testFiles()
	{
		$this->app->cache('changes')->set('files', $cache = [
			'file://test'
		]);

		$changes = new Changes();

		// in cache, but changes don't exist in reality
		$this->assertCount(0, $changes->files());
		$this->assertSame([], $this->app->cache('changes')->get('files'));

		// in cache and changes exist in reality
		$this->app->file('test/test.jpg')->version(VersionId::latest())->save([]);
		$this->app->file('test/test.jpg')->version(VersionId::changes())->save([]);

		$this->assertSame($cache, $this->app->cache('changes')->get('files'));
		$this->assertCount(1, $changes->files());
		$this->assertSame('test/test.jpg', $changes->files()->first()->id());
	}

	/**
	 * @covers ::cacheKey
	 */
	public function testCacheKey()
	{
		$changes = new Changes();

		$page = $this->app->page('test');
		$file = $this->app->file('test/test.jpg');
		$user = $this->app->user('test');

		$this->assertSame('pages', $changes->cacheKey($page));
		$this->assertSame('files', $changes->cacheKey($file));
		$this->assertSame('users', $changes->cacheKey($user));
	}

	/**
	 * @covers ::cacheExists
	 * @covers ::generateCache
	 */
	public function testGenerateCache()
	{
		$changes = new Changes();

		$file = $this->app->file('test/test.jpg');
		$file->version(VersionId::latest())->save(['foo' => 'bar']);
		$file->version(VersionId::changes())->save(['foo' => 'bar']);

		$page = $this->app->page('test');
		$page->version(VersionId::latest())->save(['foo' => 'bar']);
		$page->version(VersionId::changes())->save(['foo' => 'bar']);

		$user = $this->app->user('test');
		$user->version(VersionId::latest())->save(['foo' => 'bar']);
		$user->version(VersionId::changes())->save(['foo' => 'bar']);

		$this->app->cache('changes')->flush();

		$this->assertFalse($changes->cacheExists());
		$this->assertSame([], $changes->read('files'));
		$this->assertSame([], $changes->read('pages'));
		$this->assertSame([], $changes->read('users'));

		$changes->generateCache();

		$this->assertTrue($changes->cacheExists());
		$this->assertSame(['file://test'], $changes->read('files'));
		$this->assertSame(['page://test'], $changes->read('pages'));
		$this->assertSame(['user://test'], $changes->read('users'));
	}

	/**
	 * @covers ::pages
	 * @covers ::ensure
	 */
	public function testPages()
	{
		$this->app->cache('changes')->set('pages', $cache = [
			'page://test'
		]);

		$changes = new Changes();

		// in cache, but changes don't exist in reality
		$this->assertCount(0, $changes->pages());
		$this->assertSame([], $this->app->cache('changes')->get('pages'));

		// in cache and changes exist in reality
		$this->app->page('test')->version(VersionId::latest())->save([]);
		$this->app->page('test')->version(VersionId::changes())->save([]);

		$this->assertSame($cache, $this->app->cache('changes')->get('pages'));
		$this->assertCount(1, $changes->pages());
		$this->assertSame('test', $changes->pages()->first()->id());
	}

	/**
	 * @covers ::read
	 */
	public function testRead()
	{
		$this->app->cache('changes')->set('files', [
			'file://test'
		]);

		$this->app->cache('changes')->set('pages', [
			'page://test'
		]);

		$this->app->cache('changes')->set('users', [
			'user://test'
		]);

		$changes = new Changes();

		$this->assertSame(['file://test'], $changes->read('files'));
		$this->assertSame(['page://test'], $changes->read('pages'));
		$this->assertSame(['user://test'], $changes->read('users'));
	}

	/**
	 * @covers ::track
	 */
	public function testTrack()
	{
		$changes = new Changes();

		$this->assertCount(0, $changes->read('files'));
		$this->assertCount(0, $changes->read('pages'));
		$this->assertCount(0, $changes->read('users'));

		$changes->track($this->app->page('test'));
		$changes->track($this->app->file('test/test.jpg'));
		$changes->track($this->app->user('test'));

		$this->assertCount(1, $files = $changes->read('files'));
		$this->assertCount(1, $pages = $changes->read('pages'));
		$this->assertCount(1, $users = $changes->read('users'));

		$this->assertSame('file://test', $files[0]);
		$this->assertSame('page://test', $pages[0]);
		$this->assertSame('user://test', $users[0]);
	}

	/**
	 * @covers ::track
	 */
	public function testTrackDisabledUuids()
	{
		$this->app = $this->app->clone([
			'options' => [
				'content' => [
					'uuid' => false
				]
			]
		]);

		$changes = new Changes();

		$this->assertCount(0, $changes->read('files'));
		$this->assertCount(0, $changes->read('pages'));
		$this->assertCount(0, $changes->read('users'));

		$changes->track($this->app->page('test'));
		$changes->track($this->app->file('test/test.jpg'));
		$changes->track($this->app->user('test'));

		$this->assertCount(1, $files = $changes->read('files'));
		$this->assertCount(1, $pages = $changes->read('pages'));
		$this->assertCount(1, $users = $changes->read('users'));

		$this->assertSame('test/test.jpg', $files[0]);
		$this->assertSame('test', $pages[0]);
		$this->assertSame('test', $users[0]);
	}

	/**
	 * @covers ::update
	 */
	public function testUpdate()
	{
		$changes = new Changes();

		$changes->update('files', [
			$this->app->file('test/test.jpg')->uuid()->toString(),
		]);

		$changes->update('pages', [
			$this->app->page('test')->uuid()->toString(),
		]);

		$changes->update('users', [
			$this->app->user('test')->uuid()->toString()
		]);

		$this->assertCount(1, $changes->read('files'));
		$this->assertCount(1, $changes->read('pages'));
		$this->assertCount(1, $changes->read('users'));

		$changes->update('files', []);
		$changes->update('pages', []);
		$changes->update('users', []);

		$this->assertCount(0, $changes->read('files'));
		$this->assertCount(0, $changes->read('pages'));
		$this->assertCount(0, $changes->read('users'));
	}

	/**
	 * @covers ::untrack
	 */
	public function testUntrack()
	{
		$changes = new Changes();

		$changes->track($this->app->page('test'));
		$changes->track($this->app->file('test/test.jpg'));
		$changes->track($this->app->user('test'));

		$this->assertCount(1, $changes->read('files'));
		$this->assertCount(1, $changes->read('pages'));
		$this->assertCount(1, $changes->read('users'));

		$changes->untrack($this->app->page('test'));
		$changes->untrack($this->app->file('test/test.jpg'));
		$changes->untrack($this->app->user('test'));

		$this->assertCount(0, $changes->read('files'));
		$this->assertCount(0, $changes->read('pages'));
		$this->assertCount(0, $changes->read('users'));
	}

	/**
	 * @covers ::untrack
	 */
	public function testUntrackDisabledUuids()
	{
		$this->app = $this->app->clone([
			'options' => [
				'content' => [
					'uuid' => false
				]
			]
		]);

		$changes = new Changes();

		$changes->track($this->app->page('test'));
		$changes->track($this->app->file('test/test.jpg'));
		$changes->track($this->app->user('test'));

		$this->assertCount(1, $changes->read('files'));
		$this->assertCount(1, $changes->read('pages'));
		$this->assertCount(1, $changes->read('users'));

		$changes->untrack($this->app->page('test'));
		$changes->untrack($this->app->file('test/test.jpg'));
		$changes->untrack($this->app->user('test'));

		$this->assertCount(0, $changes->read('files'));
		$this->assertCount(0, $changes->read('pages'));
		$this->assertCount(0, $changes->read('users'));
	}

	/**
	 * @covers ::users
	 * @covers ::ensure
	 */
	public function testUsers()
	{
		$this->app->cache('changes')->set('users', $cache = [
			'user://test'
		]);

		$changes = new Changes();

		// in cache, but changes don't exist in reality
		$this->assertCount(0, $changes->users());
		$this->assertSame([], $this->app->cache('changes')->get('users'));

		// in cache and changes exist in reality
		$this->app->user('test')->version(VersionId::latest())->save([]);
		$this->app->user('test')->version(VersionId::changes())->save([]);

		$this->assertSame($cache, $this->app->cache('changes')->get('users'));
		$this->assertCount(1, $changes->users());
		$this->assertSame('test', $changes->users()->first()->id());
	}
}
