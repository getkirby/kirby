<?php

namespace Kirby\Content;

use Kirby\Cms\App;
use Kirby\Data\Data;
use Kirby\Filesystem\Dir;
use Kirby\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
	public const TMP = KIRBY_TMP_DIR;

	protected $model;

	public function assertContentFileExists(string|null $language = null, VersionId|null $versionId = null)
	{
		$this->assertFileExists($this->contentFile($language, $versionId));
	}

	public function assertContentFileDoesNotExist(string|null $language = null, VersionId|null $versionId = null)
	{
		$this->assertFileDoesNotExist($this->contentFile($language, $versionId));
	}

	public function contentFile(string|null $language = null, VersionId|null $versionId = null): string
	{
		return
			$this->model->root() .
			// add the changes folder
			($versionId?->value() === 'changes' ? '/_changes/' : '/') .
			// template
			'article' .
			// language code
			($language === null ? '' : '.' . $language) .
			'.txt';
	}

	public function createContentMultiLanguage(): array
	{
		Data::write($fileEN = $this->contentFile('en'), $contentEN = [
			'title'    => 'Title English',
			'subtitle' => 'Subtitle English'
		]);

		Data::write($fileDE = $this->contentFile('de'), $contentDE = [
			'title'    => 'Title Deutsch',
			'subtitle' => 'Subtitle Deutsch'
		]);

		return [
			'en' => [
				'content' => $contentEN,
				'file'    => $fileEN,
			],
			'de' => [
				'content' => $contentDE,
				'file'    => $fileDE,
			]
		];
	}

	public function createContentSingleLanguage(): array
	{
		Data::write($file = $this->contentFile(), $content = [
			'title'    => 'Title',
			'subtitle' => 'Subtitle'
		]);

		return [
			'content' => $content,
			'file'    => $file
		];
	}

	public function setUp(): void
	{
		Dir::make(static::TMP);
	}

	public function setUpMultiLanguage(): void
	{
		$this->app = new App([
			'languages' => [
				[
					'code'    => 'en',
					'default' => true
				],
				[
					'code' => 'de'
				]
			],
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'a-page',
						'template' => 'article',
					]
				]
			]
		]);

		$this->model = $this->app->page('a-page');

		Dir::make($this->model->root());
	}

	public function setUpSingleLanguage(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			],
			'site' => [
				'children' => [
					[
						'slug'     => 'a-page',
						'template' => 'article'
					]
				]
			]
		]);

		$this->model = $this->app->page('a-page');

		Dir::make($this->model->root());
	}

	public function tearDown(): void
	{
		App::destroy();
		Dir::remove(static::TMP);
	}
}
