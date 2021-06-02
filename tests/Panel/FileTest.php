<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\File as ModelFile;
use Kirby\Cms\Page as ModelPage;
use Kirby\Cms\Site as ModelSite;
use Kirby\Cms\User as ModelUser;
use Kirby\Toolkit\Dir;
use PHPUnit\Framework\TestCase;

class ModelFileTestForceLocked extends ModelFile
{
    public function isLocked(): bool
    {
        return true;
    }
}

/**
 * @coversDefaultClass \Kirby\Panel\File
 */
class FileTest extends TestCase
{
    protected $app;
    protected $fixtures;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => $this->fixtures = __DIR__ . '/fixtures/FileTest',
            ]
        ]);

        Dir::make($this->fixtures);
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
    }

    /**
     * @covers ::breadcrumb
     */
    public function testBreadcrumbForSiteFile(): void
    {
        $site = new ModelSite([
            'files' => [
                ['filename' => 'test.jpg'],
            ]
        ]);

        $file = new File($site->file('test.jpg'));
        $this->assertSame([
            [
                'label' => 'test.jpg',
                'link'  => '/site/files/test.jpg'
            ]
        ], $file->breadcrumb());
    }

    /**
     * @covers ::breadcrumb
     */
    public function testBreadcrumbForPageFile(): void
    {
        $page = new ModelPage([
            'slug' => 'test',
            'content' => [
                'title' => 'Test'
            ],
            'files' => [
                ['filename' => 'test.jpg'],
            ]
        ]);

        $file = new File($page->file('test.jpg'));
        $this->assertSame([
            [
                'label' => 'Test',
                'link'  => '/pages/test'
            ],
            [
                'label' => 'test.jpg',
                'link'  => '/pages/test/files/test.jpg'
            ]
        ], $file->breadcrumb());
    }

    /**
     * @covers ::breadcrumb
     */
    public function testBreadcrumbForUserFile(): void
    {
        $user = new ModelUser([
            'id'    => 'test',
            'email' => 'test@getkirby.com',
            'files' => [
                ['filename' => 'test.jpg'],
            ]
        ]);

        $file = new File($user->file('test.jpg'));
        $this->assertSame([
            [
                'label' => 'test@getkirby.com',
                'link'  => '/users/test'
            ],
            [
                'label' => 'test.jpg',
                'link'  => '/users/test/files/test.jpg'
            ]
        ], $file->breadcrumb());
    }

    /**
     * @covers ::dragText
     */
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
        $this->assertSame('(file: test.pdf)', $panel->dragText());

        $panel = new File($page->file('test.mp4'));
        $this->assertSame('(video: test.mp4)', $panel->dragText());

        $panel = new File($page->file('test.jpg'));
        $this->assertSame('(image: test.jpg)', $panel->dragText());
    }

    /**
     * @covers ::dragText
     */
    public function testDragTextMarkdown()
    {
        $app = $this->app->clone([
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

    /**
     * @covers ::dragText
     */
    public function testDragTextCustomMarkdown()
    {
        $app = $this->app->clone([
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
        $this->assertSame('![](test.jpg)', $panel->dragText());

        // Custom function should return image tag for heic
        $panel = new File($app->page('test')->file('test.heic'));
        $this->assertSame('![](test.heic)', $panel->dragText());
    }

    /**
     * @covers ::dragText
     */
    public function testDragTextCustomKirbytext()
    {
        $app = $this->app->clone([
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
        $this->assertSame('(image: test.jpg)', $panel->dragText());

        // Custom function should return image tag for heic
        $panel = new File($app->page('test')->file('test.heic'));
        $this->assertSame('(image: test.heic)', $panel->dragText());
    }

    /**
     * @covers ::icon
     */
    public function testIconDefault()
    {
        $file = new ModelFile([
            'filename' => 'something.jpg'
        ]);

        $icon = (new File($file))->icon();

        $this->assertSame([
            'type'  => 'file-image',
            'ratio' => null,
            'back'  => 'pattern',
            'color' => '#de935f'
        ], $icon);
    }

    /**
     * @covers ::icon
     */
    public function testIconWithRatio()
    {
        $file = new ModelFile([
            'filename' => 'something.jpg'
        ]);

        $icon = (new File($file))->icon(['ratio' => '3/2']);

        $this->assertSame([
            'type'  => 'file-image',
            'ratio' => '3/2',
            'back'  => 'pattern',
            'color' => '#de935f'
        ], $icon);
    }

    /**
     * @covers ::imageSource
     */
    public function testImage()
    {
        $file = new ModelFile([
            'filename' => 'something.jpg'
        ]);

        $image = (new File($file))->image();

        $this->assertSame('3/2', $image['ratio']);
        $this->assertSame('pattern', $image['back']);
        $this->assertTrue(array_key_exists('url', $image));
    }

    /**
     * @covers ::imageSource
     */
    public function testImageCover()
    {
        $app = $this->app->clone([
            'site' => [
                'files' => [
                    ['filename' => 'test.jpg']
                ]
            ]
        ]);

        $file  = $app->site()->image();
        $panel = new File($file);

        $hash = $file->mediaHash();

        // cover disabled as default
        $this->assertSame([
            'ratio' => '3/2',
            'back' => 'pattern',
            'cover' => false,
            'url' => '/media/site/' . $hash . '/test.jpg',
            'cards' => [
                'url' => Model::imagePlaceholder(),
                'srcset' => '/media/site/' . $hash . '/test-352x.jpg 352w, /media/site/' . $hash . '/test-864x.jpg 864w, /media/site/' . $hash . '/test-1408x.jpg 1408w'
            ],
            'list' => [
                'url' => Model::imagePlaceholder(),
                'srcset' => '/media/site/' . $hash . '/test-38x.jpg 38w, /media/site/' . $hash . '/test-76x.jpg 76w'
            ]
        ], $panel->image());

        // cover enabled
        $this->assertSame([
            'ratio' => '3/2',
            'back' => 'pattern',
            'cover' => true,
            'url' => '/media/site/' . $hash . '/test.jpg',
            'cards' => [
                'url' => Model::imagePlaceholder(),
                'srcset' => '/media/site/' . $hash . '/test-352x.jpg 352w, /media/site/' . $hash . '/test-864x.jpg 864w, /media/site/' . $hash . '/test-1408x.jpg 1408w'
            ],
            'list' => [
                'url' => Model::imagePlaceholder(),
                'srcset' => '/media/site/' . $hash . '/test-38x38.jpg 1x, /media/site/' . $hash . '/test-76x76.jpg 2x'
            ]
        ], $panel->image(['cover' => true]));
    }

    /**
     * @covers ::imageSource
     */
    public function testImageStringQuery()
    {
        $page = new ModelPage([
            'slug'  => 'test',
            'files' => [
                ['filename' => 'test.jpg'],
                ['filename' => 'foo.pdf']
            ]
        ]);

        // fallback to model itself
        $image = (new File($page->file()))->image('foo.bar');
        $this->assertFalse(empty($image));
    }

    /**
     * @covers ::options
     */
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
        $this->assertSame($expected, $panel->options());
    }

    /**
     * @covers ::options
     */
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
        $this->assertSame($expected, $panel->options());

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
        $this->assertSame($expected, $panel->options(['delete']));
    }

    /**
     * @covers ::options
     */
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

    /**
     * @covers ::options
     */
    public function testOptionsAllowedReplaceOption()
    {
        $this->app->clone([
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

    /**
     * @covers ::options
     */
    public function testOptionsDisabledReplaceOption()
    {
        $this->app->clone([
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

    /**
     * @covers ::path
     */
    public function testPath()
    {
        $page = new ModelPage([
            'slug'  => 'test',
            'files' => [
                ['filename' => 'test.jpg']
            ]
        ]);

        $panel = new File($page->file('test.jpg'));
        $this->assertSame('files/test.jpg', $panel->path());
    }

    /**
     * @covers ::pickerData
     * @covers \Kirby\Panel\Model::pickerData
     */
    public function testPickerDataDefault()
    {
        $page = new ModelPage([
            'slug'  => 'test',
            'files' => [
                ['filename' => 'test.jpg']
            ]
        ]);

        $panel = new File($page->file('test.jpg'));
        $data  = $panel->pickerData();
        $this->assertSame('test.jpg', $data['filename']);
        $this->assertSame('(image: test.jpg)', $data['dragText']);
        $this->assertSame('test/test.jpg', $data['id']);
        $this->assertSame('3/2', $data['image']['ratio']);
        $this->assertSame('file-image', $data['icon']['type']);
        $this->assertSame('/pages/test/files/test.jpg', $data['link']);
        $this->assertSame('test.jpg', $data['text']);
    }

    /**
     * @covers ::pickerData
     */
    public function testPickerDataWithParams()
    {
        $page = new ModelPage([
            'slug'  => 'test',
            'files' => [
                [
                    'filename' => 'test.jpg',
                    'content' => [
                        'alt' => 'From foo to the bar'
                    ]
                ]
            ]
        ]);

        $panel = new File($page->file('test.jpg'));
        $data  = $panel->pickerData([
            'image' => [
                'ratio' => '1/1'
            ],
            'text' => '{{ file.alt }}'
        ]);

        $this->assertSame('test/test.jpg', $data['id']);
        $this->assertSame('1/1', $data['image']['ratio']);
        $this->assertSame('From foo to the bar', $data['text']);
    }

    /**
     * @covers ::pickerData
     */
    public function testPickerDataSameModel()
    {
        $page = new ModelPage([
            'slug'  => 'test',
            'files' => [
                ['filename' => 'test.jpg']
            ]
        ]);

        $panel = new File($page->file('test.jpg'));
        $data  = $panel->pickerData(['model' => $page]);

        $this->assertSame('(image: test.jpg)', $data['dragText']);
    }

    /**
     * @covers ::pickerData
     */
    public function testPickerDataDifferentModel()
    {
        $page = new ModelPage([
            'slug'  => 'test',
            'files' => [
                ['filename' => 'test.jpg']
            ]
        ]);

        $model = new ModelPage([
            'slug'  => 'foo'
        ]);

        $panel = new File($page->file('test.jpg'));
        $data  = $panel->pickerData(['model' => $model]);

        $this->assertSame('(image: test/test.jpg)', $data['dragText']);
    }

    /**
     * @covers ::url
     */
    public function testUrl()
    {
        $app = $this->app->clone([
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

        $this->assertSame('https://getkirby.com/panel/site/files/site-file.jpg', $panel->url());
        $this->assertSame('/site/files/site-file.jpg', $panel->url(true));

        // page file
        $file = $app->file('mother/child/page-file.jpg');
        $panel = new File($file);

        $this->assertSame('https://getkirby.com/panel/pages/mother+child/files/page-file.jpg', $panel->url());
        $this->assertSame('/pages/mother+child/files/page-file.jpg', $panel->url(true));

        // user file
        $user = $app->user('test@getkirby.com');
        $file = $user->file('user-file.jpg');
        $panel = new File($file);

        $this->assertSame('https://getkirby.com/panel/users/test/files/user-file.jpg', $panel->url());
        $this->assertSame('/users/test/files/user-file.jpg', $panel->url(true));
    }
}
