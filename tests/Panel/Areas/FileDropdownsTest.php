<?php

namespace Kirby\Panel\Areas;

use Kirby\Toolkit\F;

class FileDropdownsTest extends AreaTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->install();
        $this->login();
    }

    protected function createPageFile(): void
    {
        $this->app([
            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'files' => [
                            ['filename' => 'test.jpg']
                        ]
                    ]
                ]
            ]
        ]);

        // pretend the file exists
        F::write($this->app->page('test')->file('test.jpg')->root(), '');

        $this->login();
    }

    protected function createSiteFile(): void
    {
        $this->app([
            'site' => [
                'files' => [
                    ['filename' => 'test.jpg']
                ]
            ]
        ]);

        // pretend the file exists
        F::write($this->app->site()->file('test.jpg')->root(), '');

        $this->login();
    }

    protected function createUserFile(): void
    {
        $this->app([
            'users' => [
                [
                    'id'    => 'test',
                    'email' => 'test@getkirby.com',
                    'role'  => 'admin',
                    'files' => [
                        ['filename' => 'test.jpg']
                    ]
                ]
            ]
        ]);

        // pretend the file exists
        F::write($this->app->user('test')->file('test.jpg')->root(), '');

        $this->login();
    }

    public function testFileDropdownForPageFile(): void
    {
        $this->createPageFile();

        $options = $this->dropdown('pages/test/files/test.jpg')['options'];

        $this->assertSame('/pages/test/files/test.jpg/changeName', $options[0]['dialog']);
        $this->assertSame('Rename', $options[0]['text']);

        $this->assertSame('replace', $options[1]['click']);
        $this->assertSame('Replace', $options[1]['text']);

        $this->assertSame('-', $options[2]);

        $this->assertSame('/pages/test/files/test.jpg/delete', $options[3]['dialog']);
        $this->assertSame('Delete', $options[3]['text']);
    }

    public function testFileDropdownForSiteFile(): void
    {
        $this->createSiteFile();

        $options = $this->dropdown('site/files/test.jpg')['options'];

        $this->assertSame('/site/files/test.jpg/changeName', $options[0]['dialog']);
        $this->assertSame('Rename', $options[0]['text']);

        $this->assertSame('replace', $options[1]['click']);
        $this->assertSame('Replace', $options[1]['text']);

        $this->assertSame('-', $options[2]);

        $this->assertSame('/site/files/test.jpg/delete', $options[3]['dialog']);
        $this->assertSame('Delete', $options[3]['text']);
    }

    public function testFileDropdownForUserFile(): void
    {
        $this->createUserFile();

        $options = $this->dropdown('users/test/files/test.jpg')['options'];

        $this->assertSame('/users/test/files/test.jpg/changeName', $options[0]['dialog']);
        $this->assertSame('Rename', $options[0]['text']);

        $this->assertSame('replace', $options[1]['click']);
        $this->assertSame('Replace', $options[1]['text']);

        $this->assertSame('-', $options[2]);

        $this->assertSame('/users/test/files/test.jpg/delete', $options[3]['dialog']);
        $this->assertSame('Delete', $options[3]['text']);
    }
}
