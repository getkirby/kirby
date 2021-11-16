<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\File as ModelFile;
use Kirby\Cms\Page as ModelPage;
use Kirby\Cms\Site as ModelSite;
use Kirby\Cms\User as ModelUser;
use Kirby\Filesystem\Dir;
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
    protected $tmp = __DIR__ . '/tmp';

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => $this->tmp,
            ]
        ]);

        Dir::make($this->tmp);
    }

    public function tearDown(): void
    {
        Dir::remove($this->tmp);
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
     * @covers ::dropdownOption
     */
    public function testDropdownOption()
    {
        $page = new ModelPage([
            'slug' => 'test',
            'files' => [
                ['filename' => 'test.jpg'],
            ]
        ]);

        $panel  = new File($page->file());
        $option = $panel->dropdownOption();

        $this->assertSame('image', $option['icon']);
        $this->assertSame('test.jpg', $option['text']);
        $this->assertSame('/panel/pages/test/files/test.jpg', $option['link']);
    }

    /**
     * @covers ::imageDefaults
     * @covers ::imageColor
     * @covers ::imageIcon
     * @covers ::imageSource
     */
    public function testImage()
    {
        $page = new ModelPage([
            'slug' => 'test'
        ]);

        $file = new ModelFile([
            'filename' => 'something.jpg',
            'parent'   => $page
        ]);

        $image = (new File($file))->image();
        $this->assertSame('file-image', $image['icon']);
        $this->assertSame('orange-400', $image['color']);
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
            'back' => 'pattern',
            'color' => 'orange-400',
            'cover' => false,
            'icon' => 'file-image',
            'ratio' => '3/2',
            'url' => '/media/site/' . $hash . '/test.jpg',
            'src' => Model::imagePlaceholder(),
            'srcset' => '/media/site/' . $hash . '/test-38x.jpg 38w, /media/site/' . $hash . '/test-76x.jpg 76w'
        ], $panel->image());

        // cover enabled
        $this->assertSame([
            'back' => 'pattern',
            'color' => 'orange-400',
            'cover' => true,
            'icon' => 'file-image',
            'ratio' => '3/2',
            'url' => '/media/site/' . $hash . '/test.jpg',
            'src' => Model::imagePlaceholder(),
            'srcset' => '/media/site/' . $hash . '/test-38x38-crop.jpg 1x, /media/site/' . $hash . '/test-76x76-crop.jpg 2x'
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
        $page = new ModelPage([
            'slug' => 'test'
        ]);

        $file = new ModelFile([
            'filename' => 'test.jpg',
            'parent'   => $page
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
        $page = new ModelPage([
            'slug' => 'test'
        ]);

        $file = new ModelFileTestForceLocked([
            'filename' => 'test.jpg',
            'parent'   => $page
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
        $page = new ModelPage([
            'slug' => 'test'
        ]);

        $file = new ModelFile([
            'filename' => 'test.js',
            'parent'   => $page
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

        $page = new ModelPage([
            'slug' => 'test'
        ]);

        $file = new ModelFile([
            'filename' => 'test.js',
            'parent'   => $page,
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

        $page = new ModelPage([
            'slug' => 'test'
        ]);

        $file = new ModelFile([
            'filename' => 'test.js',
            'parent'   => $page,
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
        $this->assertSame('file-image', $data['image']['icon']);
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
     * @covers ::props
     */
    public function testProps()
    {
        $page = new ModelPage([
            'slug'  => 'test',
            'files' => [
                ['filename' => 'test.jpg']
            ]
        ]);

        $panel = new File($page->file('test.jpg'));
        $props = $panel->props();

        $this->assertArrayHasKey('model', $props);
        $this->assertArrayHasKey('content', $props['model']);
        $this->assertArrayHasKey('dimensions', $props['model']);
        $this->assertArrayHasKey('extension', $props['model']);
        $this->assertArrayHasKey('filename', $props['model']);
        $this->assertArrayHasKey('id', $props['model']);
        $this->assertArrayHasKey('mime', $props['model']);
        $this->assertArrayHasKey('niceSize', $props['model']);
        $this->assertArrayHasKey('parent', $props['model']);
        $this->assertArrayHasKey('url', $props['model']);
        $this->assertArrayHasKey('template', $props['model']);
        $this->assertArrayHasKey('type', $props['model']);

        $this->assertArrayHasKey('preview', $props);
        $this->assertArrayHasKey('image', $props['preview']);
        $this->assertArrayHasKey('url', $props['preview']);
        $this->assertArrayHasKey('details', $props['preview']);
        $this->assertCount(6, $props['preview']['details']);

        // inherited props
        $this->assertArrayHasKey('blueprint', $props);
        $this->assertArrayHasKey('lock', $props);
        $this->assertArrayHasKey('permissions', $props);
        $this->assertArrayNotHasKey('tab', $props);
        $this->assertArrayHasKey('tabs', $props);
    }

    /**
     * @covers ::props
     */
    public function testPropsPrevNext()
    {
        $page = new ModelPage([
            'slug'  => 'test',
            'files' => [
                ['filename' => 'a.jpg'],
                ['filename' => 'b.jpg'],
                ['filename' => 'c.jpg']
            ]
        ]);

        $props = (new File($page->file('a.jpg')))->props();
        $this->assertNull($props['prev']());
        $this->assertSame('/pages/test/files/b.jpg', $props['next']()['link']);

        $props = (new File($page->file('b.jpg')))->props();
        $this->assertSame('/pages/test/files/a.jpg', $props['prev']()['link']);
        $this->assertSame('/pages/test/files/c.jpg', $props['next']()['link']);

        $props = (new File($page->file('c.jpg')))->props();
        $this->assertSame('/pages/test/files/b.jpg', $props['prev']()['link']);
        $this->assertNull($props['next']());
    }

    /**
     * @covers ::props
     */
    public function testPropsPrevNextWithSort()
    {
        $page = new ModelPage([
            'slug'  => 'test',
            'files' => [
                ['filename' => 'a.jpg', 'content' => ['sort' => 2]],
                ['filename' => 'b.jpg', 'content' => ['sort' => 1]],
                ['filename' => 'c.jpg', 'content' => ['sort' => 3]]
            ]
        ]);

        $props = (new File($page->file('a.jpg')))->props();
        $this->assertSame('/pages/test/files/b.jpg', $props['prev']()['link']);
        $this->assertSame('/pages/test/files/c.jpg', $props['next']()['link']);

        $props = (new File($page->file('b.jpg')))->props();
        $this->assertNull($props['prev']());
        $this->assertSame('/pages/test/files/a.jpg', $props['next']()['link']);

        $props = (new File($page->file('c.jpg')))->props();
        $this->assertSame('/pages/test/files/a.jpg', $props['prev']()['link']);
        $this->assertNull($props['next']());
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

    /**
     * @covers ::prevNext
     */
    public function testPrevNext()
    {
        $page = new ModelPage([
            'slug'  => 'test',
            'files' => [
                ['filename' => 'a.jpg'],
                ['filename' => 'b.jpg'],
                ['filename' => 'c.jpg']
            ]
        ]);

        $prevNext = (new File($page->file('a.jpg')))->prevNext();
        $this->assertNull($prevNext['prev']());
        $this->assertSame('/pages/test/files/b.jpg', $prevNext['next']()['link']);

        $prevNext = (new File($page->file('b.jpg')))->prevNext();
        $this->assertSame('/pages/test/files/a.jpg', $prevNext['prev']()['link']);
        $this->assertSame('/pages/test/files/c.jpg', $prevNext['next']()['link']);

        $prevNext = (new File($page->file('c.jpg')))->prevNext();
        $this->assertSame('/pages/test/files/b.jpg', $prevNext['prev']()['link']);
        $this->assertNull($prevNext['next']());
    }

    /**
     * @covers ::view
     */
    public function testView()
    {
        $page = new ModelPage([
            'slug'  => 'test',
            'files' => [
                ['filename' => 'test.jpg']
            ]
        ]);

        $panel = new File($page->file('test.jpg'));
        $view  = $panel->view();

        $this->assertArrayHasKey('props', $view);
        $this->assertSame('k-file-view', $view['component']);
        $this->assertSame('test.jpg', $view['title']);
        $this->assertSame('files', $view['search']);
        $breadcrumb = $view['breadcrumb']();
        $this->assertSame('test', $breadcrumb[0]['label']);
        $this->assertSame('test.jpg', $breadcrumb[1]['label']);
    }
}
