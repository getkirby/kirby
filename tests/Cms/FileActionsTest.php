<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Image\Image;

class ReplaceableTestFile extends File
{
    public function mime()
    {
        return 'text/plain';
    }
}

class FileActionsTestStore extends FileStoreDefault
{

    public static $exists = true;

    public function create(Upload $upload)
    {
        return $this->file();
    }

    public function delete(): bool
    {
        static::$exists = false;
        return true;
    }

    public function exists(): bool
    {
        return static::$exists;
    }

}


class FileActionsTest extends TestCase
{

    protected function pageFile()
    {
        $parent = new Page([
            'slug'  => 'test',
            'store' => PageStoreDefault::class
        ]);

        $file = new File([
            'filename' => 'test.jpg',
            'parent'   => $parent,
            'store'    => FileActionsTestStore::class
        ]);

        return $file;
    }

    protected function siteFile()
    {
        $parent = new Site();
        $file   = new File([
            'filename' => 'test.jpg',
            'parent'   => $parent,
            'store'    => FileActionsTestStore::class
        ]);

        return $file;
    }

    public function fileProvider()
    {
        return [
            [$this->pageFile()],
            [$this->siteFile()]
        ];
    }

    /**
     * @dataProvider fileProvider
     */
    public function testChangeName(File $file)
    {
        $this->assertHooks([
            'file.changeName:before' => function (File $file, string $name) {
                $this->assertEquals('awesome', $name);
            },
            'file.changeName:after' => function (File $newFile, File $oldFile) {
                $this->assertEquals('awesome', $newFile->name());
                $this->assertEquals('test', $oldFile->name());
            }
        ], function () use ($file) {
            $result = $file->changeName('awesome');
            $this->assertEquals('awesome.jpg', $result->filename());
        });
    }

    public function testCreate()
    {
        FileActionsTestStore::$exists = false;

        $parent = new Page([
            'slug'  => 'test',
            'store' => PageStoreDefault::class
        ]);

        $this->assertHooks([
            'file.create:before' => function (File $file, Upload $upload) use ($parent) {
                $this->assertEquals('test.js', $upload->filename());
                $this->assertEquals($parent, $file->parent());
            },
            'file.create:after' => function (File $file) use ($parent) {
                $this->assertEquals('test.js', $file->filename());
                $this->assertEquals($parent, $file->parent());
            }
        ], function () use ($parent) {
            $result = File::create([
                'source' => __DIR__ . '/fixtures/files/test.js',
                'parent' => $parent,
                'store'  => FileActionsTestStore::class
            ]);

            $this->assertEquals('test.js', $result->filename());
            $this->assertEquals($parent, $result->parent());
        });

    }

    /**
     * @dataProvider fileProvider
     */
    public function testDelete($file)
    {
        FileActionsTestStore::$exists = true;

        $this->assertHooks([
            'file.delete:before' => function (File $file) {
                $this->assertTrue($file->exists());
            },
            'file.delete:after' => function (bool $result, File $file) {
                $this->assertFalse($file->exists());
                $this->assertTrue($result);
            }
        ], function () use ($file) {
            $file->delete();
        });
    }

    public function testReplace()
    {
        $file = new ReplaceableTestFile([
            'filename' => 'yay.js',
            'store'    => FileActionsTestStore::class
        ]);

        $this->assertHooks([
            'file.replace:before' => function (File $file, Upload $upload) {
                $this->assertEquals('test.js', $upload->filename());
            },
            'file.replace:after' => function (File $newFile, File $oldFile) {
            }
        ], function () use ($file) {
            $file->replace(__DIR__ . '/fixtures/files/test.js');
        });
    }

}
