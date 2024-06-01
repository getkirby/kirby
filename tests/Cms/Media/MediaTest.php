<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;

class MediaTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';
	public const TMP      = KIRBY_TMP_DIR . '/Cms.Media';

	public function testLinkSiteFile()
	{
		F::write(static::TMP . '/content/test.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>');

		$file   = $this->app->file('test.svg');
		$result = Media::link($this->app->site(), $file->mediaHash(), $file->filename());

		$this->assertInstanceOf(Response::class, $result);
		$this->assertSame(200, $result->code());
		$this->assertSame('image/svg+xml', $result->type());
	}

	public function testLinkPageFile()
	{
		F::write(static::TMP . '/content/projects/test.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>');

		$file   = $this->app->file('projects/test.svg');
		$result = Media::link($this->app->page('projects'), $file->mediaHash(), $file->filename());

		$this->assertInstanceOf(Response::class, $result);
		$this->assertSame(200, $result->code());
		$this->assertSame('image/svg+xml', $result->type());
	}

	public function testLinkWithInvalidHash()
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

	public function testLinkWithoutModel()
	{
		$this->assertFalse(Media::link(null, 'hash', 'filename.jpg'));
	}

	public function testLinkNonExistingFile()
	{
		$this->assertFalse(Media::link($this->app->site(), 'hash', 'filename.jpg'));
	}

	public function testPublish()
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

	public function testUnpublish()
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

	public function testUnpublishAndIgnore()
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

	public function testUnpublishNonExistingDirectory()
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

	public function testThumb()
	{
		Dir::make(static::TMP . '/content');

		// copy test image to content
		F::copy(static::FIXTURES . '/files/test.jpg', static::TMP . '/content/test.jpg');

		// get file object
		$file  = $this->app->file('test.jpg');
		Dir::make(dirname($file->mediaRoot()));
		$this->assertIsFile($file);

		// create job file
		$jobString = '{"width":64,"height":64,"quality":null,"crop":"center","filename":"test.jpg"}';
		F::write(dirname($file->mediaRoot()) . '/.jobs/' . $file->filename() . '.json', $jobString);

		// copy to media folder
		$file->asset()->copy($mediaPath = $file->mediaRoot());

		$thumb = Media::thumb($file, $file->mediaHash(), $file->filename());
		$this->assertInstanceOf(Response::class, $thumb);
		$this->assertNotFalse($thumb->body());
		$this->assertSame(200, $thumb->code());
		$this->assertSame('image/jpeg', $thumb->type());

		$thumbInfo = getimagesize($mediaPath);
		$this->assertSame(64, $thumbInfo[0]);
		$this->assertSame(64, $thumbInfo[1]);
	}

	public function testThumbWithoutJobsFile()
	{
		Dir::make(static::TMP . '/content');

		// copy test image to content
		F::copy(static::FIXTURES . '/files/test.jpg', static::TMP . '/content/test.jpg');

		// get file object
		$file = $this->app->file('test.jpg');

		$this->expectException(\Kirby\Exception\NotFoundException::class);
		$this->expectExceptionMessage('The thumbnail configuration could not be found');

		// invalid with no job file
		$thumb = Media::thumb($file, $file->mediaHash(), $file->filename());
		$this->assertFalse($thumb);
	}

	public function testThumbWithIncompleteJobFile()
	{
		Dir::make(static::TMP . '/content');

		// copy test image to content
		F::copy(static::FIXTURES . '/files/test.jpg', static::TMP . '/content/test.jpg');

		// get file object
		$file = $this->app->file('test.jpg');

		// create an empty job file
		F::write(dirname($file->mediaRoot()) . '/.jobs/' . $file->filename() . '.json', '{}');

		$this->expectException(\Kirby\Exception\InvalidArgumentException::class);
		$this->expectExceptionMessage('Incomplete thumbnail configuration');

		$thumb = Media::thumb($file, $file->mediaHash(), $file->filename());
		$this->assertFalse($thumb);
	}

	public function testThumbWhenGenerationFails()
	{
		Dir::make(static::TMP . '/content');

		// copy test image to content
		F::copy(static::FIXTURES . '/files/test.jpg', static::TMP . '/content/test.jpg');

		// get file object
		$file = $this->app->file('test.jpg');

		// create a valid job file
		$jobString = '{"width":64,"height":64,"quality":null,"crop":"center","filename":"test.jpg"}';
		F::write(dirname($file->mediaRoot()) . '/.jobs/' . $file->filename() . '.json', $jobString);

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('File not found');

		// but the file cannot be found in the media folder
		Media::thumb($file, $file->mediaHash(), $file->filename());
	}

	public function testThumbStringModel()
	{
		Dir::make(static::TMP . '/content');

		// copy test image to content
		F::copy(static::FIXTURES . '/files/test.jpg', static::TMP . '/content/test.jpg');

		// get file object
		$file  = $this->app->file('test.jpg');
		Dir::make(static::TMP . '/media/assets/site/' . $file->mediaHash());
		$this->assertIsFile($file);

		// create job file
		$jobString = '{"width":64,"height":64,"quality":null,"crop":"center","filename":"test.jpg"}';
		F::write(static::TMP . '/media/assets/site/' . $file->mediaHash() . '/.jobs/' . $file->filename() . '.json', $jobString);

		// copy to media folder
		$file->asset()->copy($mediaPath = static::TMP . '/media/assets/site/' . $file->mediaHash() . '/' . $file->filename());

		$thumb = Media::thumb('site', $file->mediaHash(), $file->filename());
		$this->assertInstanceOf(Response::class, $thumb);
		$this->assertNotFalse($thumb->body());
		$this->assertSame(200, $thumb->code());
		$this->assertSame('image/jpeg', $thumb->type());

		$thumbInfo = getimagesize($mediaPath);
		$this->assertSame(64, $thumbInfo[0]);
		$this->assertSame(64, $thumbInfo[1]);
	}
}
