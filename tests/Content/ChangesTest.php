<?php

namespace Kirby\Content;

use Kirby\Cms\App;
use Kirby\Cms\Site;
use Kirby\TestCase;
use Kirby\Uuid\Uuids;

/**
 * @coversDefaultClass Kirby\Content\Changes
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
	 * @covers ::field
	 */
	public function testField()
	{
		$changes = new Changes();
		$this->assertInstanceOf(Field::class, $changes->field());
	}

	/**
	 * @covers ::files
	 */
	public function testFiles()
	{
		$site = App::instance()->site()->update([
			'changes' => [
				'file://test'
			]
		]);

		$changes = new Changes();

		$this->assertCount(1, $changes->files());
		$this->assertSame('test/test.jpg', $changes->files()->first()->id());
	}

	/**
	 * @covers ::pages
	 */
	public function testPages()
	{
		$site = App::instance()->site()->update([
			'changes' => [
				'page://test'
			]
		]);

		$changes = new Changes();

		$this->assertCount(1, $changes->pages());
		$this->assertSame('test', $changes->pages()->first()->id());
	}

	/**
	 * @covers ::site
	 */
	public function testSite()
	{
		$changes = new Changes();
		$this->assertInstanceOf(Site::class, $changes->site());
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

		$changes->update([
			$this->app->page('test')->uuid()->toString(),
			$this->app->file('test/test.jpg')->toString(),
			$this->app->user('test')->toString()
		]);

		$this->assertCount(1, $changes->files());
		$this->assertCount(1, $changes->pages());
		$this->assertCount(1, $changes->users());

		$changes->update([]);

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
		$site = App::instance()->site()->update([
			'changes' => [
				'user://test'
			]
		]);

		$changes = new Changes();

		$this->assertCount(1, $changes->users());
		$this->assertSame('test', $changes->users()->first()->id());
	}
}
