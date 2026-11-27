<?php

namespace Kirby\Cms;

use Darkroom\Plan;
use Darkroom\Source;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(File::class)]
class FileManipulateTest extends ModelTestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures/files';
	public const string GPS = __DIR__ . '/../../Image/fixtures/image/onigiri-adobe-rgb-gps.jpg';
	public const string TMP = KIRBY_TMP_DIR . '/Cms.FileManipulate';

	/**
	 * The decoded pixels, which only re-encoding changes
	 */
	protected function pixels(string $file): string
	{
		ob_start();
		imagepng(imagecreatefromjpeg($file));
		return md5(ob_get_clean());
	}

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

	public function testManipulateCopy(): void
	{
		$file = File::create([
			'filename' => 'test.jpg',
			'source'   => static::GPS,
			'parent'   => new Page(['slug' => 'test'])
		])->manipulate([
			'width' => 2000
		]);

		// not enlarged and not re-encoded, but without GPS
		$this->assertSame(600, $file->width());
		$this->assertSame($this->pixels(static::GPS), $this->pixels($file->root()));
		$this->assertNull($file->exif()->location()->lat());
		$this->assertSame(new Source(static::GPS)->icc(), new Source($file->root())->icc());

		// a quality of its own re-encodes
		$file = File::create([
			'filename' => 'quality.jpg',
			'source'   => static::GPS,
			'parent'   => new Page(['slug' => 'test'])
		])->manipulate([
			'width'   => 2000,
			'quality' => 50
		]);

		$this->assertNotSame($this->pixels(static::GPS), $this->pixels($file->root()));
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

	public function testManipulateStrip(): void
	{
		$file = File::create([
			'filename' => 'test.jpg',
			'source'   => static::GPS,
			'parent'   => new Page(['slug' => 'test'])
		])->manipulate([
			'strip' => true
		]);

		// without its GPS, but with its color profile
		$this->assertNull($file->exif()->location()->lat());
		$this->assertSame(new Source(static::GPS)->icc(), new Source($file->root())->icc());

		// not re-encoded
		$this->assertSame($this->pixels(static::GPS), $this->pixels($file->root()));
	}

	public function testManipulateStripAboveLimit(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'thumbs' => ['limit' => 1000]
			]
		]);
		$this->app->impersonate('kirby');

		$file = File::create([
			'filename' => 'test.jpg',
			'source'   => static::GPS,
			'parent'   => new Page(['slug' => 'test'])
		])->manipulate([
			'width' => 300
		]);

		// too big to resize, but still without GPS
		$this->assertSame(600, $file->width());
		$this->assertNull($file->exif()->location()->lat());
		$this->assertSame($this->pixels(static::GPS), $this->pixels($file->root()));
	}

	public function testManipulateStripCategories(): void
	{
		$file = File::create([
			'filename' => 'test.jpg',
			'source'   => static::GPS,
			'parent'   => new Page(['slug' => 'test'])
		])->manipulate([
			'strip' => ['camera']
		]);

		// keeps the categories it doesn't list
		$this->assertEqualsWithDelta(40.26, $file->exif()->location()->lat(), 0.01);
		$this->assertSame($this->pixels(static::GPS), $this->pixels($file->root()));
	}

	public function testManipulateStripNonImage(): void
	{
		$originalFile = File::create([
			'filename' => 'test.mp4',
			'source'   => static::FIXTURES . '/test.mp4',
			'parent'   => new Page(['slug' => 'test'])
		]);

		$this->assertSame($originalFile, $originalFile->manipulate(['strip' => true]));
	}

	public function testManipulateStripWithoutDriver(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'thumbs' => ['drivers' => []]
			]
		]);
		$this->app->impersonate('kirby');

		$file = File::create([
			'filename' => 'test.jpg',
			'source'   => static::GPS,
			'parent'   => new Page(['slug' => 'test'])
		]);

		// no driver resizes it, but its EXIF tags still go
		$resized = $file->manipulate(['width' => 300]);
		$this->assertSame(600, $resized->width());
		$this->assertNull($resized->exif()->location()->lat());
		$this->assertSame($this->pixels(static::GPS), $this->pixels($resized->root()));

		// unless asked to keep them
		$file = File::create([
			'filename' => 'kept.jpg',
			'source'   => static::GPS,
			'parent'   => new Page(['slug' => 'test'])
		]);

		$kept = $file->manipulate(['width' => 300, 'strip' => false]);
		$this->assertEqualsWithDelta(40.26, $kept->exif()->location()->lat(), 0.01);
	}

	public function testManipulateStripWithResize(): void
	{
		$file = File::create([
			'filename' => 'test.jpg',
			'source'   => static::GPS,
			'parent'   => new Page(['slug' => 'test'])
		]);

		// re-encoded, by default without GPS
		$resized = $file->manipulate(['width' => 300]);
		$this->assertSame(300, $resized->width());
		$this->assertNull($resized->exif()->location()->lat());

		$file = File::create([
			'filename' => 'kept.jpg',
			'source'   => static::GPS,
			'parent'   => new Page(['slug' => 'test'])
		]);

		$resized = $file->manipulate(['width' => 300, 'strip' => ['camera']]);
		$this->assertSame(300, $resized->width());
		$this->assertEqualsWithDelta(40.26, $resized->exif()->location()->lat(), 0.01);
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
