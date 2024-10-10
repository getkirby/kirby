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
		$cache = App::instance()->cache('changes');

		$this->assertInstanceOf(Cache::class, $cache);
	}

	/**
	 * @covers ::files
	 */
	public function testFiles()
	{
		App::instance()->cache('changes')->set('files', [
			'file://test'
		]);

		$changes = new Changes();

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
	 * @covers ::pages
	 */
	public function testPages()
	{
		App::instance()->cache('changes')->set('pages', [
			'page://test'
		]);

		$changes = new Changes();

		$this->assertCount(1, $changes->pages());
		$this->assertSame('test', $changes->pages()->first()->id());
	}

	/**
	 * @covers ::read
	 */
	public function testRead()
	{
		App::instance()->cache('changes')->set('files', [
			'file://test'
		]);

		App::instance()->cache('changes')->set('pages', [
			'page://test'
		]);

		App::instance()->cache('changes')->set('users', [
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

		$this->assertCount(0, $changes->files());
		$this->assertCount(0, $changes->pages());
		$this->assertCount(0, $changes->users());

		$changes->track($this->app->page('test'));
		$changes->track($this->app->file('test/test.jpg'));
		$changes->track($this->app->user('test'));

		$this->assertCount(1, $changes->files());
		$this->assertCount(1, $changes->pages());
		$this->assertCount(1, $changes->users());

		$this->assertSame('test', $changes->pages()->first()->id());
		$this->assertSame('test/test.jpg', $changes->files()->first()->id());
		$this->assertSame('test', $changes->users()->first()->id());
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

		$this->assertCount(1, $changes->files());
		$this->assertCount(1, $changes->pages());
		$this->assertCount(1, $changes->users());

		$changes->update('files', []);
		$changes->update('pages', []);
		$changes->update('users', []);

		$this->assertCount(0, $changes->files());
		$this->assertCount(0, $changes->pages());
		$this->assertCount(0, $changes->users());
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

		$this->assertCount(1, $changes->files());
		$this->assertCount(1, $changes->pages());
		$this->assertCount(1, $changes->users());

		$changes->untrack($this->app->page('test'));
		$changes->untrack($this->app->file('test/test.jpg'));
		$changes->untrack($this->app->user('test'));

		$this->assertCount(0, $changes->files());
		$this->assertCount(0, $changes->pages());
		$this->assertCount(0, $changes->users());
	}

	/**
	 * @covers ::users
	 */
	public function testUsers()
	{
		App::instance()->cache('changes')->set('users', [
			'user://test'
		]);

		$changes = new Changes();

		$this->assertCount(1, $changes->users());
		$this->assertSame('test', $changes->users()->first()->id());
	}
}
