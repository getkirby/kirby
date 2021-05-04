<?php

namespace Kirby\Cms;

use Kirby\Image\Image;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

class FileTestModel extends File
{
}

class FileTestForceLocked extends File
{
    public function isLocked(): bool
    {
        return true;
    }
}

class FileTest extends TestCase
{
    protected function defaults(): array
    {
        return [
            'filename' => 'cover.jpg',
            'url'      => 'https://getkirby.com/projects/project-a/cover.jpg'
        ];
    }

    protected function file(array $props = [])
    {
        return new File(array_merge($this->defaults(), $props));
    }

    public function testAsset()
    {
        $file = $this->file();

        $this->assertInstanceOf(Image::class, $file->asset());
        $this->assertEquals(null, $file->asset()->url());
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

    public function testDragText()
    {
        $page = new Page([
            'slug'  => 'test',
            'files' => [
                ['filename' => 'test.jpg'],
                ['filename' => 'test.mp4'],
                ['filename' => 'test.pdf'],
            ]
        ]);

        $file = $page->file('test.jpg');
        $this->assertSame('(image: test.jpg)', $file->dragText());

        $file = $page->file('test.mp4');
        $this->assertSame('(video: test.mp4)', $file->dragText());

        $file = $page->file('test.pdf');
        $this->assertSame('(file: test.pdf)', $file->dragText());
    }

    public function testDragTextMarkdown()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => [
                'panel' => [
                    'kirbytext' => false
                ]
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'files' => [
                            ['filename' => 'test.jpg'],
                            ['filename' => 'test.mp4'],
                            ['filename' => 'test.pdf'],
                        ]
                    ]
                ]
            ]
        ]);

        $file = $app->page('test')->file('test.jpg');
        $this->assertSame('![](test.jpg)', $file->dragText());

        $file = $app->page('test')->file('test.mp4');
        $this->assertSame('[test.mp4](test.mp4)', $file->dragText());

        $file = $app->page('test')->file('test.pdf');
        $this->assertSame('[test.pdf](test.pdf)', $file->dragText());
    }

    public function testDragTextForImages()
    {
        $page = new Page([
            'slug'  => 'test',
            'files' => [
                [
                    'filename' => 'test.jpg'
                ]
            ]
        ]);

        $file = $page->file('test.jpg');
        $this->assertEquals('(image: test.jpg)', $file->dragText());
    }

    public function testDragTextForImagesMarkdown()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => [
                'panel' => [
                    'kirbytext' => false
                ]
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'files' => [
                            [
                                'filename' => 'test.jpg'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $file = $app->page('test')->file('test.jpg');
        $this->assertEquals('![](test.jpg)', $file->dragText());
    }

    public function testDragTextCustomMarkdown()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],

            'options' => [
                'panel' => [
                    'kirbytext' => false,
                    'markdown' => [
                        'fileDragText' => function (\Kirby\Cms\File $file, string $url) {
                            if ($file->extension() === 'heic') {
                                return sprintf('![](%s)', $url);
                            }

                            return null;
                        },
                    ]
                ]
            ],

            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'files' => [
                            [
                                'filename' => 'test.heic'
                            ],
                            [
                                'filename' => 'test.jpg'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        // Custom function does not match and returns null, default case
        $file = $app->page('test')->file('test.jpg');
        $this->assertEquals('![](test.jpg)', $file->dragText());

        // Custom function should return image tag for heic
        $file = $app->page('test')->file('test.heic');
        $this->assertEquals('![](test.heic)', $file->dragText());
    }

    public function testDragTextCustomKirbytext()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],

            'options' => [
                'panel' => [
                    'kirbytext' => [
                        'fileDragText' => function (\Kirby\Cms\File $file, string $url) {
                            if ($file->extension() === 'heic') {
                                return sprintf('(image: %s)', $url);
                            }

                            return null;
                        },
                    ]
                ]
            ],

            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'files' => [
                            [
                                'filename' => 'test.heic'
                            ],
                            [
                                'filename' => 'test.jpg'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        // Custom function does not match and returns null, default case
        $file = $app->page('test')->file('test.jpg');
        $this->assertEquals('(image: test.jpg)', $file->dragText());

        // Custom function should return image tag for heic
        $file = $app->page('test')->file('test.heic');
        $this->assertEquals('(image: test.heic)', $file->dragText());
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

        $this->assertEquals($page, $file->page());
    }

    public function testParentId()
    {
        $file = new File([
            'filename' => 'test.jpg',
            'parent'   => $page = new Page(['slug' => 'test'])
        ]);

        $this->assertEquals('test', $file->parentId());

        $file = new File([
            'filename' => 'test.jpg',
        ]);

        $this->assertNull($file->parentId());
    }

    public function testDefaultPage()
    {
        $this->assertNull($this->file()->page());
    }

    public function testUrl()
    {
        $this->assertEquals($this->defaults()['url'], $this->file()->url());
    }

    public function testToString()
    {
        $file = new File(['filename' => 'super.jpg']);
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

        $file = new File([
            'kirby'    => $app,
            'filename' => 'test.jpg'
        ]);
        $this->assertTrue($file->isReadable());
        $this->assertTrue($file->isReadable()); // test caching

        $file = new File([
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
        $file = new File([
            'kirby'    => $app,
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

        $file = new File([
            'kirby'    => $app,
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
        $this->assertEquals(strftime($format, $modified), $file->modified($format, 'strftime'));

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

    public function testPanelIconDefault()
    {
        $file = new File([
            'filename' => 'something.jpg'
        ]);

        $icon     = $file->panelIcon();
        $expected = [
            'type'  => 'file-image',
            'back'  => 'pattern',
            'color' => '#de935f',
            'ratio' => null
        ];

        $this->assertEquals($expected, $icon);
    }

    public function testPanelIconWithRatio()
    {
        $file = new File([
            'filename' => 'something.jpg'
        ]);

        $icon     = $file->panelIcon(['ratio' => '3/2']);
        $expected = [
            'type'  => 'file-image',
            'back'  => 'pattern',
            'color' => '#de935f',
            'ratio' => '3/2'
        ];

        $this->assertEquals($expected, $icon);
    }

    public function testPanelOptions()
    {
        $file = new File([
            'filename' => 'test.jpg',
        ]);

        $file->kirby()->impersonate('kirby');

        $expected = [
            'changeName' => true,
            'create'     => true,
            'delete'     => true,
            'read'       => true,
            'replace'    => true,
            'update'     => true,
        ];

        $this->assertEquals($expected, $file->panelOptions());
    }

    public function testPanelOptionsWithLockedFile()
    {
        $file = new FileTestForceLocked([
            'filename' => 'test.jpg',
        ]);

        $file->kirby()->impersonate('kirby');

        // without override
        $expected = [
            'changeName' => false,
            'create'     => false,
            'delete'     => false,
            'read'       => false,
            'replace'    => false,
            'update'     => false,
        ];

        $this->assertEquals($expected, $file->panelOptions());

        // with override
        $expected = [
            'changeName' => false,
            'create'     => false,
            'delete'     => true,
            'read'       => false,
            'replace'    => false,
            'update'     => false,
        ];

        $this->assertEquals($expected, $file->panelOptions(['delete']));
    }

    public function testPanelOptionsDefaultReplaceOption()
    {
        $file = new File([
            'filename' => 'test.js',
        ]);
        $file->kirby()->impersonate('kirby');

        $expected = [
            'changeName' => true,
            'create'     => true,
            'delete'     => true,
            'read'       => true,
            'replace'    => false,
            'update'     => true,
        ];

        $this->assertSame($expected, $file->panelOptions());
    }

    public function testPanelOptionsAllowedReplaceOption()
    {
        new App([
            'blueprints' => [
                'files/test' => [
                    'name'   => 'test',
                    'accept' => true
                ]
            ]
        ]);

        $file = new File([
            'filename' => 'test.js',
            'template' => 'test',
        ]);

        $file->kirby()->impersonate('kirby');

        $expected = [
            'changeName' => true,
            'create'     => true,
            'delete'     => true,
            'read'       => true,
            'replace'    => true,
            'update'     => true,
        ];

        $this->assertSame($expected, $file->panelOptions());
    }

    public function testPanelOptionsDisabledReplaceOption()
    {
        new App([
            'blueprints' => [
                'files/test' => [
                    'name'   => 'test',
                    'accept' => [
                        'type' => 'image'
                    ]
                ]
            ]
        ]);

        $file = new File([
            'filename' => 'test.js',
            'template' => 'test',
        ]);

        $file->kirby()->impersonate('kirby');

        $expected = [
            'changeName' => true,
            'create'     => true,
            'delete'     => true,
            'read'       => true,
            'replace'    => false,
            'update'     => true,
        ];

        $this->assertSame($expected, $file->panelOptions());
    }

    public function testPanelUrl()
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

        $this->assertEquals('https://getkirby.com/panel/site/files/site-file.jpg', $file->panelUrl());
        $this->assertEquals('/site/files/site-file.jpg', $file->panelUrl(true));

        // page file
        $file = $app->file('mother/child/page-file.jpg');

        $this->assertEquals('https://getkirby.com/panel/pages/mother+child/files/page-file.jpg', $file->panelUrl());
        $this->assertEquals('/pages/mother+child/files/page-file.jpg', $file->panelUrl(true));

        // user file
        $user = $app->user('test@getkirby.com');
        $file = $user->file('user-file.jpg');

        $this->assertEquals('https://getkirby.com/panel/users/test/files/user-file.jpg', $file->panelUrl());
        $this->assertEquals('/users/test/files/user-file.jpg', $file->panelUrl(true));
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
