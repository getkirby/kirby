<?php

namespace Kirby\Cms;

use Kirby\Content\Field;
use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Asset;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(File::class)]
class FileModificationsTest extends ModelTestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures/files';
	public const string TMP      = KIRBY_TMP_DIR . '/Cms.FileModifications';

	protected function setUp(): void
	{
		parent::setUp();

		$this->app = $this->app->clone([
			'site' => [
				'files' => [
					['filename' => 'test.jpg']
				]
			]
		]);
	}

	/**
	 * A real 500×500 JPEG in the site's content folder
	 */
	protected function cat(array $options = []): File
	{
		F::copy(static::FIXTURES . '/cat.jpg', static::TMP . '/content/cat.jpg');

		$app = $this->app->clone([
			'options' => $options,
			'site'    => [
				'files' => [
					['filename' => 'cat.jpg']
				]
			]
		]);

		return $app->file('cat.jpg');
	}

	public function testBlur(): void
	{
		$app = $this->app->clone([
			'components' => [
				'file::version' => function ($kirby, $file, array $options) {
					$this->assertSame(['blur' => 5], $options);
					return $file;
				}
			]
		]);

		$file = $app->file('test.jpg');
		$file->blur(5);
	}

	public function testBw(): void
	{
		$app = $this->app->clone([
			'components' => [
				'file::version' => function ($kirby, $file, array $options) {
					$this->assertSame(['grayscale' => true], $options);
					return $file;
				}
			]
		]);

		$file = $app->file('test.jpg');
		$file->bw();
	}

	public static function cropOptionsProvider(): array
	{
		$field = new Field(null, 'crop', 'top left');

		return [
			[
				[300],
				['width' => 300, 'crop' => 'center']
			],
			[
				[300, 200],
				['width' => 300, 'height' => 200, 'crop' => 'center']
			],
			[
				[300, 200, 10],
				['quality' => 10, 'width' => 300, 'height' => 200, 'crop' => 'center']
			],
			[
				[300, 200, $field],
				['width' => 300, 'height' => 200, 'crop' => 'top left']
			],
			[
				[300, 200, 'top left'],
				['width' => 300, 'height' => 200, 'crop' => 'top left']
			],
			[
				[300, 200, ['crop' => 'top left', 'quality' => 20]],
				['quality' => 20, 'width' => 300, 'height' => 200, 'crop' => 'top left']
			],
		];
	}

	#[DataProvider('cropOptionsProvider')]
	public function testCrop($args, $expected): void
	{
		$app = $this->app->clone([
			'components' => [
				'file::version' => function ($kirby, $file, array $options) use ($expected) {
					$this->assertSame($expected, $options);
					return $file;
				}
			]
		]);

		$file = $app->file('test.jpg');
		$file->crop(...$args);
	}

	public function testQuality(): void
	{
		$app = $this->app->clone([
			'components' => [
				'file::version' => function ($kirby, $file, array $options) {
					$this->assertSame(['quality' => 10], $options);
					return $file;
				}
			]
		]);

		$file = $app->file('test.jpg');
		$file->quality(10);
	}

	public function testResize(): void
	{
		$app = $this->app->clone([
			'components' => [
				'file::version' => function ($kirby, $file, array $options) {
					$this->assertSame([
						'quality' => 10,
						'width'   => 100,
						'height'  => 200
					], $options);
					return $file;
				}
			]
		]);

		$file = $app->file('test.jpg');
		$file->resize(100, 200, 10);
	}

	public function testSharpen(): void
	{
		$app = $this->app->clone([
			'components' => [
				'file::version' => function ($kirby, $file, array $options) {
					$this->assertSame(['sharpen' => 50], $options);
					return $file;
				}
			]
		]);

		$file = $app->file('test.jpg');
		$file->sharpen();
	}

	public function testSharpenWithCustomValue(): void
	{
		$app = $this->app->clone([
			'components' => [
				'file::version' => function ($kirby, $file, array $options) {
					$this->assertSame(['sharpen' => 20], $options);
					return $file;
				}
			]
		]);

		$file = $app->file('test.jpg');
		$file->sharpen(20);
	}

	public function testSrcset(): void
	{
		$file = $this->cat();

		$this->assertSame(
			$file->resize(300)->url() . ' 300w, ' . $file->resize(400)->url() . ' 400w',
			$file->srcset([300, 400])
		);
	}

	public function testSrcsetWithConditions(): void
	{
		$file = $this->cat();

		$this->assertSame(
			$file->resize(100)->url() . ' 1x, ' . $file->resize(200)->url() . ' 2x',
			$file->srcset([100 => '1x', 200 => '2x'])
		);
	}

	public function testSrcsetWithPreset(): void
	{
		$file = $this->cat([
			'thumbs' => [
				'srcsets' => [
					'default' => [100],
					'hero'    => [200]
				]
			]
		]);

		$this->assertSame($file->resize(100)->url() . ' 100w', $file->srcset());
		$this->assertSame($file->resize(200)->url() . ' 200w', $file->srcset('hero'));
		$this->assertNull($file->srcset('missing'));
	}

	public function testThumb(): void
	{
		$input = [
			'width'  => 300,
			'height' => 200,
			'focus'  => '20%, 80%'
		];

		$app = $this->app->clone([
			'components' => [
				'file::version' => function ($kirby, $file, array $options) {
					// unknown keys stay, e.g. for plugins
					$this->assertSame([
						'width'  => 300,
						'height' => 200,
						'focus'  => '20%, 80%'
					], $options);
					return $file;
				}
			],
			'site' => [
				'files' => [
					[
						'filename' => 'test.jpg',
						'content' => ['focus' => '70%, 30%']
					]
				]
			]
		]);

		$file = $app->file('test.jpg');
		$file->thumb($input);
	}

	public function testThumbWithAssetObject(): void
	{
		$app = $this->app->clone();
		$asset = new Asset('');
		$result = $asset->thumb([
			'crop' => true
		]);

		$this->assertInstanceOf(Asset::class, $result);
	}

	public function testThumbWithCustomPreset(): void
	{
		$app = $this->app->clone([
			'components' => [
				'file::version' => function ($kirby, $file, array $options) {
					$expected = [
						'width' => 300
					];

					$this->assertSame($expected, $options);
					return $file;
				}
			],
			'options' => [
				'thumbs' => [
					'presets' => [
						'test' => ['width' => 300]
					]
				]
			]
		]);

		$file = $app->file('test.jpg');
		$file->thumb('test');
	}

	public function testThumbWithDefaultPreset(): void
	{
		$app = $this->app->clone([
			'components' => [
				'file::version' => function ($kirby, $file, array $options) {
					$expected = [
						'width' => 300
					];

					$this->assertSame($expected, $options);
					return $file;
				}
			],
			'options' => [
				'thumbs' => [
					'presets' => [
						'default' => ['width' => 300]
					]
				]
			]
		]);

		$file = $app->file('test.jpg');
		$file->thumb();
		$file->thumb('default');
	}

	public function testThumbWithFocusFromContent(): void
	{
		$app = $this->app->clone([
			'components' => [
				'file::version' => function ($kirby, $file, array $options) {
					// as the library writes it
					$this->assertSame('70% 30%', $options['crop']);
					return $file;
				}
			],
			'site' => [
				'files' => [
					[
						'filename' => 'test.jpg',
						'content' => ['focus' => '70%, 30%']
					]
				]
			]
		]);

		$file = $app->file('test.jpg');
		$file->thumb(['width' => 100, 'crop' => true]);
	}

	public function testThumbWithInvalidFocusFromContent(): void
	{
		$app = $this->app->clone([
			'components' => [
				'file::version' => function ($kirby, $file, array $options) {
					$this->assertSame('center', $options['crop']);
					return $file;
				}
			],
			'site' => [
				'files' => [
					[
						'filename' => 'test.jpg',
						'content'  => ['focus' => 'somewhere']
					]
				]
			]
		]);

		$file = $app->file('test.jpg');
		$file->thumb(['width' => 100, 'crop' => true]);
	}

	public function testThumbWithInvalidOption(): void
	{
		$file = $this->app->file('test.jpg');

		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessageIs('Invalid focus: middle');

		$file->thumb(['width' => 100, 'crop' => 'middle']);
	}

	public function testThumbWithInvalidReturnValue(): void
	{
		$app = $this->app->clone([
			'components' => [
				'file::version' => fn ($kirby, $file, $options) => 'image'
			]
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessageIs('The file::version component must return a File, FileVersion or Asset object');

		$file = $app->file('test.jpg');
		$file->thumb(['width' => 100]);
	}

	public function testThumbWithNoOptions(): void
	{
		$file = $this->app->file('test.jpg');
		$this->assertIsFile($file, $file->thumb([]));
	}

	public function testVersion(): void
	{
		$file    = $this->cat();
		$version = $file->crop(300, 200, ['crop' => 'top left', 'quality' => 80]);

		// named by the plan's key
		$this->assertInstanceOf(FileVersion::class, $version);
		$this->assertSame($file->mediaUrl('cat-300x200-crop0-0-500-333-q80.jpg'), $version->url());
		$this->assertSame(300, $version->width());
		$this->assertSame(200, $version->height());

		// a job for the media route, nothing made yet
		$job = $file->mediaDir() . '/.jobs/cat-300x200-crop0-0-500-333-q80.jpg.json';

		$this->assertSame([
			'quality'  => 80,
			'format'   => 'jpg',
			'width'    => 300,
			'height'   => 200,
			'crop'     => 'top left',
			'filename' => 'cat.jpg'
		], Data::read($job));
		$this->assertFalse($version->exists());
	}

	public function testVersionNames(): void
	{
		$file = $this->cat();

		$this->assertStringEndsWith('/cat-500x500-q85.jpg', $file->resize(800)->url());
		$this->assertStringEndsWith('/cat-250x250-q85.jpg', $file->crop(250)->url());
		$this->assertStringEndsWith('/cat-500x500-blur10-grayscale-q85.jpg', $file->thumb(['blur' => true, 'bw' => true])->url());
		$this->assertStringEndsWith('/cat-100x100-sharpen50-q85.webp', $file->thumb(['width' => 100, 'sharpen' => true, 'format' => 'webp'])->url());

		// requests for the same image share it
		$this->assertSame($file->resize(800)->url(), $file->resize(1200)->url());
	}

	public function testVersionReadsHeaderOnce(): void
	{
		$file = $this->cat();
		$file->thumb(['width' => 100]);

		// planned from the header the file read for the first version
		F::remove($file->root());

		$version = $file->thumb(['width' => 50]);
		$this->assertInstanceOf(FileVersion::class, $version);
		$this->assertSame(50, $version->width());
	}

	public function testVersionWithFormatOption(): void
	{
		$file = $this->cat(['thumbs' => ['format' => 'webp']]);

		$this->assertStringEndsWith('/cat-100x100-q85.webp', $file->resize(100)->url());
		$this->assertStringEndsWith('/cat-100x100-q85.png', $file->thumb(['width' => 100, 'format' => 'png'])->url());
	}

	public function testVersionWithoutImage(): void
	{
		// virtual file, no image on disk
		$file = $this->app->file('test.jpg');

		$this->assertSame($file, $file->resize(100));
	}
}
