<?php

namespace Kirby\Cms;

use Darkroom\Plan;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class FileManipulateTest extends ModelTestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures/files';
	public const string TMP = KIRBY_TMP_DIR . '/Cms.FileManipulate';

	public function testManipulate(): void
	{
		$parent       = new Page(['slug' => 'test']);
		$originalFile = File::create([
			'filename' => 'test.jpg',
			'source'   => static::FIXTURES . '/test.jpg',
			'parent'   => $parent
		]);

		$this->assertSame(128, $originalFile->width());
		$this->assertSame(128, $originalFile->height());

		$replacedFile = $originalFile->manipulate([
			'width' => 100,
			'height' => 100,
		]);

		$this->assertSame($originalFile->root(), $replacedFile->root());
		$this->assertSame(100, $replacedFile->width());
		$this->assertSame(100, $replacedFile->height());
	}

	public function testManipulateGlobalFormat(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'thumbs' => [
					'format' => 'webp'
				]
			]
		]);
		$this->app->impersonate('kirby');

		$parent       = new Page(['slug' => 'test']);
		$originalFile = File::create([
			'filename' => 'test.jpg',
			'source'   => static::FIXTURES . '/test.jpg',
			'parent'   => $parent
		]);

		$replacedFile = $originalFile->manipulate([
			'width'  => 100,
			'height' => 100
		]);

		$this->assertSame('jpg', $replacedFile->extension());
		$this->assertSame('image/jpeg', $replacedFile->mime());
		$this->assertSame(100, $replacedFile->width());
	}

	public function testManipulateGlobalFormatOverride(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'thumbs' => [
					'format' => 'webp'
				]
			]
		]);
		$this->app->impersonate('kirby');

		$parent       = new Page(['slug' => 'test']);
		$originalFile = File::create([
			'filename' => 'test.jpg',
			'source'   => static::FIXTURES . '/test.jpg',
			'parent'   => $parent
		]);

		$replacedFile = $originalFile->manipulate([
			'width'  => 100,
			'height' => 100,
			'format' => 'webp'
		]);

		$this->assertSame('webp', $replacedFile->extension());
		$this->assertSame('image/webp', $replacedFile->mime());
		$this->assertSame(100, $replacedFile->width());
	}

	public function testManipulateGlobalInterlaceAndStrip(): void
	{
		$plans = [];

		$this->app = $this->app->clone([
			'options' => [
				'thumbs' => [
					'drivers'   => [
						new CallbackDriver(function (Plan $plan) use (&$plans) {
							$plans[] = $plan;
						})
					],
					'interlace' => true,
					'strip'     => false
				]
			]
		]);
		$this->app->impersonate('kirby');

		File::create([
			'filename' => 'test.jpg',
			'source'   => static::FIXTURES . '/test.jpg',
			'parent'   => new Page(['slug' => 'test'])
		])->manipulate([
			'width' => 100
		]);

		$this->assertCount(1, $plans);
		$this->assertFalse($plans[0]->interlace);
		$this->assertTrue($plans[0]->strip);
	}

	public function testManipulateGlobalQuality(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'thumbs' => [
					'quality' => 1
				]
			]
		]);
		$this->app->impersonate('kirby');

		$parent = new Page(['slug' => 'test']);

		$default = File::create([
			'filename' => 'default.jpg',
			'source'   => static::FIXTURES . '/test.jpg',
			'parent'   => $parent
		])->manipulate([
			'width' => 100
		]);

		$explicit = File::create([
			'filename' => 'explicit.jpg',
			'source'   => static::FIXTURES . '/test.jpg',
			'parent'   => $parent
		])->manipulate([
			'width'   => 100,
			'quality' => 85
		]);

		$lowQuality = File::create([
			'filename' => 'low-quality.jpg',
			'source'   => static::FIXTURES . '/test.jpg',
			'parent'   => $parent
		])->manipulate([
			'width'   => 100,
			'quality' => 1
		]);

		$this->assertSame($explicit->size(), $default->size());
		$this->assertNotSame($lowQuality->size(), $default->size());
	}

	public function testManipulateInvalidValidFormat(): void
	{
		$parent       = new Page(['slug' => 'test']);
		$originalFile = File::create([
			'filename' => 'test.mp4',
			'source'   => static::FIXTURES . '/test.mp4',
			'parent'   => $parent
		]);

		$replacedFile = $originalFile->manipulate([
			'width'  => 100,
			'height' => 100,
			'format' => 'webp'
		]);

		// proves strictly that both are the same object
		$this->assertSame($originalFile, $replacedFile);
		$this->assertSame('mp4', $replacedFile->extension());
	}

	public function testManipulateNonImage(): void
	{
		$parent       = new Page(['slug' => 'test']);
		$originalFile = File::create([
			'filename' => 'test.mp4',
			'source'   => static::FIXTURES . '/test.mp4',
			'parent'   => $parent
		]);

		$replacedFile = $originalFile->manipulate([
			'width' => 100,
			'height' => 100,
		]);

		// proves strictly that both are the same object
		$this->assertSame($originalFile, $replacedFile);
	}

	public function testManipulateValidFormat(): void
	{
		$parent       = new Page(['slug' => 'test']);
		$originalFile = File::create([
			'filename' => 'test.jpg',
			'source'   => static::FIXTURES . '/test.jpg',
			'parent'   => $parent
		]);

		$this->assertSame(128, $originalFile->width());
		$this->assertSame(128, $originalFile->height());

		$replacedFile = $originalFile->manipulate([
			'width'  => 100,
			'height' => 100,
			'format' => 'webp'
		]);

		$this->assertSame('webp', $replacedFile->extension());
		$this->assertSame(100, $replacedFile->width());
		$this->assertSame(100, $replacedFile->height());
	}
}
