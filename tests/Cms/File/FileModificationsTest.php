<?php

namespace Kirby\Cms;

use Kirby\Content\Field;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Asset;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(File::class)]
class FileModificationsTest extends ModelTestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.FileModifications';

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
}
