<?php

namespace Kirby\Cms;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class FilePreviewUrlTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.FilePreviewUrl';

	public function testPreviewUrl(): void
	{
		$page = new Page(['slug'  => 'test']);
		$file = new File([
			'filename' => 'test.pdf',
			'parent'   => $page
		]);

		$this->assertSame('/test/test.pdf', $file->previewUrl());
	}

	public function testPreviewUrlUnauthenticated(): void
	{
		$this->app->impersonate(null);

		$page = new Page(['slug'  => 'test']);
		$file = new File([
			'filename' => 'test.pdf',
			'parent'   => $page
		]);

		$this->assertSame('/media/pages/test/' . $file->mediaHash() . '/test.pdf', $file->previewUrl());
	}

	public function testPreviewUrlForDraft(): void
	{
		$page = new Page([
			'slug'    => 'test',
			'isDraft' => true
		]);
		$file = new File([
			'filename' => 'test.pdf',
			'parent'   => $page
		]);

		$this->assertSame($file->url(), $file->previewUrl());
	}

	public function testPreviewUrlForPageWithDeniedPreviewSetting(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/test' => [
					'options' => [
						'preview' => false
					]
				]
			],
			'users' => [
				[
					'email' => 'test@getkirby.com',
					'role'  => 'editor'
				]
			]
		]);

		$this->app->impersonate('test@getkirby.com');

		$page = new Page([
			'slug'    => 'test',
			'template' => 'test'
		]);

		$file = new File([
			'filename' => 'test.pdf',
			'parent'   => $page
		]);

		$this->assertSame(
			'/media/pages/test/' . $file->mediaHash() . '/test.pdf',
			$file->previewUrl()
		);
	}

	public function testPreviewUrlForPageWithCustomPreviewSetting(): void
	{
		$this->app = $this->app->clone([
			'blueprints' => [
				'pages/test' => [
					'options' => [
						'preview' => '/foo/bar'
					]
				]
			],
			'users' => [
				[
					'email' => 'test@getkirby.com',
					'role'  => 'editor'
				]
			]
		]);

		$this->app->impersonate('test@getkirby.com');

		$page = new Page([
			'slug'    => 'test',
			'template' => 'test'
		]);

		$file = new File([
			'filename' => 'test.pdf',
			'parent'   => $page
		]);

		$this->assertSame($file->url(), $file->previewUrl());
	}

	public function testPreviewUrlForUserFile(): void
	{
		$user = new User(['email' => 'test@getkirby.com']);
		$file = new File([
			'filename' => 'test.pdf',
			'parent'   => $user
		]);

		$this->assertSame($file->url(), $file->previewUrl());
	}

	public function testPreviewUrlForExtendedComponent(): void
	{
		$this->app = $this->app->clone([
			'components' => [
				'file::url' => fn ($kirby, $file, array $options = []) => 'https://getkirby.com/' . $file->filename()
			],
			'users' => [
				[
					'email' => 'test@getkirby.com',
					'role'  => 'editor'
				]
			]
		]);

		$this->app->impersonate('test@getkirby.com');

		$page = new Page(['slug' => 'test']);
		$file = new File([
			'filename' => 'test.pdf',
			'parent'   => $page
		]);

		$this->assertSame('https://getkirby.com/test.pdf', $file->previewUrl());
	}
}
