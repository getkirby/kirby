<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\File as ModelFile;
use Kirby\Cms\Page as ModelPage;
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
    public function tearDown(): void
    {
        Dir::remove(__DIR__ . '/tmp');
    }

    /**
     * @covers ::dragText
     * @covers \Kirby\Panel\Model::dragTextType
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
     * @covers \Kirby\Panel\Model::dragTextType
     */
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

    /**
     * @covers ::dragText
     * @covers \Kirby\Panel\Model::dragTextFromCallback
     */
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
        $this->assertSame('![](test.jpg)', $panel->dragText());

        // Custom function should return image tag for heic
        $panel = new File($app->page('test')->file('test.heic'));
        $this->assertSame('![](test.heic)', $panel->dragText());
    }

    /**
     * @covers ::dragText
     * @covers \Kirby\Panel\Model::dragTextFromCallback
     */
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
        $this->assertSame('(image: test.jpg)', $panel->dragText());

        // Custom function should return image tag for heic
        $panel = new File($app->page('test')->file('test.heic'));
        $this->assertSame('(image: test.heic)', $panel->dragText());
    }

    /**
     * @covers ::icon
     * @covers \Kirby\Panel\Model::icon
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
     * @covers \Kirby\Panel\Model::icon
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
     * @covers \Kirby\Panel\Model::image
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
     * @covers \Kirby\Panel\Model::image
     * @covers \Kirby\Panel\Model::imageSource
     */
    public function testImageCover()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null',
                'media' => __DIR__ . '/tmp'
            ],
            'site' => [
                'files' => [
                    ['filename' => 'test.jpg']
                ]
            ]
        ]);

        $file  = $app->site()->image();
        $panel = new File($file);

        $hash = $file->mediaHash();
        $imagePlaceholder = 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw';

        // cover disabled as default
        $this->assertSame([
            'ratio' => '3/2',
            'back' => 'pattern',
            'cover' => false,
            'url' => '/media/site/' . $hash . '/test.jpg',
            'cards' => [
                'url' => $imagePlaceholder,
                'srcset' => '/media/site/' . $hash . '/test-352x.jpg 352w, /media/site/' . $hash . '/test-864x.jpg 864w, /media/site/' . $hash . '/test-1408x.jpg 1408w'
            ],
            'list' => [
                'url' => $imagePlaceholder,
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
                'url' => $imagePlaceholder,
                'srcset' => '/media/site/' . $hash . '/test-352x.jpg 352w, /media/site/' . $hash . '/test-864x.jpg 864w, /media/site/' . $hash . '/test-1408x.jpg 1408w'
            ],
            'list' => [
                'url' => $imagePlaceholder,
                'srcset' => '/media/site/' . $hash . '/test-38x38.jpg 1x, /media/site/' . $hash . '/test-76x76.jpg 2x'
            ]
        ], $panel->image(['cover' => true]));
    }

    /**
     * @covers \Kirby\Panel\Model::image
     */
    public function testImageDeactivated()
    {
        $file = new ModelFile([
            'filename' => 'something.jpg'
        ]);

        $image = (new File($file))->image(false);

        $this->assertNull($image);
    }

    /**
     * @covers \Kirby\Panel\Model::image
     */
    public function testImageStringIcon()
    {
        $file = new ModelFile([
            'filename' => 'something.jpg'
        ]);

        $image = (new File($file))->image('icon');

        $this->assertSame([], $image);
    }

    /**
     * @covers ::imageSource
     * @covers \Kirby\Panel\Model::image
     * @covers \Kirby\Panel\Model::imageSource
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
     * @covers \Kirby\Panel\Model::options
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
     * @covers \Kirby\Panel\Model::options
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
     * @covers \Kirby\Panel\Model::options
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
     * @covers \Kirby\Panel\Model::options
     */
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

    /**
     * @covers ::options
     * @covers \Kirby\Panel\Model::options
     */
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

    /**
     * @covers ::path
     * @covers \Kirby\Panel\Model::__construct
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
     * @covers \Kirby\Panel\Model::url
     */
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
