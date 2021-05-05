<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\File as ModelFile;
use Kirby\Cms\Page as ModelPage;
use PHPUnit\Framework\TestCase;

class ModelFileTestForceLocked extends ModelFile
{
    public function isLocked(): bool
    {
        return true;
    }
}

class FileTest extends TestCase
{
    public function testDragText()
    {
        $page = new ModelPage([
            'slug'  => 'test',
            'files' => [
                ['filename' => 'test.jpg'],
                ['filename' => 'test.mp4'],
                ['filename' => 'test.pdf']
            ]
        ]);

        $panel = new File($page->file('test.pdf'));
        $this->assertEquals('(file: test.pdf)', $panel->dragText());

        $panel = new File($page->file('test.mp4'));
        $this->assertSame('(video: test.mp4)', $panel->dragText());

        $panel = new File($page->file('test.jpg'));
        $this->assertEquals('(image: test.jpg)', $panel->dragText());
    }

    public function testDragTextForImages()
    {
        $page = new ModelPage([
            'slug'  => 'test',
            'files' => [
                ['filename' => 'test.jpg']
            ]
        ]);

        $file = $page->file('test.jpg');
        $this->assertEquals('(image: test.jpg)', $file->dragText());
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
                            ['filename' => 'test.jpg']
                        ]
                    ]
                ]
            ]
        ]);

        $panel = new File($app->page('test')->file('test.jpg'));
        $this->assertEquals('![](test.jpg)', $panel->dragText());
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
                            ['filename' => 'test.heic'],
                            ['filename' => 'test.jpg']
                        ]
                    ]
                ]
            ]
        ]);

        // Custom function does not match and returns null, default case
        $panel = new File($app->page('test')->file('test.jpg'));
        $this->assertEquals('![](test.jpg)', $panel->dragText());

        // Custom function should return image tag for heic
        $panel = new File($app->page('test')->file('test.heic'));
        $this->assertEquals('![](test.heic)', $panel->dragText());
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
                            ['filename' => 'test.heic'],
                            ['filename' => 'test.jpg']
                        ]
                    ]
                ]
            ]
        ]);

        // Custom function does not match and returns null, default case
        $panel = new File($app->page('test')->file('test.jpg'));
        $this->assertEquals('(image: test.jpg)', $panel->dragText());

        // Custom function should return image tag for heic
        $panel = new File($app->page('test')->file('test.heic'));
        $this->assertEquals('(image: test.heic)', $panel->dragText());
    }

    public function testIconDefault()
    {
        $file = new ModelFile([
            'filename' => 'something.jpg'
        ]);

        $icon = (new File($file))->icon();

        $this->assertEquals([
            'type'  => 'file-image',
            'back'  => 'pattern',
            'color' => '#de935f',
            'ratio' => null
        ], $icon);
    }

    public function testIconWithRatio()
    {
        $file = new ModelFile([
            'filename' => 'something.jpg'
        ]);

        $icon = (new File($file))->icon(['ratio' => '3/2']);

        $this->assertEquals([
            'type'  => 'file-image',
            'back'  => 'pattern',
            'color' => '#de935f',
            'ratio' => '3/2'
        ], $icon);
    }

    public function testOptions()
    {
        $file = new ModelFile([
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

        $panel = new File($file);
        $this->assertEquals($expected, $panel->options());
    }

    public function testOptionsWithLockedFile()
    {
        $file = new ModelFileTestForceLocked([
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

        $panel = new File($file);
        $this->assertEquals($expected, $panel->options());

        // with override
        $expected = [
            'changeName' => false,
            'create'     => false,
            'delete'     => true,
            'read'       => false,
            'replace'    => false,
            'update'     => false,
        ];

        $panel = new File($file);
        $this->assertEquals($expected, $panel->options(['delete']));
    }

    public function testOptionsDefaultReplaceOption()
    {
        $file = new ModelFile([
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

        $panel = new File($file);
        $this->assertSame($expected, $panel->options());
    }

    public function testOptionsAllowedReplaceOption()
    {
        new App([
            'blueprints' => [
                'files/test' => [
                    'name'   => 'test',
                    'accept' => true
                ]
            ]
        ]);

        $file = new ModelFile([
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

        $panel = new File($file);
        $this->assertSame($expected, $panel->options());
    }

    public function testOptionsDisabledReplaceOption()
    {
        new App([
            'blueprints' => [
                'files/restricted' => [
                    'name'   => 'restricted',
                    'accept' => [
                        'type' => 'image'
                    ]
                ]
            ]
        ]);

        $file = new ModelFile([
            'filename' => 'test.js',
            'template' => 'restricted',
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

        $panel = new File($file);
        $this->assertSame($expected, $panel->options());
    }

    public function testUrl()
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
        $panel = new File($file);

        $this->assertEquals('https://getkirby.com/panel/site/files/site-file.jpg', $panel->url());
        $this->assertEquals('/site/files/site-file.jpg', $panel->url(true));

        // page file
        $file = $app->file('mother/child/page-file.jpg');
        $panel = new File($file);

        $this->assertEquals('https://getkirby.com/panel/pages/mother+child/files/page-file.jpg', $panel->url());
        $this->assertEquals('/pages/mother+child/files/page-file.jpg', $panel->url(true));

        // user file
        $user = $app->user('test@getkirby.com');
        $file = $user->file('user-file.jpg');
        $panel = new File($file);

        $this->assertEquals('https://getkirby.com/panel/users/test/files/user-file.jpg', $panel->url());
        $this->assertEquals('/users/test/files/user-file.jpg', $panel->url(true));
    }
}
