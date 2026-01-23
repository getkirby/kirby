<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Http\Response;

class MediaTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';
	public const TMP      = KIRBY_TMP_DIR . '/Cms.Media';

	public function testLinkSiteFile(): void
	{
		F::write(static::TMP . '/content/test.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>');

		$file   = $this->app->file('test.svg');
		$result = Media::link($this->app->site(), $file->mediaHash(), $file->filename());

		$this->assertInstanceOf(Response::class, $result);
		$this->assertSame(200, $result->code());
		$this->assertSame('image/svg+xml', $result->type());
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

	public function testLinkNonExistingFile(): void
	{
		$this->assertFalse(Media::link($this->app->site(), 'hash', 'filename.jpg'));
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

	public function testThumb(): void
	{
		Dir::make(static::TMP . '/content');

		// copy test image to content
		F::copy(static::FIXTURES . '/files/test.jpg', static::TMP . '/content/test.jpg');

		// get file object
		$file  = $this->app->file('test.jpg');
		Dir::make($file->mediaDir());
		$this->assertIsFile($file);

		// create job file
		$jobString = '{"width":64,"height":64,"quality":null,"crop":"center","filename":"test.jpg"}';
		F::write($file->mediaDir() . '/.jobs/' . $file->filename() . '.json', $jobString);

		// copy to media folder
		$file->asset()->copy($mediaRoot = $file->mediaRoot());

		$thumb = Media::thumb($file, $file->mediaHash(), $file->filename());
		$this->assertInstanceOf(Response::class, $thumb);
		$this->assertNotFalse($thumb->body());
		$this->assertSame(200, $thumb->code());
		$this->assertSame('image/jpeg', $thumb->type());

		$thumbInfo = getimagesize($mediaRoot);
		$this->assertSame(64, $thumbInfo[0]);
		$this->assertSame(64, $thumbInfo[1]);
	}

	public function testThumbWithoutJobsFile(): void
	{
		Dir::make(static::TMP . '/content');

		// copy test image to content
		F::copy(static::FIXTURES . '/files/test.jpg', static::TMP . '/content/test.jpg');

		// get file object
		$file = $this->app->file('test.jpg');

		$this->expectException(NotFoundException::class);
		$this->expectExceptionMessage('The thumbnail configuration could not be found');

		// invalid with no job file
		$thumb = Media::thumb($file, $file->mediaHash(), $file->filename());
		$this->assertFalse($thumb);
	}

	public function testThumbWithIncompleteJobFile(): void
	{
		Dir::make(static::TMP . '/content');

		// copy test image to content
		F::copy(static::FIXTURES . '/files/test.jpg', static::TMP . '/content/test.jpg');

		// get file object
		$file = $this->app->file('test.jpg');

		// create an empty job file
		F::write($file->mediaDir() . '/.jobs/' . $file->filename() . '.json', '{}');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Incomplete thumbnail configuration');

		$thumb = Media::thumb($file, $file->mediaHash(), $file->filename());
		$this->assertFalse($thumb);
	}

	public function testThumbWithExistingJobFile(): void
	{
		Dir::make(static::TMP . '/content');

		// copy test image to content
		F::copy(
			static::FIXTURES . '/files/test.jpg',
			static::TMP . '/content/test.jpg'
		);

		// get file object
		$file = $this->app->file('test.jpg');
		$this->assertIsFile($file);

		// create job file with specific marker before calling thumb()
		$dir = $file->mediaDir() . '/.jobs';
		$job = $dir . '/test-64x64-crop.jpg.json';
		$payload = '{"width":64,"height":64,"crop":"center","filename":"test.jpg","custom":"marker"}';

		Dir::make($dir);
		F::write($job, $payload);

		// call thumb() which triggers file::version component
		$file->thumb(['width' => 64, 'height' => 64, 'crop' => 'center']);

		// job file should not have been overwritten
		$this->assertFileExists($job);
		$this->assertSame($payload, F::read($job));
	}

	public function testThumbWhenGenerationFails(): void
	{
		Dir::make(static::TMP . '/content');

		// copy test image to content
		F::copy(static::FIXTURES . '/files/test.jpg', static::TMP . '/content/test.jpg');

		// get file object
		$site = $this->app->site();
		$file = $site->file('test.jpg');

		// make the image mysteriously disappear again
		F::remove(static::TMP . '/content/test.jpg');

		// create a valid job file
		$jobString = '{"width":64,"height":64,"quality":null,"crop":"center","filename":"test.jpg"}';
		F::write($file->mediaDir() . '/.jobs/' . $file->filename() . '.json', $jobString);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('File not found');

		Media::thumb($site, $file->mediaHash(), $file->filename());
	}

	public function testThumbStringModel(): void
	{
		Dir::make(static::TMP . '/content');

		// copy test image to content
		F::copy(static::FIXTURES . '/files/test.jpg', static::TMP . '/content/test.jpg');

		// get file object
		$file  = $this->app->file('test.jpg');
		Dir::make(static::TMP . '/media/assets/content/' . $file->mediaHash());
		$this->assertIsFile($file);

		// create job file
		$jobString = '{"width":64,"height":64,"quality":null,"crop":"center","filename":"test.jpg"}';
		F::write(static::TMP . '/media/assets/content/' . $file->mediaHash() . '/.jobs/' . $file->filename() . '.json', $jobString);

		$thumb = Media::thumb('content', $file->mediaHash(), $file->filename());
		$this->assertInstanceOf(Response::class, $thumb);
		$this->assertNotFalse($thumb->body());
		$this->assertSame(200, $thumb->code());
		$this->assertSame('image/jpeg', $thumb->type());

		$thumbInfo = getimagesize(static::TMP . '/media/assets/content/' . $file->mediaHash() . '/' . $file->filename());
		$this->assertSame(64, $thumbInfo[0]);
		$this->assertSame(64, $thumbInfo[1]);
	}
}
