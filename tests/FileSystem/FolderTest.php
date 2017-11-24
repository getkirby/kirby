<?php

namespace Kirby\FileSystem;

class FolderTest extends TestCase
{

    protected function _folder()
    {
        return new Folder(static::FIXTURES . '/folder');
    }

    public function testIgnore()
    {
        $defaultIgnoreArray = [
        '.',
        '..',
        '.DS_Store',
        '.gitignore',
        '.git',
        '.svn',
        '.htaccess',
        'Thumb.db',
        '@eaDir'
        ];

        $this->assertEquals($defaultIgnoreArray, Folder::ignore());

        $customIgnoreArray = [
        '.',
        '..'
        ];

        Folder::ignore($customIgnoreArray);

        $this->assertEquals($customIgnoreArray, Folder::ignore());

        // make sure to reset the defaults for all other tests
        Folder::ignore($defaultIgnoreArray);
    }

    public function testMake()
    {
        $folder = new Folder(static::FIXTURES . '/new');
        $this->assertFalse($folder->exists());
        $this->assertTrue($folder->make());
        $this->assertTrue($folder->exists());
        $this->assertTrue($folder->make());
        rmdir(static::FIXTURES . '/new');
    }

    public function testRoot()
    {
        $folder = $this->_folder();
        $this->assertEquals(static::FIXTURES . '/folder', $folder->root());
    }

    public function testExists()
    {
        $folder = $this->_folder();
        $this->assertTrue($folder->exists());

        $folder = new Folder('does-not-exist');
        $this->assertFalse($folder->exists());
    }

    public function testReadable()
    {
        $folder = $this->_folder();
        $this->assertTrue($folder->isReadable());

        $folder = new Folder('does-not-exist');
        $this->assertFalse($folder->isReadable());
    }

    public function testName()
    {
        $folder = $this->_folder();
        $this->assertEquals('folder', $folder->name());
    }

    public function testFile()
    {
        $folder = $this->_folder();
        $file   = $folder->file('a.js');

        $this->assertInstanceOf('Kirby\\FileSystem\\File', $file);
    }

    public function testFiles()
    {
        $folder = $this->_folder();
        $files  = $folder->files();

        $this->assertCount(2, $files);
    }

    public function testFilesWithNonExistingFolder()
    {
        $folder = new Folder('does-not-exist');
        $this->assertEquals([], $folder->files());
    }

    public function testFolder()
    {
        $folder    = $this->_folder();
        $subfolder = $folder->folder('subfolder');

        $this->assertInstanceOf('Kirby\\FileSystem\\Folder', $subfolder);
    }

    public function testFolders()
    {
        $folder  = $this->_folder();
        $folders = $folder->folders();

        $this->assertCount(1, $folders);
    }

    public function testFoldersWithNonExistingFolder()
    {
        $folder = new Folder('does-not-exist');
        $this->assertEquals([], $folder->folders());
    }

    public function testDelete()
    {
        $folder = new Folder(static::FIXTURES . '/new');
        $folder->make();
        $this->assertTrue($folder->exists());
        $folder->delete();
        $this->assertFalse($folder->exists());
    }

    public function testParent()
    {
        $folder = $this->_folder();
        $parent = $folder->parent();

        $this->assertInstanceOf('Kirby\\FileSystem\\Folder', $parent);
    }

    public function testRootParent()
    {
        $folder = new Folder('/');
        $parent = $folder->parent();

        $this->assertEquals(null, $parent);
    }

    public function testFind()
    {
        $folder  = $this->_folder();
        $results = $folder->find('a.*');

        $this->assertCount(1, $results);
        $this->assertEquals($results[0], $folder->root() . '/a.js');
    }

    public function testToString()
    {
        $folder = $this->_folder();
        $this->assertEquals($folder->root(), $folder->__toString());
        $this->assertEquals($folder->root(), (string)$folder);
    }
}
