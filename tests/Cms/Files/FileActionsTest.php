<?php

namespace Kirby\Cms;

use Kirby\File\File as BaseFile;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

class FileActionsTest extends TestCase
{
    protected $app;
    protected $fixtures;

    public function app()
    {
        return new App([
            'roots' => [
                'index' => $this->fixtures = __DIR__ . '/fixtures/FileActionsTest'
            ],
            'site' => [
                'children' => [
                    [
                        'slug'  => 'test',
                        'files' => [
                            [
                                'filename' => 'page.csv'
                            ]
                        ]
                    ]
                ],
                'files' => [
                    [
                        'filename' => 'site.csv'
                    ]
                ],
            ],
            'users' => [
                [
                    'email' => 'admin@domain.com',
                    'role'  => 'admin'
                ]
            ],
            'user' => 'admin@domain.com'
        ]);
    }

    public function appWithLanguages()
    {
        return $this->app()->clone([
            'languages' => [
                [
                    'code'    => 'en',
                    'name'    => 'English',
                    'default' => true
                ],
                [
                    'code' => 'de',
                    'name' => 'Deutsch'
                ]
            ]
        ]);
    }

    public function setUp(): void
    {
        $this->app = $this->app();
        Dir::make($this->fixtures);
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
    }

    public function parentProvider()
    {
        $app = $this->app();

        return [
            [$app->site()],
            [$app->site()->children()->first()]
        ];
    }

    public function fileProvider()
    {
        $app = $this->app();

        return [
            [$app->site()->file()],
            [$app->site()->children()->files()->first()]
        ];
    }

    /**
     * @dataProvider fileProvider
     */
    public function testChangeName(File $file)
    {
        // create an empty dummy file
        F::write($file->root(), '');
        // ...and an empty content file for it
        F::write($file->contentFile(), '');

        $this->assertFileExists($file->root());
        $this->assertFileExists($file->contentFile());

        $result = $file->changeName('test');

        $this->assertNotEquals($file->root(), $result->root());
        $this->assertEquals('test.csv', $result->filename());
        $this->assertFileExists($result->root());
        $this->assertFileExists($result->contentFile());
    }

    public function fileProviderMultiLang()
    {
        $app = $this->appWithLanguages();

        return [
            [$app->site()->file()],
            [$app->site()->children()->files()->first()]
        ];
    }

    /**
     * @dataProvider fileProviderMultiLang
     */
    public function testChangeNameMultiLang(File $file)
    {
        $app = $this->appWithLanguages();
        $app->impersonate('kirby');

        Dir::make($this->fixtures);

        // create an empty dummy file
        F::write($file->root(), '');
        // ...and empty content files for it
        F::write($file->contentFile('en'), '');
        F::write($file->contentFile('de'), '');

        $this->assertFileExists($file->root());
        $this->assertFileExists($file->contentFile('en'));
        $this->assertFileExists($file->contentFile('de'));

        $result = $file->changeName('test');

        $this->assertNotEquals($file->root(), $result->root());
        $this->assertEquals('test.csv', $result->filename());
        $this->assertFileExists($result->root());
        $this->assertFileExists($result->contentFile('en'));
        $this->assertFileExists($result->contentFile('de'));
    }

    /**
     * @dataProvider parentProvider
     */
    public function testCreate($parent)
    {
        $source = $this->fixtures . '/source.md';

        // create the dummy source
        F::write($source, '# Test');

        $result = File::create([
            'filename' => 'test.md',
            'source'   => $source,
            'parent'   => $parent
        ]);

        $this->assertFileExists($result->root());
        $this->assertFileExists($parent->root() . '/test.md');
    }

    /**
     * @dataProvider parentProvider
     */
    public function testCreateWithDefaults($parent)
    {
        $source = $this->fixtures . '/source.md';

        // create the dummy source
        F::write($source, '# Test');

        $result = File::create([
            'filename' => 'test.md',
            'source'   => $source,
            'parent'   => $parent,
            'blueprint' => [
                'name' => 'test',
                'fields' => [
                    'a'  => [
                        'type'    => 'text',
                        'default' => 'A'
                    ],
                    'b' => [
                        'type'    => 'textarea',
                        'default' => 'B'
                    ]
                ]
            ]
        ]);

        $this->assertEquals('A', $result->a()->value());
        $this->assertEquals('B', $result->b()->value());
    }

    /**
     * @dataProvider parentProvider
     */
    public function testCreateWithDefaultsAndContent($parent)
    {
        $source = $this->fixtures . '/source.md';

        // create the dummy source
        F::write($source, '# Test');

        $result = File::create([
            'content' => [
                'a' => 'Custom A'
            ],
            'filename' => 'test.md',
            'source'   => $source,
            'parent'   => $parent,
            'blueprint' => [
                'name' => 'test',
                'fields' => [
                    'a'  => [
                        'type'    => 'text',
                        'default' => 'A'
                    ],
                    'b' => [
                        'type'    => 'textarea',
                        'default' => 'B'
                    ]
                ]
            ]
        ]);

        $this->assertEquals('Custom A', $result->a()->value());
        $this->assertEquals('B', $result->b()->value());
    }

    /**
     * @dataProvider parentProvider
     */
    public function testCreateHooks($parent)
    {
        $phpunit = $this;
        $before  = false;
        $after   = false;

        $app = $this->app->clone([
            'hooks' => [
                'file.create:before' => function (File $file, BaseFile $upload) use (&$before) {
                    $before = true;
                },
                'file.create:after' => function (File $file) use (&$after, $phpunit) {
                    $phpunit->assertTrue($file->siblings(true)->has($file));
                    $phpunit->assertTrue($file->parent()->files()->has($file));
                    $phpunit->assertEquals('test.md', $file->filename());

                    $after = true;
                }
            ]
        ]);

        // create the dummy source
        F::write($source = $this->fixtures . '/source.md', '# Test');

        $result = File::create([
            'filename' => 'test.md',
            'source'   => $source,
            'parent'   => $parent
        ]);

        $this->assertTrue($before);
        $this->assertTrue($after);
    }

    /**
     * @dataProvider fileProvider
     */
    public function testDelete(File $file)
    {
        // create an empty dummy file
        F::write($file->root(), '');
        // ...and an empty content file for it
        F::write($file->contentFile(), '');

        $this->assertFileExists($file->root());
        $this->assertFileExists($file->contentFile());

        $result = $file->delete();

        $this->assertTrue($result);

        $this->assertFileDoesNotExist($file->root());
        $this->assertFileDoesNotExist($file->contentFile());
    }

    /**
     * @dataProvider fileProvider
     */
    public function testPublish($file)
    {
        // create an empty dummy file
        F::write($file->root(), '');

        $this->assertFileDoesNotExist($file->mediaRoot());

        $file->publish();

        $this->assertFileExists($file->mediaRoot());
    }

    /**
     * @dataProvider parentProvider
     */
    public function testReplace($parent)
    {
        $original    = $this->fixtures . '/original.md';
        $replacement = $this->fixtures . '/replacement.md';

        // create the dummy files
        F::write($original, '# Original');
        F::write($replacement, '# Replacement');

        $originalFile = File::create([
            'filename' => 'test.md',
            'source'   => $original,
            'parent'   => $parent
        ]);

        $this->assertEquals(F::read($original), F::read($originalFile->root()));

        $replacedFile = $originalFile->replace($replacement);

        $this->assertEquals(F::read($replacement), F::read($replacedFile->root()));
    }

    /**
     * @dataProvider fileProvider
     */
    public function testSave($file)
    {
        // create an empty dummy file
        F::write($file->root(), '');

        $this->assertFileExists($file->root());
        $this->assertFileDoesNotExist($file->contentFile());

        $file = $file->clone(['content' => ['caption' => 'save']])->save();

        $this->assertFileExists($file->contentFile());
    }

    /**
     * @dataProvider fileProvider
     */
    public function testUnpublish($file)
    {
        // create an empty dummy file
        F::write($file->root(), '');

        $this->assertFileDoesNotExist($file->mediaRoot());
        $file->publish();
        $this->assertFileExists($file->mediaRoot());
        $file->unpublish();
        $this->assertFileDoesNotExist($file->mediaRoot());
    }

    /**
     * @dataProvider fileProvider
     */
    public function testUpdate($file)
    {
        $file = $file->update([
            'caption' => $caption = 'test'
        ]);

        $this->assertEquals($caption, $file->caption()->value());
    }

    public function testChangeNameHooks()
    {
        $calls = 0;
        $phpunit = $this;

        $app = $this->app->clone([
            'hooks' => [
                'file.changeName:before' => function (File $file, $name) use ($phpunit, &$calls) {
                    $phpunit->assertInstanceOf('Kirby\Cms\File', $file);
                    $phpunit->assertSame('test', $name);
                    $phpunit->assertSame('site.csv', $file->filename());
                    $calls++;
                },
                'file.changeName:after' => function (File $newFile, File $oldFile) use ($phpunit, &$calls) {
                    $phpunit->assertInstanceOf('Kirby\Cms\File', $newFile);
                    $phpunit->assertInstanceOf('Kirby\Cms\File', $oldFile);
                    $phpunit->assertSame('test.csv', $newFile->filename());
                    $phpunit->assertSame('site.csv', $oldFile->filename());
                    $calls++;
                },
            ]
        ]);

        $app->site()->file()->changeName('test');

        $this->assertSame(2, $calls);
    }

    public function testChangeSortHooks()
    {
        $calls = 0;
        $phpunit = $this;

        $app = $this->app->clone([
            'site' => [
                'files' => [
                    [
                        'filename' => 'site-1.csv'
                    ],
                    [
                        'filename' => 'site-2.csv'
                    ],
                    [
                        'filename' => 'site-3.csv'
                    ]
                ],
            ],
            'hooks' => [
                'file.changeSort:before' => function (File $file, $position) use ($phpunit, &$calls) {
                    $phpunit->assertInstanceOf('Kirby\Cms\File', $file);
                    $phpunit->assertSame(3, $position);
                    $phpunit->assertNull($file->sort()->value());
                    $calls++;
                },
                'file.changeSort:after' => function (File $newFile, File $oldFile) use ($phpunit, &$calls) {
                    $phpunit->assertInstanceOf('Kirby\Cms\File', $newFile);
                    $phpunit->assertInstanceOf('Kirby\Cms\File', $oldFile);
                    $phpunit->assertSame(3, $newFile->sort()->value());
                    $phpunit->assertNull($oldFile->sort()->value());
                    $calls++;
                },
            ]
        ]);

        $app->site()->file()->changeSort(3);

        $this->assertSame(2, $calls);
    }

    /**
     * @dataProvider parentProvider
     */
    public function testDeleteHooks($parent)
    {
        $calls = 0;
        $phpunit = $this;

        $this->app->clone([
            'hooks' => [
                'file.delete:before' => function (File $file) use ($phpunit, &$calls) {
                    $phpunit->assertFileExists($file->root());
                    $phpunit->assertSame('test.md', $file->filename());
                    $calls++;
                },
                'file.delete:after' => function ($status, File $file) use ($phpunit, &$calls) {
                    $phpunit->assertTrue($status);
                    $phpunit->assertFileDoesNotExist($file->root());
                    $phpunit->assertSame('test.md', $file->filename());
                    $calls++;
                }
            ]
        ]);

        // create the dummy source
        F::write($source = $this->fixtures . '/source.md', '# Test');

        $file = File::create([
            'filename' => 'test.md',
            'source'   => $source,
            'parent'   => $parent
        ]);

        $file->delete();

        $this->assertSame(2, $calls);
    }

    /**
     * @dataProvider parentProvider
     */
    public function testReplaceHooks($parent)
    {
        $calls = 0;
        $phpunit = $this;

        $app = $this->app->clone([
            'hooks' => [
                'file.replace:before' => function (File $file, BaseFile $upload) use ($phpunit, &$calls) {
                    $phpunit->assertInstanceOf('Kirby\Cms\File', $file);
                    $phpunit->assertInstanceOf('Kirby\File\File', $upload);
                    $phpunit->assertSame('site.csv', $file->filename());
                    $phpunit->assertSame('replace.csv', $upload->filename());
                    $phpunit->assertFileDoesNotExist($file->root());
                    $calls++;
                },
                'file.replace:after' => function (File $newFile, File $oldFile) use ($phpunit, &$calls) {
                    $phpunit->assertInstanceOf('Kirby\Cms\File', $newFile);
                    $phpunit->assertInstanceOf('Kirby\Cms\File', $oldFile);
                    $phpunit->assertSame('site.csv', $newFile->filename());
                    $phpunit->assertSame('Replace', F::read($newFile->root()));
                    $phpunit->assertSame('site.csv', $oldFile->filename());
                    $calls++;
                },
            ]
        ]);

        // create the dummy source
        F::write($source = $this->fixtures . '/replace.csv', 'Replace');

        File::create([
            'filename' => 'replace.csv',
            'source'   => $source,
            'parent'   => $parent
        ]);

        $app->site()->file()->replace($source);

        $this->assertSame(2, $calls);
    }

    public function testUpdateHooks()
    {
        $calls = 0;
        $phpunit = $this;
        $input = [
            'title' => 'Test'
        ];

        $app = $this->app->clone([
            'hooks' => [
                'file.update:before' => function (File $file, $values, $strings) use ($phpunit, $input, &$calls) {
                    $phpunit->assertInstanceOf('Kirby\Cms\File', $file);
                    $phpunit->assertNull($file->title()->value());
                    $phpunit->assertSame($input, $values);
                    $phpunit->assertSame($input, $strings);
                    $calls++;
                },
                'file.update:after' => function (File $newFile, File $oldFile) use ($phpunit, &$calls) {
                    $phpunit->assertInstanceOf('Kirby\Cms\File', $newFile);
                    $phpunit->assertInstanceOf('Kirby\Cms\File', $oldFile);
                    $phpunit->assertSame('Test', $newFile->title()->value());
                    $phpunit->assertNull($oldFile->title()->value());
                    $calls++;
                },
            ]
        ]);

        $app->site()->file()->update($input);

        $this->assertSame(2, $calls);
    }
}
