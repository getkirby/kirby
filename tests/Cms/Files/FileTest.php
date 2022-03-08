<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;

class FileTestModel extends File
{
}

class FileTest extends TestCase
{
    protected function defaults(?App $kirby = null): array
    {
        $page = new Page([
            'kirby' => $kirby,
            'slug'  => 'test'
        ]);

        return [
            'filename' => 'cover.jpg',
            'parent'   => $page,
            'url'      => 'https://getkirby.com/projects/project-a/cover.jpg'
        ];
    }

    protected function file(array $props = [])
    {
        return new File(array_merge($this->defaults($props['kirby'] ?? null), $props));
    }

    public function testAsset()
    {
        $file = $this->file();
        $this->assertInstanceOf('Kirby\Filesystem\File', $file->asset());
        $this->assertEquals('https://getkirby.com/projects/project-a/cover.jpg', $file->asset()->url());
    }

    public function testContent()
    {
        $file = $this->file([
            'content' => [
                'test' => 'Test'
            ]
        ]);

        $this->assertEquals('Test', $file->content()->get('test')->value());
    }

    public function testDefaultContent()
    {
        $file = $this->file();

        $this->assertInstanceOf(Content::class, $file->content());
    }

    public function testFilename()
    {
        $this->assertEquals($this->defaults()['filename'], $this->file()->filename());
    }

    public function testPage()
    {
        $file = $this->file([
            'parent' => $page = new Page(['slug' => 'test'])
        ]);

        $this->assertSame($page, $file->page());

        $file = $this->file([
            'parent' => new User([])
        ]);

        $this->assertNull($file->page());
    }

    public function testParentId()
    {
        $file = $this->file([
            'parent' => $page = new Page(['slug' => 'test'])
        ]);

        $this->assertSame('test', $file->parentId());
    }

    public function testHtml()
    {
        $file = $this->file([
            'filename' => 'test.jpg',
            'url' => null,
            'parent' => new Site(),
            'content' => [
                'alt' => 'This is the alt text'
            ]
        ]);
        $this->assertSame('<img alt="This is the alt text" src="/media/site/f563e6e59b-0/test.jpg">', $file->html());
    }

    public function testUrl()
    {
        $this->assertEquals($this->defaults()['url'], $this->file()->url());
    }

    public function testToString()
    {
        $file = $this->file(['filename' => 'super.jpg']);
        $this->assertEquals('super.jpg', $file->toString('{{ file.filename }}'));
    }

    public function testIsReadable()
    {
        $app = new App([
            'blueprints' => [
                'files/test' => [
                    'options' => ['read' => false]
                ]
            ],
            'roots' => [
                'index' => '/dev/null'
            ],
            'users' => [
                [
                    'email' => 'admin@getkirby.com',
                    'id'    => 'admin',
                    'role'  => 'admin'
                ]
            ],
            'user' => 'admin'
        ]);

        $file = $this->file([
            'kirby'    => $app,
            'filename' => 'test.jpg'
        ]);
        $this->assertTrue($file->isReadable());
        $this->assertTrue($file->isReadable()); // test caching

        $file = $this->file([
            'kirby'    => $app,
            'filename' => 'test.jpg',
            'template' => 'test'
        ]);
        $this->assertFalse($file->isReadable());
        $this->assertFalse($file->isReadable()); // test caching
    }

    public function testMediaHash()
    {
        $app = new App([
            'roots' => [
                'index'   => $index = __DIR__ . '/fixtures/FileTest/mediaHash',
                'content' => $index
            ],
            'options' => [
                'content.salt' => 'test'
            ]
        ]);

        F::write($index . '/test.jpg', 'test');
        touch($index . '/test.jpg', 5432112345);
        $file = $this->file([
            'kirby'    => $app,
            'parent'   => $app->site(),
            'filename' => 'test.jpg'
        ]);

        $this->assertSame('08756f3115-5432112345', $file->mediaHash());

        Dir::remove(dirname($index));
    }

    public function testMediaToken()
    {
        $app = new App([
            'roots' => [
                'index'   => $index = __DIR__ . '/fixtures/FileTest/mediaHash',
                'content' => $index
            ],
            'options' => [
                'content.salt' => 'test'
            ]
        ]);

        $file = $this->file([
            'kirby'    => $app,
            'parent'   => $app->site(),
            'filename' => 'test.jpg'
        ]);

        $this->assertSame('08756f3115', $file->mediaToken());
    }

    public function testModified()
    {
        $app = new App([
            'roots' => [
                'index'   => $index = __DIR__ . '/fixtures/FileTest/modified',
                'content' => $index
            ]
        ]);

        // create a file
        F::write($file = $index . '/test.js', 'test');

        $modified = filemtime($file);
        $file     = $app->file('test.js');

        $this->assertEquals($modified, $file->modified());

        // default date handler
        $format = 'd.m.Y';
        $this->assertEquals(date($format, $modified), $file->modified($format));

        // custom date handler
        $format = '%d.%m.%Y';
        $this->assertEquals(@strftime($format, $modified), $file->modified($format, 'strftime'));

        Dir::remove(dirname($index));
    }

    public function testModifiedContent()
    {
        $app = new App([
            'roots' => [
                'index'   => $index = __DIR__ . '/fixtures/FileTest/modified',
                'content' => $index
            ]
        ]);

        // create a file
        F::write($file = $index . '/test.js', 'test');
        touch($file, $modifiedFile = \time() + 2);

        F::write($content = $index . '/test.js.txt', 'test');
        touch($file, $modifiedContent = \time() + 5);

        $file = $app->file('test.js');

        $this->assertNotEquals($modifiedFile, $file->modified());
        $this->assertEquals($modifiedContent, $file->modified());

        Dir::remove(dirname($index));
    }

    public function testModifiedSpecifyingLanguage()
    {
        $app = new App([
            'roots' => [
                'index'   => $index = __DIR__ . '/fixtures/FileTest/modified',
                'content' => $index
            ],
            'languages' => [
                [
                    'code'    => 'en',
                    'default' => true,
                    'name'    => 'English'
                ],
                [
                    'code'    => 'de',
                    'name'    => 'Deutsch'
                ]
            ]
        ]);

        // create a file
        F::write($index . '/test.js', 'test');

        // create the english content
        F::write($file = $index . '/test.js.en.txt', 'test');
        touch($file, $modifiedEnContent = \time() + 2);

        // create the german content
        F::write($file = $index . '/test.js.de.txt', 'test');
        touch($file, $modifiedDeContent = \time() + 5);

        $file = $app->file('test.js');

        $this->assertEquals($modifiedEnContent, $file->modified(null, null, 'en'));
        $this->assertEquals($modifiedDeContent, $file->modified(null, null, 'de'));

        Dir::remove(dirname($index));
    }

    public function testPanel()
    {
        $page = new Page([
            'slug'  => 'test',
            'files' => [
                [
                    'filename' => 'test.pdf'
                ]
            ]
        ]);

        $file = $page->file('test.pdf');
        $this->assertInstanceOf('Kirby\Panel\File', $file->panel());
    }

    public function testPreviewUrl()
    {
        $page = new Page([
            'slug'  => 'test',
            'files' => [
                [
                    'filename' => 'test.pdf'
                ]
            ]
        ]);

        $file = $page->file('test.pdf');
        $this->assertSame('/test/test.pdf', $file->previewUrl());
    }

    public function testPreviewUrlForDraft()
    {
        $page = new Page([
            'slug'    => 'test',
            'isDraft' => true,
            'files'   => [
                [
                    'filename' => 'test.pdf'
                ]
            ]
        ]);

        $file = $page->file('test.pdf');
        $this->assertSame($file->url(), $file->previewUrl());
    }

    public function testPreviewUrlForPageWithCustomPreviewSetting()
    {
        $app = new App([
            'blueprints' => [
                'pages/test' => [
                    'options' => [
                        'preview' => false
                    ]
                ]
            ],
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'children' => [
                    [
                        'slug'     => 'test',
                        'template' => 'test',
                        'files'    => [
                            [
                                'filename' => 'test.pdf'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $file = $app->file('test/test.pdf');
        $this->assertSame($file->url(), $file->previewUrl());
    }

    public function testPreviewUrlForUserFile()
    {
        $user = new User([
            'email' => 'test@getkirby.com',
            'files' => [
                [
                    'filename' => 'test.pdf'
                ]
            ]
        ]);

        $file = $user->file('test.pdf');
        $this->assertSame($file->url(), $file->previewUrl());
    }

    public function testQuery()
    {
        $file = $this->file();

        $this->assertSame('cover.jpg', $file->query('file.filename'));
        $this->assertSame('cover.jpg', $file->query('model.filename'));
    }

    public function testApiUrl()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'urls' => [
                'index' => 'https://getkirby.com'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'mother',
                        'children' => [
                            [
                                'slug' => 'child',
                                'files' => [
                                    ['filename' => 'page-file.jpg'],
                                ]
                            ]
                        ]
                    ]
                ],
                'files' => [
                    ['filename' => 'site-file.jpg']
                ]
            ],
            'users' => [
                [
                    'email' => 'test@getkirby.com',
                    'id'    => 'test',
                    'files' => [
                        ['filename' => 'user-file.jpg']
                    ]
                ]
            ]
        ]);

        // site file
        $file = $app->file('site-file.jpg');

        $this->assertEquals('https://getkirby.com/api/site/files/site-file.jpg', $file->apiUrl());
        $this->assertEquals('site/files/site-file.jpg', $file->apiUrl(true));

        // page file
        $file = $app->file('mother/child/page-file.jpg');

        $this->assertEquals('https://getkirby.com/api/pages/mother+child/files/page-file.jpg', $file->apiUrl());
        $this->assertEquals('pages/mother+child/files/page-file.jpg', $file->apiUrl(true));

        // user file
        $user = $app->user('test@getkirby.com');
        $file = $user->file('user-file.jpg');

        $this->assertEquals('https://getkirby.com/api/users/test/files/user-file.jpg', $file->apiUrl());
        $this->assertEquals('users/test/files/user-file.jpg', $file->apiUrl(true));
    }
}
