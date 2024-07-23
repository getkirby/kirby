<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Page\PageCopy
 */
class PageCopyTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.PageCopy';

	protected $app;

	public function setUp(): void
	{
		Dir::make(static::TMP);

		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
		]);

		$this->app->impersonate('kirby');
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
		App::destroy();
	}

	/**
	 * @covers ::trackUuid
	 */
	public function testTrackUuid(): void
	{
		$page = new Page([
			'slug'    => 'test',
			'content' => ['uuid' => 'old']
		]);

		$copy = new PageCopy($page);

		$copy->trackUuid(
			$page,
			fn () => $page->save(
				['uuid' => 'new']
			)
		);

		$this->assertSame(['page://old' => 'page://new'], $copy->uuids);
	}

	/**
	 * @coversNothing
	 */
	public function testTrackUuidNested(): void
	{
		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug'     => 'test',
						'content'  => ['uuid' => 'a'],
						'children' => [
							[
								'slug'     => 'test-a',
								'content'  => ['uuid' => 'aa'],
							],
							[
								'slug'     => 'test-b',
								'content'  => ['uuid' => 'ab'],
							]
						],
						'files' => [
							[
								'filename' => 'test-a.jpg',
								'content'  => ['uuid' => 'file-a'],
							],
							[
								'filename' => 'test-b.jpg',
								'content'  => ['uuid' => 'file-b'],
							],
						]
					]
				]
			]
		]);

		$page = $app->page('test');
		$copy = new PageCopy($page, files: true, children: true);
		$copy->adapt();

		$this->assertSame([
			'page://a',
			'file://file-a',
			'file://file-b',
			'page://aa',
			'page://ab',
		], array_keys($copy->uuids));
	}

}
