<?php

namespace Kirby\Cms;

class ResourceTest extends TestCase
{

    public function testProps()
    {
        $resource = new Resource([
            'path'      => 'some/path',
            'src'       => 'some/src.css',
            'type'      => 'css',
            'timestamp' => true
        ]);

        $this->assertEquals('some/path', $resource->path());
        $this->assertEquals('some/src.css', $resource->src());
        $this->assertEquals('css', $resource->type());
        $this->assertEquals(true, $resource->timestamp());
    }

    public function testFilename()
    {
        $resource = new Resource([
            'path' => 'some/path',
            'src'  => 'some/src.css',
            'type' => 'css',
        ]);

        $this->assertEquals('src.css', $resource->filename());
    }

    public function testFilenameSanitization()
    {
        $resource = new Resource([
            'path' => 'some/path',
            'src'  => 'some/Söme Thing.css',
            'type' => 'css',
        ]);

        $this->assertEquals('some-thing.css', $resource->filename());
    }

    public function testFilenameWithTimestamp()
    {
        $resource = new Resource([
            'path'      => 'some/path',
            'src'       => __FILE__,
            'type'      => 'css',
            'timestamp' => true
        ]);

        $this->assertEquals('resourcetest.' . filemtime(__FILE__) . '.php', $resource->filename());
    }

    public function testRedirect()
    {
        $resource = new Resource([
            'path' => 'some/path',
            'src'  => 'some/src.css',
            'type' => 'css',
        ]);

        $this->assertInstanceOf(\Kirby\Http\Response\Redirect::class, $resource->redirect());
    }

    public function testId()
    {
        $resource = new Resource([
            'path' => 'some/path',
            'src'  => 'some/src.css',
            'type' => 'css',
        ]);

        $this->assertEquals('some/path/src.css', $resource->id());
    }

    public function testRoot()
    {
        $kirby = new App([
            'roots' => [
                'media' => '/var/www/media'
            ]
        ]);

        $resource = new Resource([
            'path'  => 'some/path',
            'src'   => 'some/src.css',
            'type'  => 'css',
            'kirby' => $kirby
        ]);

        $this->assertEquals('/var/www/media/some/path/src.css', $resource->root());
    }

    public function testDir()
    {
        $kirby = new App([
            'roots' => [
                'media' => '/var/www/media'
            ]
        ]);

        $resource = new Resource([
            'path'  => 'some/path',
            'src'   => 'some/src.css',
            'type'  => 'css',
            'kirby' => $kirby
        ]);

        $this->assertEquals('/var/www/media/some/path', $resource->dir());
    }

    public function testUrl()
    {
        $kirby = new App([
            'urls' => [
                'media' => 'https://getkirby.com/media'
            ]
        ]);

        $resource = new Resource([
            'path'  => 'some/path',
            'src'   => 'some/src.css',
            'type'  => 'css',
            'kirby' => $kirby
        ]);

        $this->assertEquals('https://getkirby.com/media/some/path/src.css', $resource->url());
    }

    public function testForPageFile()
    {
        $kirby = new App([
            'urls' => [
                'media' => 'https://getkirby.com/media'
            ]
        ]);

        $file = new File([
            'filename' => 'test.jpg',
            'parent'   => new Page(['slug' => 'test']),
            'kirby'    => $kirby
        ]);

        $resource = Resource::forFile($file);

        $this->assertEquals('https://getkirby.com/media/pages/test/test.jpg', $resource->url());
    }

    public function testForSiteFile()
    {
        $kirby = new App([
            'urls' => [
                'media' => 'https://getkirby.com/media'
            ]
        ]);

        $file = new File([
            'filename' => 'test.jpg',
            'parent'   => new Site(),
            'kirby'    => $kirby
        ]);

        $resource = Resource::forFile($file);

        $this->assertEquals('https://getkirby.com/media/site/test.jpg', $resource->url());
    }

}
