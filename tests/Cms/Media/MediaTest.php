<?php

namespace Kirby\Cms;

use Closure;
use Exception;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Asset;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Http\Response;
use PHPUnit\Framework\Attributes\DataProvider;

class MediaTest extends TestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures';
	public const string TMP      = KIRBY_TMP_DIR . '/Cms.Media';

	public function testLinkNonExistingFile(): void
	{
		$this->assertFalse(Media::link($this->app->site(), 'hash', 'filename.jpg'));
	}

	public function testLinkPageFile(): void
	{
		F::write(static::TMP . '/content/projects/test.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>');

		$file   = $this->app->file('projects/test.svg');
		$result = Media::link($this->app->page('projects'), $file->mediaHash(), $file->filename());

		$this->assertInstanceOf(Response::class, $result);
		$this->assertSame(200, $result->code());
		$this->assertSame('image/svg+xml', $result->type());
	}

	public function testLinkSiteFile(): void
	{
		F::write(static::TMP . '/content/test.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>');

		$file   = $this->app->file('test.svg');
		$result = Media::link($this->app->site(), $file->mediaHash(), $file->filename());

		$this->assertInstanceOf(Response::class, $result);
		$this->assertSame(200, $result->code());
		$this->assertSame('image/svg+xml', $result->type());
	}

	public function testLinkWithInvalidHash(): void
	{
		F::write(static::TMP . '/content/projects/test.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>');

		// with the correct media token
		$file   = $this->app->file('projects/test.svg');
		$result = Media::link($this->app->page('projects'), $file->mediaToken() . '-12345', $file->filename());

		$this->assertInstanceOf(Response::class, $result);
		$this->assertSame(307, $result->code());

		// with a completely invalid hash
		$file   = $this->app->file('projects/test.svg');
		$result = Media::link($this->app->page('projects'), 'abcde-12345', $file->filename());

		$this->assertFalse($result);
	}

	public function testLinkWithoutModel(): void
	{
		$this->assertFalse(Media::link(null, 'hash', 'filename.jpg'));
	}

	public function testPublish(): void
	{
		$site = new Site();

		F::write($src = static::TMP . '/content/test.jpg', 'nice jpg');
		$file = new File([
			'kirby'    => $this->app,
			'parent'   => $site,
			'filename' => $filename = 'test.jpg'
		]);

		$oldToken  = crc32($filename);
		$newToken  = $file->mediaToken();
		$directory = static::TMP . '/media/site';

		Dir::make($versionA1 = $directory . '/' . $oldToken . '-1234');
		Dir::make($versionA2 = $directory . '/' . $oldToken . '-5678');
		Dir::make($versionB1 = $directory . '/' . $newToken . '-1234');
		Dir::make($versionB2 = $directory . '/' . $newToken . '-5678');

		$this->assertTrue(Media::publish($file, $dest = $versionB2 . '/test.jpg'));

		// the file should be copied
		$this->assertDirectoryExists($versionB2);
		$this->assertFileExists($dest);

		// older versions should be removed
		$this->assertDirectoryDoesNotExist($versionA1);
		$this->assertDirectoryDoesNotExist($versionA2);
		$this->assertDirectoryDoesNotExist($versionB1);
	}

	public function testSign(): void
	{
		F::copy(static::FIXTURES . '/files/test.jpg', static::TMP . '/content/test.jpg');

		$file   = $this->app->file('test.jpg');
		$signed = Media::sign($file, 'test-64x64-q85.jpg');
		$other  = Media::sign($file, 'test-32x32-q85.jpg');

		$this->assertMatchesRegularExpression('/^test-64x64-q85-[0-9a-f]{10}\.jpg$/', $signed);
		$this->assertSame($signed, Media::sign($file, 'test-64x64-q85.jpg'));
		$this->assertNotSame(substr($signed, -14), substr($other, -14));
	}

	public function testThumb(): void
	{
		F::copy(static::FIXTURES . '/files/test.jpg', static::TMP . '/content/test.jpg');

		$file    = $this->app->file('test.jpg');
		$version = $file->thumb(['width' => 64, 'height' => 64, 'crop' => 'center']);

		// nothing is made or written until the thumb is asked for
		$this->assertDirectoryDoesNotExist($file->mediaDir());

		$thumb = Media::thumb($this->app->site(), $file->mediaHash(), $version->filename());

		$this->assertInstanceOf(Response::class, $thumb);
		$this->assertSame(200, $thumb->code());
		$this->assertSame('image/jpeg', $thumb->type());

		$size = getimagesize($file->mediaDir() . '/' . $version->filename());
		$this->assertSame([64, 64], [$size[0], $size[1]]);
	}

	public function testThumbFileModel(): void
	{
		F::copy(static::FIXTURES . '/files/test.jpg', static::TMP . '/content/test.jpg');

		$file    = $this->app->file('test.jpg');
		$version = $file->thumb(['width' => 64]);
		$thumb   = Media::thumb($file, $file->mediaHash(), $version->filename());

		$this->assertSame(200, $thumb->code());
	}

	public static function thumbInvalidProvider(): array
	{
		return [
			'forged signature' => [fn (string $name) => preg_replace('/-[0-9a-f]{10}\./', '-0123456789.', $name)],
			'other key'        => [fn (string $name) => str_replace('64x64', '128x128', $name)],
			'other format'     => [fn (string $name) => str_replace('.jpg', '.webp', $name)],
			'no signature'     => [fn (string $name) => preg_replace('/-[0-9a-f]{10}\./', '.', $name)],
			'traversal'        => [fn (string $name) => '../../../../../' . $name],
		];
	}

	#[DataProvider('thumbInvalidProvider')]
	public function testThumbInvalid(Closure $change): void
	{
		F::copy(static::FIXTURES . '/files/test.jpg', static::TMP . '/content/test.jpg');

		$file    = $this->app->file('test.jpg');
		$version = $file->thumb(['width' => 64]);
		$name    = $change($version->filename());

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessageIs('The thumbnail could not be found');

		Media::thumb($this->app->site(), $file->mediaHash(), $name);
	}

	public function testThumbStringModel(): void
	{
		F::copy(static::FIXTURES . '/files/test.jpg', static::TMP . '/content/test.jpg');

		$asset   = new Asset('content/test.jpg');
		$version = $asset->thumb(['width' => 64]);
		$thumb   = Media::thumb('content', $asset->mediaHash(), $version->filename());

		$this->assertSame(200, $thumb->code());
		$this->assertFileExists($asset->mediaDir() . '/' . $version->filename());
	}

	public function testThumbWhenComponentFails(): void
	{
		F::copy(static::FIXTURES . '/files/test.jpg', static::TMP . '/content/test.jpg');

		$app = $this->app->clone([
			'components' => [
				'thumb' => function (App $kirby, string $src, string $dst) {
					// a generator that writes in place
					F::write($dst, 'broken');
					throw new Exception('Generation failed');
				}
			]
		]);

		$file    = $app->file('test.jpg');
		$version = $file->thumb(['width' => 64]);
		$root    = $file->mediaDir() . '/' . $version->filename();

		try {
			Media::thumb($app->site(), $file->mediaHash(), $version->filename());
			$this->fail('The component did not throw');
		} catch (Exception $e) {
			$this->assertSame('Generation failed', $e->getMessage());
		}

		// no broken thumb, the next request tries again
		$this->assertFileDoesNotExist($root);
	}

	public function testThumbWithComponent(): void
	{
		F::copy(static::FIXTURES . '/files/test.jpg', static::TMP . '/content/test.jpg');

		$args = null;
		$app  = $this->app->clone([
			'components' => [
				'thumb' => function (App $kirby, string $src, string $dst, array $options) use (&$args) {
					$args = [$src, $dst, $options];
					F::copy($src, $dst);
					return $dst;
				}
			]
		]);

		$file    = $app->file('test.jpg');
		$version = $file->thumb(['width' => 64]);

		Media::thumb($app->site(), $file->mediaHash(), $version->filename());

		// the options the name's key holds
		$this->assertSame([
			$file->root(),
			$file->mediaDir() . '/' . $version->filename(),
			['quality' => 85, 'interlace' => false, 'strip' => true, 'width' => 64, 'height' => 64]
		], $args);
	}

	public function testThumbWithInvalidKey(): void
	{
		F::copy(static::FIXTURES . '/files/test.jpg', static::TMP . '/content/test.jpg');

		// signed, but no key the library reads
		$file = $this->app->file('test.jpg');
		$name = Media::sign($file, 'test-abc.jpg');

		$this->expectException(NotFoundException::class);

		Media::thumb($this->app->site(), $file->mediaHash(), $name);
	}

	public function testThumbWithOutdatedHash(): void
	{
		F::copy(static::FIXTURES . '/files/test.jpg', static::TMP . '/content/test.jpg');

		$file    = $this->app->file('test.jpg');
		$version = $file->thumb(['width' => 64]);

		$this->expectException(NotFoundException::class);

		Media::thumb($this->app->site(), $file->mediaToken() . '-1', $version->filename());
	}

	public function testThumbWithoutOriginal(): void
	{
		F::copy(static::FIXTURES . '/files/test.jpg', static::TMP . '/content/test.jpg');

		$file    = $this->app->file('test.jpg');
		$version = $file->thumb(['width' => 64]);
		$hash    = $file->mediaHash();

		F::remove($file->root());

		$this->expectException(NotFoundException::class);

		Media::thumb($this->app->site(), $hash, $version->filename());
	}

	public function testUnpublish(): void
	{
		$page = new Page([
			'slug' => 'test'
		]);

		F::write($src = static::TMP . '/content/test.jpg', 'nice jpg');
		$file = new File([
			'kirby'    => $this->app,
			'parent'   => $page,
			'filename' => $filename = 'test.jpg'
		]);

		$oldToken  = crc32($filename);
		$newToken  = $file->mediaToken();
		$directory = static::TMP . '/media/site';

		Dir::make($versionA1 = $directory . '/' . $oldToken . '-1234');
		Dir::make($versionA2 = $directory . '/' . $oldToken . '-5678');
		Dir::make($versionB1 = $directory . '/' . $newToken . '-1234');
		Dir::make($versionB2 = $directory . '/' . $newToken . '-5678');

		$this->assertDirectoryExists($versionA1);
		$this->assertDirectoryExists($versionA2);
		$this->assertDirectoryExists($versionB1);
		$this->assertDirectoryExists($versionB2);

		Media::unpublish($directory, $file);

		$this->assertDirectoryDoesNotExist($versionA1);
		$this->assertDirectoryDoesNotExist($versionA2);
		$this->assertDirectoryDoesNotExist($versionB1);
		$this->assertDirectoryDoesNotExist($versionB2);
	}

	public function testUnpublishAndIgnore(): void
	{
		$page = new Page([
			'slug' => 'test'
		]);

		F::write($src = static::TMP . '/content/test.jpg', 'nice jpg');
		$file = new File([
			'kirby'    => $this->app,
			'parent'   => $page,
			'filename' => $filename = 'test.jpg'
		]);

		$oldToken  = crc32($filename);
		$newToken  = $file->mediaToken();
		$directory = static::TMP . '/media/site';

		Dir::make($versionA1 = $directory . '/' . $oldToken . '-1234');
		Dir::make($versionA2 = $directory . '/' . $oldToken . '-5678');
		Dir::make($versionB1 = $directory . '/' . $newToken . '-1234');
		Dir::make($versionB2 = $directory . '/' . $newToken . '-5678');

		$this->assertDirectoryExists($versionA1);
		$this->assertDirectoryExists($versionA2);
		$this->assertDirectoryExists($versionB1);
		$this->assertDirectoryExists($versionB2);

		Media::unpublish($directory, $file, $versionB1);

		$this->assertDirectoryExists($versionB1);
		$this->assertDirectoryDoesNotExist($versionA1);
		$this->assertDirectoryDoesNotExist($versionA2);
		$this->assertDirectoryDoesNotExist($versionB2);
	}

	public function testUnpublishNonExistingDirectory(): void
	{
		$directory = static::TMP . '/does-not-exist';

		$page = new Page([
			'slug' => 'test'
		]);

		$file = new File([
			'kirby'    => $this->app,
			'parent'   => $page,
			'filename' => 'does-not-exist.jpg'
		]);

		$this->assertTrue(Media::unpublish($directory, $file));
	}
}
