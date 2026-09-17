<?php

namespace Kirby\Text\Tag;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileTag::class)]
class FileTagTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Text.FileTag';

	protected function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);

		Dir::make(static::TMP);
	}

	protected function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testRender(): void
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'files' => [
							['filename' => 'a.jpg']
						]
					]
				]
			]
		]);

		$page = $kirby->page('a');
		$file = $page->file('a.jpg');

		$tag = FileTag::factory('file', 'a.jpg', [], ['parent' => $page]);

		$this->assertSame(
			'<a download href="' . $file->url() . '">a.jpg</a>',
			$tag->render()
		);
	}

	public function testRenderWithUUID(): void
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'files' => [
							[
								'filename' => 'a.jpg',
								'content'  => ['uuid' => 'file-a']
							]
						]
					]
				]
			]
		]);

		$page = $kirby->page('a');
		$file = $page->file('a.jpg');

		$tag = FileTag::factory('file', 'file://file-a', [], ['parent' => $page]);

		$this->assertSame(
			'<a download href="' . $file->url() . '">a.jpg</a>',
			$tag->render()
		);
	}

	public function testRenderFileDoesNotExist(): void
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'a']
				]
			]
		]);

		$page = $kirby->page('a');

		// falls back to the value when there is no text
		$tag = FileTag::factory('file', 'a.jpg', [], ['parent' => $page]);
		$this->assertSame('a.jpg', $tag->render());

		// falls back to the text when given
		$tag = FileTag::factory('file', 'b.jpg', ['text' => 'b'], ['parent' => $page]);
		$this->assertSame('b', $tag->render());
	}

	public function testRenderWithDisabledDownloadOption(): void
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'files' => [
							['filename' => 'a.jpg']
						]
					]
				]
			]
		]);

		$page = $kirby->page('a');
		$file = $page->file('a.jpg');

		$tag = FileTag::factory('file', 'a.jpg', ['download' => 'false'], ['parent' => $page]);

		$this->assertSame(
			'<a href="' . $file->url() . '">a.jpg</a>',
			$tag->render()
		);
	}

	public function testRenderWithinFile(): void
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'files' => [
							['filename' => 'a.jpg'],
							['filename' => 'b.jpg']
						]
					]
				]
			]
		]);

		$a = $kirby->file('a/a.jpg');
		$b = $kirby->file('a/b.jpg');

		$tag = FileTag::factory('file', 'b.jpg', [], ['parent' => $a]);

		$this->assertSame(
			'<a download href="' . $b->url() . '">b.jpg</a>',
			$tag->render()
		);
	}
}
