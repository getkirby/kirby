<?php

namespace Kirby\Image;

use Exception;
use Kirby\Image\Darkroom\GdLib;
use Kirby\Image\Darkroom\ImageMagick;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Darkroom::class)]
class DarkroomTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';

	public function file(string|null $driver = null)
	{
		if ($driver !== null) {
			return static::FIXTURES . '/image/cat-' . $driver . '.jpg';
		}

		return static::FIXTURES . '/image/cat.jpg';
	}

	public function testFactory(): void
	{
		$instance = Darkroom::factory('gd');
		$this->assertInstanceOf(GdLib::class, $instance);

		$instance = Darkroom::factory('im');
		$this->assertInstanceOf(ImageMagick::class, $instance);
	}

	public function testFactoryWithInvalidType(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Invalid Darkroom type');

		Darkroom::factory('does-not-exist');
	}

	public function testCropWithoutPosition(): void
	{
		$darkroom = new Darkroom();
		$options  = $darkroom->preprocess($this->file(), [
			'crop'  => true,
			'width' => 100
		]);

		$this->assertSame('center', $options['crop']);
	}

	public function testBlurWithoutPosition(): void
	{
		$darkroom = new Darkroom();
		$options  = $darkroom->preprocess($this->file(), [
			'blur' => true,
		]);

		$this->assertSame(10, $options['blur']);
	}

	public function testQualityWithoutValue(): void
	{
		$darkroom = new Darkroom();
		$options  = $darkroom->preprocess($this->file(), [
			'quality' => null,
		]);

		$this->assertSame(90, $options['quality']);
	}

	public function testSharpenWithoutValue(): void
	{
		$darkroom = new Darkroom();
		$options  = $darkroom->preprocess($this->file(), [
			'sharpen' => true,
			'width'   => 100
		]);

		$this->assertSame(50, $options['sharpen']);
	}

	public function testDefaults(): void
	{
		$darkroom = new Darkroom();
		$options  = $darkroom->preprocess('/dev/null');

		$this->assertFalse($options['crop']);
		$this->assertFalse($options['blur']);
		$this->assertFalse($options['grayscale']);
		$this->assertSame(0, $options['height']);
		$this->assertSame(90, $options['quality']);
		$this->assertSame(0, $options['width']);
	}

	public function testGlobalOptions(): void
	{
		$darkroom = new Darkroom([
			'quality' => 20
		]);

		$options = $darkroom->preprocess($this->file());

		$this->assertSame(20, $options['quality']);
	}

	public function testPassedOptions(): void
	{
		$darkroom = new Darkroom([
			'quality' => 20
		]);

		$options = $darkroom->preprocess($this->file(), [
			'quality' => 30
		]);

		$this->assertSame(30, $options['quality']);
	}

	public function testProcess(): void
	{
		$darkroom = new Darkroom([
			'quality' => 20
		]);

		$options = $darkroom->process($this->file(), [
			'quality' => 30
		]);

		$this->assertSame(30, $options['quality']);
	}

	public function testGrayscaleFixes(): void
	{
		$darkroom = new Darkroom();

		// grayscale
		$options = $darkroom->preprocess($this->file(), [
			'grayscale' => true
		]);

		$this->assertTrue($options['grayscale']);

		// greyscale
		$options = $darkroom->preprocess($this->file(), [
			'greyscale' => true
		]);

		$this->assertTrue($options['grayscale']);
		$this->assertFalse(isset($options['greyscale']));

		// bw
		$options = $darkroom->preprocess($this->file(), [
			'bw' => true
		]);

		$this->assertTrue($options['grayscale']);
		$this->assertFalse(isset($options['bw']));
	}
}
