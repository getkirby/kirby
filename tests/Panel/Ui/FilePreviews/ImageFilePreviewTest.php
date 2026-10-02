<?php

namespace Kirby\Panel\Ui\FilePreviews;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Filesystem\F;
use Kirby\Panel\Ui\FilePreview;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ImageFilePreview::class)]
class ImageFilePreviewTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Ui.FilePreviews.ImageFilePreview';

	protected function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP
			]
		]);

		$this->setUpTmp();
	}

	protected function tearDown(): void
	{
		$this->tearDownTmp();
		parent::tearDown();
	}

	public function testAccepts(): void
	{
		$page = new Page(['slug' => 'test']);

		$file = new File(['filename' => 'test.jpg', 'parent' => $page]);
		$this->assertTrue(ImageFilePreview::accepts($file));

		$file = new File(['filename' => 'test.xls', 'parent' => $page]);
		$this->assertFalse(ImageFilePreview::accepts($file));
	}

	public function testDetails(): void
	{
		$page    = new Page(['slug' => 'test']);
		$file    = new File(['filename' => 'test.jpg', 'parent' => $page]);
		$preview = new ImageFilePreview($file);
		$details = $preview->details();

		$detail = array_pop($details);
		$this->assertSame('Orientation', $detail['title']);

		$detail = array_pop($details);
		$this->assertSame('Dimensions', $detail['title']);
	}

	public function testFactory(): void
	{
		$page    = new Page(['slug' => 'test']);
		$file    = new File(['filename' => 'test.jpg', 'parent' => $page]);

		$preview = FilePreview::factory($file);
		$this->assertInstanceOf(ImageFilePreview::class, $preview);
		$this->assertSame('k-image-file-preview', $preview->component);
	}

	public function testProps(): void
	{
		$page    = new Page(['slug' => 'test']);
		$file    = new File(['filename' => 'test.xls', 'parent' => $page]);
		$preview = new ImageFilePreview($file);
		$props   = $preview->props();
		$this->assertFalse($props['focusable']);
		$this->assertNull($props['ratio']);
	}

	public static function ratioProvider(): array
	{
		return [
			'viewBox'          => ['viewBox="0 0 188 152"', 188 / 152],
			'percentages'      => ['width="100%" height="100%" viewBox="0 0 400 100"', 4.0],
			'own size'         => ['width="24" height="24" viewBox="0 0 24 24"', null],
			'own width'        => ['width="24" viewBox="0 0 48 24"', null],
			'own height in em' => ['height="2em" viewBox="0 0 48 24"', null],
			'no size info'     => ['', 1.0],
		];
	}

	#[DataProvider('ratioProvider')]
	public function testRatio(string $attrs, float|null $expected): void
	{
		$page = new Page(['slug' => 'test']);

		F::write($page->root() . '/test.svg', '<svg xmlns="http://www.w3.org/2000/svg" ' . $attrs . '></svg>');
		$file    = new File(['filename' => 'test.svg', 'parent' => $page]);
		$preview = new ImageFilePreview($file);
		$this->assertSame($expected, $preview->ratio());
	}

	public function testRatioForRaster(): void
	{
		$page    = new Page(['slug' => 'test']);
		$file    = new File(['filename' => 'test.jpg', 'parent' => $page]);
		$preview = new ImageFilePreview($file);
		$this->assertNull($preview->ratio());
	}
}
