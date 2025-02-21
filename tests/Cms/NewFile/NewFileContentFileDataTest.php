<?php

namespace Kirby\Cms;

use Kirby\Cms\NewFile as File;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class NewFileContentFileDataTest extends NewModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.NewFileContentFileData';

	public function testContentFileDataInMultiLanguageMode(): void
	{
		$this->setUpMultiLanguage();

		$file = new File([
			'parent'   => $this->app->site(),
			'filename' => 'test.jpg',
			'template' => 'test'
		]);

		$this->assertSame(['template' => 'test'], $file->contentFileData([]));
		$this->assertSame(['template' => 'test'], $file->contentFileData([], 'en'));
		$this->assertSame([], $file->contentFileData([], 'de', 'The template should not be added in secondary languages'));
	}

	public function testContentFileDataInMultiLanguageModeWithSort(): void
	{
		$this->setUpMultiLanguage();

		$file = new File([
			'parent'   => $this->app->site(),
			'filename' => 'test.jpg',
		]);

		$input = [
			'sort' => 1
		];

		$this->assertSame($input, $file->contentFileData($input));
		$this->assertSame($input, $file->contentFileData($input, 'en'));
		$this->assertSame([], $file->contentFileData($input, 'de', 'The sort field should not be added in secondary languages'));
	}

	public function testContentFileDataInSingleLanguageMode(): void
	{
		$file = new File([
			'parent'   => $this->app->site(),
			'filename' => 'test.jpg'
		]);

		$this->assertSame([], $file->contentFileData([]));
	}

	public function testContentFileDataInSingleLanguageModeWithSort(): void
	{
		$file = new File([
			'parent'   => $this->app->site(),
			'filename' => 'test.jpg'
		]);

		$input = [
			'sort' => 1
		];

		$this->assertSame($input, $file->contentFileData($input));
	}

	public function testContentFileDataInSingleLanguageModeWithTemplate(): void
	{
		$file = new File([
			'parent'   => $this->app->site(),
			'filename' => 'test.jpg',
			'template' => 'test'
		]);

		$expected = [
			'template' => 'test'
		];

		$this->assertSame($expected, $file->contentFileData([]));
	}

	public function testContentFileDataInSingleLanguageModeWithOverwrittenTemplate(): void
	{
		$file = new File([
			'parent'   => $this->app->site(),
			'filename' => 'test.jpg',
			'template' => 'test'
		]);

		$this->assertSame(['template' => null], $file->contentFileData([
			'template' => null
		]));
	}

}
