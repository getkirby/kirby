<?php

namespace Kirby\Filesystem;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Filename::class)]
class FilenameTest extends TestCase
{
	public function testAttributesToArray()
	{
		$name = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
			'width'     => 300,
			'height'    => 200,
			'crop'      => 'top left',
			'grayscale' => true,
			'blur'      => 10,
			'quality'   => 90,
			'sharpen'   => 25,
		]);

		$expected = [
			'dimensions' => '300x200',
			'crop'       => 'top-left',
			'blur'       => 10,
			'bw'         => true,
			'q'          => 90,
			'sharpen'    => 25
		];

		$this->assertSame($expected, $name->attributesToArray());
	}

	public static function attributesToStringProvider(): array
	{
		return [
			[
				'-300x200-crop-top-left-blur10-bw-q90',
				[
					'width'     => 300,
					'height'    => 200,
					'crop'      => 'top left',
					'grayscale' => true,
					'blur'      => 10,
					'quality'   => 90
				]
			],
			[
				'-300x200',
				[
					'width'  => 300,
					'height' => 200,
				]
			],
			[
				'-x200',
				[
					'height' => 200,
				]
			],
			[
				'-crop',
				[
					'crop' => 'center',
				]
			],
			[
				'-sharpen25',
				[
					'sharpen' => 25,
				]
			],
		];
	}

	#[DataProvider('attributesToStringProvider')]
	public function testAttributesToString(string $expected, array $options)
	{
		$name = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', $options);

		$this->assertSame($expected, $name->attributesToString('-'));
	}

	public function testAttributesToStringWithoutAttrs()
	{
		$name = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', []);
		$this->assertSame('', $name->attributesToString());
	}

	public static function blurOptionProvider(): array
	{
		return [
			[false, false],
			[true, 1],
			[90, 90],
			[90.0, 90],
			['90', 90],
		];
	}

	#[DataProvider('blurOptionProvider')]
	public function testBlur(bool|int|float|string $value, bool|int $expected)
	{
		$name = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
			'blur' => $value
		]);

		$this->assertSame($expected, $name->blur());
	}

	public static function cropAnchorProvider(): array
	{
		return [
			['center', 'center'],
			['top', 'top'],
			['bottom', 'bottom'],
			['left', 'left'],
			['right', 'right'],
			['top left', 'top-left'],
			['top right', 'top-right'],
			['bottom left', 'bottom-left'],
			['bottom right', 'bottom-right'],
		];
	}

	#[DataProvider('cropAnchorProvider')]
	public function testCrop(string $anchor, string $expected)
	{
		$name = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
			'crop' => $anchor
		]);

		$this->assertSame($expected, $name->crop());
	}

	public function testCropEmpty()
	{
		$name = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}');
		$this->assertFalse($name->crop());
	}

	public function testCropDisabled()
	{
		$name = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
			'crop' => false
		]);

		$this->assertFalse($name->crop());
	}

	public function testCropCustom()
	{
		$name = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
			'crop' => 'something'
		]);

		$this->assertSame('something', $name->crop());
	}

	public function testDimensions()
	{
		$name = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', $dimensions = [
			'width'  => 300,
			'height' => 200
		]);

		$this->assertSame($dimensions, $name->dimensions());
	}

	public function testDimensionsEmpty()
	{
		$name = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}');

		$this->assertSame([], $name->dimensions());
	}

	public function testDimensionsWithoutWidth()
	{
		$name = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
			'height' => 300
		]);

		$this->assertSame([
			'width'  => null,
			'height' => 300
		], $name->dimensions());
	}

	public function testDimensionsWithoutHeight()
	{
		$name = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
			'width' => 300
		]);

		$this->assertSame([
			'width'  => 300,
			'height' => null
		], $name->dimensions());
	}

	public function testExtension()
	{
		$name = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}');
		$this->assertSame('jpg', $name->extension());
	}

	public function testExtensionUppercase()
	{
		$name = new Filename('/test/some-file.JPG', '{{ name }}.{{ extension }}');
		$this->assertSame('jpg', $name->extension());
	}

	public function testExtensionJpeg()
	{
		$name = new Filename('/test/some-file.jpeg', '{{ name }}.{{ extension }}');
		$this->assertSame('jpg', $name->extension());
	}

	public static function grayscaleOptionProvider(): array
	{
		return [
			['grayscale', true, true],
			['grayscale', false, false],
			['greyscale', true, true],
			['greyscale', false, false],
			['bw', true, true],
			['bw', false, false],
		];
	}

	#[DataProvider('grayscaleOptionProvider')]
	public function testGrayscale(string $prop, bool $value, bool $expected)
	{
		$name = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}', [
			$prop => $value
		]);

		$this->assertSame($expected, $name->grayscale());
	}

	public function testName()
	{
		$name = new Filename('/var/www/some-file.jpg', '{{ name }}.{{ extension }}');
		$this->assertSame('some-file', $name->name());
	}

	public function testNameSanitization()
	{
		$name = new Filename('/var/www/söme file.jpg', '{{ name }}.{{ extension }}');
		$this->assertSame('some-file', $name->name());
	}

	public function testNameSanitizationWithLanguageRules()
	{
		$name = new Filename(
			filename: '/var/www/안녕하세요.pdf',
			template: '{{ name }}.{{ extension }}',
			language: 'ko'
		);

		$this->assertSame('annyeonghaseyo', $name->name());
	}

	public static function qualityOptionProvider(): array
	{
		return [
			[false, false],
			[true, false],
			[90, 90],
			[90.0, 90],
			['90', 90],
		];
	}

	#[DataProvider('qualityOptionProvider')]
	public function testQuality(bool|int|float|string $value, bool|int $expected)
	{
		$name = new Filename('/test/some-file.jpg', 'some-file.jpg', [
			'quality' => $value
		]);

		$this->assertSame($expected, $name->quality());
	}

	#[DataProvider('attributesToStringProvider')]
	public function testToString(string $expected, array $attributes)
	{
		$name = new Filename('/test/some-file.jpg', '{{ name }}{{ attributes }}.{{ extension }}', $attributes);

		$this->assertSame('some-file' . $expected . '.jpg', $name->toString());
		$this->assertSame('some-file' . $expected . '.jpg', (string)$name);
	}

	/**
	 * @ocvers ::__toString
	 */
	public function testToStringWithFalsyAttributes()
	{
		$name = new Filename('/test/some-file.jpg', '{{ name }}{{ attributes }}.{{ extension }}', [
			'width'     => false,
			'height'    => false,
			'crop'      => false,
			'blur'      => false,
			'grayscale' => false,
			'quality'   => false,
			'sharpen'   => false
		]);

		$this->assertSame('some-file.jpg', $name->toString());
		$this->assertSame('some-file.jpg', (string)$name);
	}

	/**
	 * @ocvers ::__toString
	 */
	public function testToStringWithoutAttributes()
	{
		$name = new Filename('/test/some-file.jpg', '{{ name }}.{{ extension }}');
		$this->assertSame('some-file.jpg', $name->toString());
		$this->assertSame('some-file.jpg', (string)$name);
	}
}
