<?php

namespace Kirby\Cms;

class FileRulesTest extends TestCase
{
    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        $this->app->impersonate('kirby');
    }

    public function testChangeName()
    {
        $page = new Page([
            'slug' => 'test',
            'files' => [
                ['filename' => 'a.jpg'],
                ['filename' => 'b.jpg']
            ]
        ]);

        $file = $page->file('a.jpg');

        $this->assertTrue(FileRules::changeName($file, 'c'));
    }

    public function testChangeToSameNameWithDifferentException()
    {
        $page = new Page([
            'slug' => 'test',
            'files' => [
                ['filename' => 'a.jpg'],
                ['filename' => 'b.png']
            ]
        ]);

        $file = $page->file('a.jpg');

        $this->assertTrue(FileRules::changeName($file, 'b'));
    }

    /**
     * @expectedException Kirby\Exception\DuplicateException
     * @expectedExceptionMessage A file with the name "b.jpg" already exists
     */
    public function testChangeNameToExistingFile()
    {
        $page = new Page([
            'slug' => 'test',
            'files' => [
                ['filename' => 'a.jpg'],
                ['filename' => 'b.jpg']
            ]
        ]);

        $file = $page->file('a.jpg');
        FileRules::changeName($file, 'b');
    }
}
