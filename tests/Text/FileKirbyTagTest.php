<?php

namespace Kirby\Text;

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;

class FileKirbyTagTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Text.FileKirbyTag';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		Dir::remove(static::TMP);
	}

	public function testFile(): void
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'content' => [
							'text' => '(file: a.jpg)'
						],
						'files' => [
							[
								'filename' => 'a.jpg',
							]
						]
					]
				]
			]
		]);

		$page = $kirby->page('a');
		$file = $page->file('a.jpg');

		$expected = '<p><a download href="' . $file->url() . '">a.jpg</a></p>';

		$this->assertSame($expected, $page->text()->kt()->value());
	}

	public function testFileWithUUID(): void
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'content' => [
							'text' => '(file: file://file-a)',
							'uuid' => 'page-uuid' // this is just to make sure that the test doesn't try to create a content file for this page with a generated UUID
						],
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

		$expected = '<p><a download href="' . $file->url() . '">a.jpg</a></p>';

		$this->assertSame($expected, $page->text()->kt()->value());
	}

	public function testFileDoesNotExist(): void
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'content' => [
							'text' => '(file: a.jpg) (file: b.jpg text: b)'
						]
					],

				]
			]
		]);

		$page = $kirby->page('a');

		$this->assertSame('<p>a.jpg b</p>', $page->text()->kt()->value());
	}

	public function testFileWithDisabledDownloadOption(): void
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'content' => [
							'text' => '(file: a.jpg download: false)'
						],
						'files' => [
							[
								'filename' => 'a.jpg',
							]
						]
					]
				]
			]
		]);

		$page = $kirby->page('a');
		$file = $page->file('a.jpg');

		$expected = '<p><a href="' . $file->url() . '">a.jpg</a></p>';

		$this->assertSame($expected, $page->text()->kt()->value());
	}

	public function testFileWithinFile(): void
	{
		$kirby = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'a',
						'files' => [
							[
								'filename' => 'a.jpg',
								'content' => [
									'caption' => '(file: b.jpg)'
								]
							],
							[
								'filename' => 'b.jpg'
							]
						]
					]
				]
			]
		]);

		$a = $kirby->file('a/a.jpg');
		$b = $kirby->file('a/b.jpg');
		$expected = '<p><a download href="' . $b->url() . '">b.jpg</a></p>';

		$this->assertSame($expected, $a->caption()->kt()->value());
	}
}
