<?php

namespace Kirby\Cms;

use Kirby\Content\Content;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class FileContentTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.FileContent';

	public function testContent(): void
	{
		$page = new Page(['slug' => 'test']);
		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page,
			'content'  => [
				'test' => 'Test'
			]
		]);

		$this->assertInstanceOf(Content::class, $file->content());
		$this->assertSame('Test', $file->content()->get('test')->value());
	}

	public function testContentFileData(): void
	{
		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $this->app->site()
		]);

		$this->assertSame([], $file->contentFileData([]));
		$this->assertSame(['foo' => 'bar'], $file->contentFileData(['foo' => 'bar']));
	}

	public function testContentFileDataWithExistingTemplate(): void
	{
		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $this->app->site(),
			'content'  => ['template' => 'image']
		]);

		$this->assertSame(['template' => 'image'], $file->contentFileData([]));
		$this->assertSame(['foo' => 'bar', 'template' => 'image'], $file->contentFileData(['foo' => 'bar']));
	}

	public function testContentFileDataWithNullTemplate(): void
	{
		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $this->app->site(),
		]);

		$this->assertSame(['template' => null], $file->contentFileData(['template' => null]));
	}

	public function testContentFileDataWithNullTemplateAndExistingTemplateInContent(): void
	{
		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $this->app->site(),
			'content'  => ['template' => 'image']
		]);

		$this->assertSame(['template' => null], $file->contentFileData(['template' => null]));
	}

	public function testContentFileDataWithDefaultTemplate(): void
	{
		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $this->app->site(),
		]);

		$this->assertSame([], $file->contentFileData(['template' => 'default']));
	}

	public function testContentFileDataInMultiLanguageMode(): void
	{
		$this->setupMultiLanguage();

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $this->app->site(),
		]);

		// ignore the template in non-default languages
		$this->assertSame(['template' => 'test'], $file->contentFileData(['template' => 'test']));
		$this->assertSame(['template' => 'test'], $file->contentFileData(['template' => 'test'], 'en'));
		$this->assertSame([], $file->contentFileData(['template' => 'test'], 'de'));

		// ignore the sort in non-default languages
		$this->assertSame(['sort' => 1], $file->contentFileData(['sort' => 1]));
		$this->assertSame(['sort' => 1], $file->contentFileData(['sort' => 1], 'en'));
		$this->assertSame([], $file->contentFileData(['sort' => 1], 'de'));
	}
}
