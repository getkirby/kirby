<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Image\Image;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Dir;

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
                                'filename' => 'page.js'
                            ]
                        ]
                    ]
                ],
                'files' => [
                    [
                        'filename' => 'site.js'
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
        $this->assertEquals('test.js', $result->filename());
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
        $this->assertEquals('test.js', $result->filename());
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
                'file.create:before' => function (File $file, Image $image) use (&$before, $phpunit, $parent) {
                    $before = true;
                },
                'file.create:after' => function (File $file) use (&$after, $phpunit, $parent) {
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

        $this->assertFileNotExists($file->root());
        $this->assertFileNotExists($file->contentFile());
    }

    /**
     * @dataProvider fileProvider
     */
    public function testPublish($file)
    {
        // create an empty dummy file
        F::write($file->root(), '');

        $this->assertFileNotExists($file->mediaRoot());

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
        $this->assertFileNotExists($file->contentFile());

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

        $this->assertFileNotExists($file->mediaRoot());
        $file->publish();
        $this->assertFileExists($file->mediaRoot());
        $file->unpublish();
        $this->assertFileNotExists($file->mediaRoot());
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
}
