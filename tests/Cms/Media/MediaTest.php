<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;
use PHPUnit\Framework\TestCase;

class MediaTest extends TestCase
{
    protected $app;
    protected $fixtures;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => $this->fixtures = __DIR__ . '/fixtures/MediaTest',
            ],
        ]);

        Dir::make($this->fixtures);
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
    }

    public function testLinkSiteFile()
    {
        F::write($this->fixtures . '/content/test.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>');

        $file   = $this->app->file('test.svg');
        $result = Media::link($this->app->site(), $file->mediaHash(), $file->filename());

        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals(200, $result->code());
        $this->assertEquals('image/svg+xml', $result->type());
    }

    public function testLinkPageFile()
    {
        F::write($this->fixtures . '/content/projects/test.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>');

        $file   = $this->app->file('projects/test.svg');
        $result = Media::link($this->app->page('projects'), $file->mediaHash(), $file->filename());

        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals(200, $result->code());
        $this->assertEquals('image/svg+xml', $result->type());
    }

    public function testLinkWithInvalidHash()
    {
        F::write($this->fixtures . '/content/projects/test.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>');

        // with the correct media token
        $file   = $this->app->file('projects/test.svg');
        $result = Media::link($this->app->page('projects'), $file->mediaToken() . '-12345', $file->filename());

        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals(307, $result->code());

        // with a completely invalid hash
        $file   = $this->app->file('projects/test.svg');
        $result = Media::link($this->app->page('projects'), 'abcde-12345', $file->filename());

        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals(404, $result->code());
    }

    public function testLinkWithoutModel()
    {
        $this->assertFalse(Media::link(null, 'hash', 'filename.jpg'));
    }

    public function testPublish()
    {
        F::write($src = $this->fixtures . '/content/test.jpg', 'nice jpg');
        $file = new File([
            'kirby'    => $this->app,
            'filename' => $filename = 'test.jpg'
        ]);

        $oldToken  = crc32($filename);
        $newToken  = $file->mediaToken();
        $directory = $this->fixtures . '/media/site';

        Dir::make($versionA1 = $directory . '/' . $oldToken . '-1234');
        Dir::make($versionA2 = $directory . '/' . $oldToken . '-5678');
        Dir::make($versionB1 = $directory . '/' . $newToken . '-1234');
        Dir::make($versionB2 = $directory . '/' . $newToken . '-5678');

        $this->assertTrue(Media::publish($file, $dest = $versionB2 . '/test.jpg'));

        // the file should be copied
        $this->assertTrue(is_dir($versionB2));
        $this->assertTrue(is_file($dest));

        // older versions should be removed
        $this->assertFalse(is_dir($versionA1));
        $this->assertFalse(is_dir($versionA2));
        $this->assertFalse(is_dir($versionB1));
    }

    public function testUnpublish()
    {
        F::write($src = $this->fixtures . '/content/test.jpg', 'nice jpg');
        $file = new File([
            'kirby'    => $this->app,
            'filename' => $filename = 'test.jpg'
        ]);

        $oldToken  = crc32($filename);
        $newToken  = $file->mediaToken();
        $directory = $this->fixtures . '/media/site';

        Dir::make($versionA1 = $directory . '/' . $oldToken . '-1234');
        Dir::make($versionA2 = $directory . '/' . $oldToken . '-5678');
        Dir::make($versionB1 = $directory . '/' . $newToken . '-1234');
        Dir::make($versionB2 = $directory . '/' . $newToken . '-5678');

        $this->assertTrue(is_dir($versionA1));
        $this->assertTrue(is_dir($versionA2));
        $this->assertTrue(is_dir($versionB1));
        $this->assertTrue(is_dir($versionB2));

        Media::unpublish($directory, $file);

        $this->assertFalse(is_dir($versionA1));
        $this->assertFalse(is_dir($versionA2));
        $this->assertFalse(is_dir($versionB1));
        $this->assertFalse(is_dir($versionB2));
    }

    public function testUnpublishAndIgnore()
    {
        F::write($src = $this->fixtures . '/content/test.jpg', 'nice jpg');
        $file = new File([
            'kirby'    => $this->app,
            'filename' => $filename = 'test.jpg'
        ]);

        $oldToken  = crc32($filename);
        $newToken  = $file->mediaToken();
        $directory = $this->fixtures . '/media/site';

        Dir::make($versionA1 = $directory . '/' . $oldToken . '-1234');
        Dir::make($versionA2 = $directory . '/' . $oldToken . '-5678');
        Dir::make($versionB1 = $directory . '/' . $newToken . '-1234');
        Dir::make($versionB2 = $directory . '/' . $newToken . '-5678');

        $this->assertTrue(is_dir($versionA1));
        $this->assertTrue(is_dir($versionA2));
        $this->assertTrue(is_dir($versionB1));
        $this->assertTrue(is_dir($versionB2));

        Media::unpublish($directory, $file, $versionB1);

        $this->assertTrue(is_dir($versionB1));
        $this->assertFalse(is_dir($versionA1));
        $this->assertFalse(is_dir($versionA2));
        $this->assertFalse(is_dir($versionB2));
    }

    public function testUnpublishNonExistingDirectory()
    {
        $directory = $this->fixtures . '/does-not-exist';

        $file = new File([
            'kirby'    => $this->app,
            'filename' => 'does-not-exist.jpg'
        ]);

        $this->assertTrue(Media::unpublish($directory, $file));
    }
}
