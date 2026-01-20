<?php

namespace Kirby\Cms;

use Kirby\Content\Field;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Asset;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(FileModifications::class)]
class FileModificationsTest extends ModelTestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/files';
	public const TMP = KIRBY_TMP_DIR . '/Cms.FileModifications';

	public function setUp(): void
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

	public function testThumb(): void
	{
		$input = [
			'width'  => 300,
			'height' => 200,
			'focus'  => '20%, 80%'
		];

		$app = $this->app->clone([
			'components' => [
				'file::version' => function ($kirby, $file, $options = []) use ($input) {
					$this->assertSame($input, $options);
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

	public function testThumbWithDefaultPreset(): void
	{
		$app = $this->app->clone([
			'components' => [
				'file::version' => function ($kirby, $file, $options = []) {
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

	public function testThumbWithCustomPreset(): void
	{
		$app = $this->app->clone([
			'components' => [
				'file::version' => function ($kirby, $file, $options = []) {
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

	public function testThumbWithInvalidReturnValue(): void
	{
		$app = $this->app->clone([
			'components' => [
				'file::version' => fn ($kirby, $file, $options = []) => 'image'
			]
		]);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The file::version component must return a File, FileVersion or Asset object');

		$file = $app->file('test.jpg');
		$file->thumb(['width' => 100]);
	}

	public function testThumbWithFormatOption(): void
	{
		$app = $this->app->clone([
			'components' => [
				'file::version' => function ($kirby, $file, $options = []) {
					$this->assertSame('webp', $options['format']);
					return $file;
				}
			],
			'options' => [
				'thumbs.format' => 'webp'
			]
		]);

		$file = $app->file('test.jpg');
		$file->thumb(['width' => 100]);
	}

	public function testThumbWithFocusFromContent(): void
	{
		$app = $this->app->clone([
			'components' => [
				'file::version' => function ($kirby, $file, $options = []) {
					$this->assertSame('70%, 30%', $options['crop']);
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

	public function testThumbWithNoOptions(): void
	{
		$file = $this->app->file('test.jpg');
		$this->assertIsFile($file, $file->thumb([]));
	}

	public function testBlur(): void
	{
		$app = $this->app->clone([
			'components' => [
				'file::version' => function ($kirby, $file, $options = []) {
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
				'file::version' => function ($kirby, $file, $options = []) {
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
				[
					'width' => 300,
					'height' => null,
					'quality' => null,
					'crop' => 'center'
				]
			],
			[
				[300, 200],
				[
					'width' => 300,
					'height' => 200,
					'quality' => null,
					'crop' => 'center'
				]
			],
			[
				[300, 200, 10],
				[
					'width' => 300,
					'height' => 200,
					'quality' => 10,
					'crop' => 'center'
				]
			],
			[
				[300, 200, $field],
				[
					'width' => 300,
					'height' => 200,
					'quality' => null,
					'crop' => 'top left'
				]
			],
			[
				[300, 200, 'top left'],
				[
					'width' => 300,
					'height' => 200,
					'quality' => null,
					'crop' => 'top left'
				]
			],
			[
				[300, 200, ['crop' => 'top left', 'quality' => 20]],
				[
					'width' => 300,
					'height' => 200,
					'quality' => 20,
					'crop' => 'top left'
				]
			],
		];
	}

	#[DataProvider('cropOptionsProvider')]
	public function testCrop($args, $expected): void
	{
		$app = $this->app->clone([
			'components' => [
				'file::version' => function ($kirby, $file, $options = []) use ($expected) {
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
				'file::version' => function ($kirby, $file, $options = []) {
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
				'file::version' => function ($kirby, $file, $options = []) {
					$this->assertSame([
						'width' => 100,
						'height' => 200,
						'quality' => 10
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
				'file::version' => function ($kirby, $file, $options = []) {
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
				'file::version' => function ($kirby, $file, $options = []) {
					$this->assertSame(['sharpen' => 20], $options);
					return $file;
				}
			]
		]);

		$file = $app->file('test.jpg');
		$file->sharpen(20);
	}

	public function testSrcsetEmpty(): void
	{
		$file = $this->app->file('test.jpg');
		$this->assertNull($file->srcset());
		$this->assertNull($file->srcset([]));
	}

	public function testSrcsetWithNumericWidths(): void
	{
		// test.jpg in fixtures is 128x128
		$page = new Page([
			'root' => static::FIXTURES,
			'slug' => 'files'
		]);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page
		]);

		$srcset = $file->srcset([50, 100]);

		// both widths should be generated with actual dimensions
		$this->assertStringContainsString(' 50w', $srcset);
		$this->assertStringContainsString(' 100w', $srcset);
	}

	public function testSrcsetWithWidthLargerThanOriginal(): void
	{
		// test.jpg in fixtures is 128x128
		$page = new Page([
			'root' => static::FIXTURES,
			'slug' => 'files'
		]);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page
		]);

		$srcset = $file->srcset([200, 300]);

		// both requested widths > 128, so both result in 128w
		// deduplication should keep only one entry
		$this->assertStringContainsString(' 128w', $srcset);
		// there should be only one entry
		$this->assertSame(1, substr_count($srcset, 'w'));
	}

	public function testSrcsetDeduplication(): void
	{
		// test.jpg in fixtures is 128x128
		$page = new Page([
			'root' => static::FIXTURES,
			'slug' => 'files'
		]);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page
		]);

		$srcset = $file->srcset([50, 128, 200, 300]);

		// 50 -> 50w, 128/200/300 all -> 128w (deduplicated)
		$this->assertStringContainsString(' 50w', $srcset);
		$this->assertStringContainsString(' 128w', $srcset);
		// there should be exactly two entries
		$this->assertSame(2, substr_count($srcset, 'w'));
	}

	public function testSrcsetWithPixelDensityDescriptors(): void
	{
		// test.jpg in fixtures is 128x128
		$page = new Page([
			'root' => static::FIXTURES,
			'slug' => 'files'
		]);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page
		]);

		// pixel density descriptors should be preserved as-is
		$srcset = $file->srcset([50 => '1x', 100 => '2x']);

		$this->assertStringContainsString(' 1x', $srcset);
		$this->assertStringContainsString(' 2x', $srcset);
		// pixel density descriptors are not deduplicated by width
		$this->assertSame(2, substr_count($srcset, 'x,') + 1);
	}

	public function testSrcsetWithArrayOptions(): void
	{
		// test.jpg in fixtures is 128x128
		$page = new Page([
			'root' => static::FIXTURES,
			'slug' => 'files'
		]);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page
		]);

		// with explicit width descriptors in keys, should use actual widths
		$srcset = $file->srcset([
			'200w' => ['width' => 200],
			'400w' => ['width' => 400]
		]);

		// both > 128, so both result in 128w (deduplicated to one entry)
		$this->assertStringContainsString(' 128w', $srcset);
		$this->assertSame(1, substr_count($srcset, 'w'));
	}

	public function testSrcsetFromConfig(): void
	{
		// test.jpg in fixtures is 128x128
		$app = $this->app->clone([
			'options' => [
				'thumbs' => [
					'srcsets' => [
						'default' => [50, 100],
						'custom'  => [64, 128]
					]
				]
			]
		]);

		$page = new Page([
			'root'  => static::FIXTURES,
			'slug'  => 'files',
			'kirby' => $app
		]);

		$file = new File([
			'filename' => 'test.jpg',
			'parent'   => $page
		]);

		// default srcset
		$srcset = $file->srcset();
		$this->assertStringContainsString(' 50w', $srcset);
		$this->assertStringContainsString(' 100w', $srcset);

		// named srcset
		$srcset = $file->srcset('custom');
		$this->assertStringContainsString(' 64w', $srcset);
		$this->assertStringContainsString(' 128w', $srcset);
	}
}
