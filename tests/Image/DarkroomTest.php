<?php

namespace Kirby\Image;

use Kirby\TestCase;

class DarkroomTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';

	public function file(string $driver = null)
	{
		if ($driver !== null) {
			return static::FIXTURES . '/image/cat-' . $driver . '.jpg';
		}

		return static::FIXTURES . '/image/cat.jpg';
	}

	public function testFactory()
	{
		$instance = Darkroom::factory('gd');
		$this->assertInstanceOf(Darkroom\GdLib::class, $instance);

		$instance = Darkroom::factory('im');
		$this->assertInstanceOf(Darkroom\ImageMagick::class, $instance);
	}

	public function testFactoryWithInvalidType()
	{
		$this->expectException('Exception');
		$this->expectExceptionMessage('Invalid Darkroom type');

		$instance = Darkroom::factory('does-not-exist');
	}

	public function testCropWithoutPosition()
	{
		$darkroom = new Darkroom();
		$options  = $darkroom->preprocess($this->file(), [
			'crop'  => true,
			'width' => 100
		]);

		$this->assertSame('center', $options['crop']);
	}

	public function testBlurWithoutPosition()
	{
		$darkroom = new Darkroom();
		$options  = $darkroom->preprocess($this->file(), [
			'blur' => true,
		]);

		$this->assertSame(10, $options['blur']);
	}

	public function testQualityWithoutValue()
	{
		$darkroom = new Darkroom();
		$options  = $darkroom->preprocess($this->file(), [
			'quality' => null,
		]);

		$this->assertSame(90, $options['quality']);
	}

	public function testSharpenWithoutValue()
	{
		$darkroom = new Darkroom();
		$options  = $darkroom->preprocess($this->file(), [
			'sharpen' => true,
			'width'   => 100
		]);

		$this->assertSame(50, $options['sharpen']);
	}

	public function testDefaults()
	{
		$darkroom = new Darkroom();
		$options  = $darkroom->preprocess('/dev/null');

		$this->assertTrue($options['autoOrient']);
		$this->assertFalse($options['crop']);
		$this->assertFalse($options['blur']);
		$this->assertFalse($options['grayscale']);
		$this->assertSame(0, $options['height']);
		$this->assertSame(90, $options['quality']);
		$this->assertSame(0, $options['width']);
	}

	public function testGlobalOptions()
	{
		$darkroom = new Darkroom([
			'quality' => 20
		]);

		$options = $darkroom->preprocess($this->file());

		$this->assertSame(20, $options['quality']);
	}

	public function testPassedOptions()
	{
		$darkroom = new Darkroom([
			'quality' => 20
		]);

		$options = $darkroom->preprocess($this->file(), [
			'quality' => 30
		]);

		$this->assertSame(30, $options['quality']);
	}

	public function testProcess()
	{
		$darkroom = new Darkroom([
			'quality' => 20
		]);

		$options = $darkroom->process($this->file(), [
			'quality' => 30
		]);

		$this->assertSame(30, $options['quality']);
	}

	public function testGrayscaleFixes()
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
