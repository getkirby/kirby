<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Asset;
use Kirby\Image\Dimensions;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FileVersion::class)]
class FileVersionTest extends ModelTestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures/files';
	public const string TMP      = KIRBY_TMP_DIR . '/Cms.FileVersion';

	protected function original(array $content = []): File
	{
		$page = new Page([
			'root' => static::FIXTURES,
			'slug' => 'files'
		]);

		return new File([
			'filename' => 'test.jpg',
			'parent'   => $page,
			'content'  => $content
		]);
	}

	public function testCall(): void
	{
		$original = $this->original(['title' => 'Test Title']);
		$version  = new FileVersion(
			original: $original,
			options:  ['width' => 64],
			url:      $url = 'https://example.com/test-64x64-q85.jpg'
		);

		// public property access
		$this->assertSame($original, $version->original());

		// file method
		$this->assertSame($url, $version->url());

		// content field via the original File
		$this->assertSame('Test Title', $version->title()->value());

		// fallback to null when original is not a File
		$assetVersion = new FileVersion(
			original: new Asset('test.jpg'),
			url:      'https://example.com/test.jpg'
		);
		$this->assertNull($assetVersion->title());
	}

	public function testConstruct(): void
	{
		$original = $this->original(['title' => 'Test Title']);
		$version  = new FileVersion(
			original: $original,
			options:  ['width' => 200, 'height' => 200],
			url:      $url = 'https://assets.getkirby.com/test-200x200.jpg'
		);

		$this->assertSame($url, $version->url());
		$this->assertSame('Test Title', $version->title()->value());
		$this->assertSame(['format' => 'jpg', 'width' => 200, 'height' => 200], $version->options());
		$this->assertSame($original, $version->original());
		$this->assertSame($original->kirby(), $version->kirby());
	}

	public function testConstructNamedByPlan(): void
	{
		$original = $this->original();

		// in the source's format, as browsers show it
		$version = new FileVersion(
			original: $original,
			options:  ['width' => 64]
		);

		$this->assertSame($original->mediaUrl('test-64x64-q85.jpg'), $version->url());
		$this->assertSame('test-64x64-q85.jpg', $version->filename());
		$this->assertSame(['format' => 'jpg', 'width' => 64], $version->options());

		// a requested format
		$version = new FileVersion(
			original: $original,
			options:  ['width' => 64, 'format' => 'webp']
		);

		$this->assertSame('test-64x64-q85.webp', $version->filename());
		$this->assertSame(['format' => 'webp', 'width' => 64], $version->options());

		// crops never enlarge
		$version = new FileVersion(
			original: $original,
			options:  ['width' => 800, 'height' => 400, 'crop' => true]
		);

		$this->assertSame('test-128x64-crop0-32-128-64-q85.jpg', $version->filename());
		$this->assertEquals(new Dimensions(128, 64), $version->dimensions());

		// nothing is made
		$this->assertFalse($version->exists());
	}

	public function testConstructWithoutPlan(): void
	{
		$version = new FileVersion(
			original: new File([
				'filename' => 'test.pdf',
				'parent'   => $this->original()->parent()
			]),
			options:  ['width' => 64]
		);

		$this->assertNull($version->url());
		$this->assertNull($version->dimensions());
		$this->assertSame(['width' => 64], $version->options());
	}

	public function testDimensions(): void
	{
		$version = new FileVersion(
			original: $this->original(),
			options:  ['width' => 50],
			root:     static::TMP . '/test-50x50.jpg'
		);

		$this->assertEquals(new Dimensions(50, 50), $version->dimensions());
		$this->assertSame(50, $version->width());
		$this->assertSame(50, $version->height());
		$this->assertSame(1.0, $version->ratio());
		$this->assertSame('square', $version->orientation());

		// the size comes from the plan, nothing is made
		$this->assertFalse($version->exists());
	}

	public function testDimensionsWithoutImage(): void
	{
		$version = new FileVersion(
			original: new Asset('test.jpg'),
			url:      'https://example.com/test.jpg'
		);

		$this->assertNull($version->dimensions());
	}

	public function testExists(): void
	{
		$version = new FileVersion(
			original: $this->original(),
			options:  ['width' => 50],
			root:     static::TMP . '/test-50x50.jpg'
		);

		$this->assertFalse($version->exists());

		// a method that needs the file makes it
		$this->assertSame('image/jpeg', $version->mime());

		$this->assertTrue($version->exists());
		$this->assertSame([50, 50], array_slice(getimagesize($version->root()), 0, 2));
	}

	public function testFilename(): void
	{
		$version = new FileVersion(
			original: $this->original(),
			options:  ['width' => 50]
		);

		$this->assertSame('test-50x50-q85.jpg', $version->filename());
		$this->assertSame('test-50x50-q85', $version->name());
		$this->assertSame('jpg', $version->extension());
		$this->assertSame('image', $version->type());
		$this->assertTrue($version->isViewable());

		// the path is known without making the thumb
		$this->assertFalse($version->exists());
	}

	public function testHtml(): void
	{
		$version = new FileVersion(
			original: $this->original(['alt' => 'Test text']),
			options:  ['width' => 50],
			root:     static::TMP . '/test-50x50.jpg',
			url:      $url = 'https://example.com/test-50x50.jpg'
		);

		$this->assertSame('<img alt="Test text" src="' . $url . '">', $version->html());
		$this->assertSame('<img alt="Test text" src="' . $url . '">', (string)$version);

		// rendering the version does not make it
		$this->assertFalse($version->exists());
	}

	public function testId(): void
	{
		$version = new FileVersion(
			original: $this->original(),
			root:     static::TMP . '/test-50x50.jpg'
		);

		$this->assertSame('files/test-50x50.jpg', $version->id());
		$this->assertFalse($version->exists());
	}

	public function testIsResizable(): void
	{
		$version = new FileVersion(
			original: $this->original(),
			options:  ['width' => 50]
		);

		$this->assertTrue($version->isResizable());
		$this->assertFalse($version->exists());

		$version = new FileVersion(
			original: new File([
				'filename' => 'test.pdf',
				'parent'   => $this->original()->parent()
			]),
			options:  ['width' => 50]
		);

		$this->assertFalse($version->isResizable());
	}

	public function testModifications(): void
	{
		$version = new FileVersion(
			original: $this->original(),
			options:  ['width' => 50]
		);

		$this->assertSame($version->options(), $version->modifications());
	}

	public function testRoot(): void
	{
		$version = new FileVersion(
			original: $this->original(),
			options:  ['width' => 50],
			root:     $root = static::TMP . '/test-50x50.jpg'
		);

		$this->assertFalse($version->exists());

		// accessing the root makes the version
		$this->assertSame($root, $version->root());
		$this->assertTrue($version->exists());
	}

	public function testSave(): void
	{
		// the root would make the version
		$original = $this->original();
		$version  = $original->thumb(['width' => 50]);
		$job     = $original->mediaDir() . '/.jobs/' . basename($version->url()) . '.json';

		$this->assertFileExists($job);

		$version->save();

		$this->assertTrue($version->exists());
		$this->assertFileDoesNotExist($job);
	}

	public function testSaveWithComponent(): void
	{
		$args = null;

		$this->app->clone([
			'components' => [
				'thumb' => function (App $kirby, string $src, string $dst, array $options) use (&$args) {
					$args = [$src, $dst, $options];
					return $dst;
				}
			]
		]);

		$version = new FileVersion(
			original: $original = $this->original(),
			options:  ['width' => 50],
			root:     $root = static::TMP . '/test-50x50.jpg'
		);

		$this->assertSame($version, $version->save());
		$this->assertSame([$original->root(), $root, ['format' => 'jpg', 'width' => 50]], $args);
	}

	public function testToArray(): void
	{
		$version = new FileVersion(
			original: $this->original(['title' => 'Test Title']),
			root:     static::FIXTURES . '/test.jpg'
		);

		$this->assertSame('jpg', $version->toArray()['extension']);
		$this->assertSame(1192, $version->toArray()['size']);
	}

	public function testToString(): void
	{
		$original = $this->original(['alt' => 'Test text']);
		$version  = new FileVersion(
			original: $original,
			root:     static::FIXTURES . '/test.txt',
			url:      $url = 'https://assets.getkirby.com/test-200x200.txt'
		);

		$this->assertSame($url, (string)$version);

		$version  = new FileVersion(
			original: $original,
			root:     static::FIXTURES . '/test.jpg',
			url:      $url = 'https://assets.getkirby.com/test-200x200.jpg'
		);

		$this->assertSame('<img alt="Test text" src="' . $url . '">', (string)$version);
	}
}
