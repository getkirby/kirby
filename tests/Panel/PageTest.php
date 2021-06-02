<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Page as ModelPage;
use Kirby\Cms\Site as ModelSite;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\Str;
use PHPUnit\Framework\TestCase;

class ModelPageTestForceLocked extends ModelPage
{
    public function isLocked(): bool
    {
        return true;
    }
}

/**
 * @coversDefaultClass \Kirby\Panel\Page
 */
class PageTest extends TestCase
{
    protected $app;
    protected $fixtures;

    public function setUp(): void
    {
        $this->app = new App([
            'roots' => [
                'index' => $this->fixtures = __DIR__ . '/fixtures/PageTest',
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
    public function testBreadcrumb(): void
    {
        $site = new ModelSite([
            'children' => [
                [
                    'slug' => 'a',
                    'children' => [
                        [
                            'slug' => 'b',
                            'children' => [
                                ['slug' => 'c'],
                            ]
                        ],
                    ]
                ],
            ]
        ]);

        $page = new Page($site->page('a'));
        $this->assertSame([
            [
                'label' => 'a',
                'link'  => '/pages/a'
            ]
        ], $page->breadcrumb());

        $page = new Page($site->page('a/b/c'));
        $this->assertSame([
            [
                'label' => 'a',
                'link'  => '/pages/a'
            ],
            [
                'label' => 'b',
                'link'  => '/pages/a+b'
            ],
            [
                'label' => 'c',
                'link'  => '/pages/a+b+c'
            ]
        ], $page->breadcrumb());
    }

    /**
     * @covers ::dragText
     */
    public function testDragText()
    {
        $page = new ModelPage([
            'slug' => 'test'
        ]);

        $panel = new Page($page);
        $this->assertSame('(link: test text: test)', $panel->dragText());

        // with title
        $page = new ModelPage([
            'slug' => 'test',
            'content' => [
                'title' => 'Test Title'
            ]
        ]);

        $panel = new Page($page);
        $this->assertSame('(link: test text: Test Title)', $panel->dragText());
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
                        'slug' => 'a'
                    ],
                    [
                        'slug' => 'b',
                        'content' => [
                            'title' => 'Test Title'
                        ]
                    ]
                ]
            ]
        ]);

        $panel = new Page($app->page('a'));
        $this->assertSame('[a](/a)', $panel->dragText());

        $panel = new Page($app->page('b'));
        $this->assertSame('[Test Title](/b)', $panel->dragText());
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
                        'pageDragText' => function (\Kirby\Cms\Page $page) {
                            return sprintf('Links sind toll: %s', $page->url());
                        },
                    ]
                ]
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'content' => [
                            'title' => 'Test Title'
                        ]
                    ]
                ]
            ]
        ]);

        $panel = new Page($app->page('test'));
        $this->assertSame('Links sind toll: /test', $panel->dragText());
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
                        'pageDragText' => function (\Kirby\Cms\Page $page) {
                            return sprintf('Links sind toll: %s', $page->url());
                        },
                    ]
                ]
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'content' => [
                            'title' => 'Test Title'
                        ]
                    ]
                ]
            ]
        ]);

        $panel = new Page($app->page('test'));
        $this->assertSame('Links sind toll: /test', $panel->dragText());
    }

    /**
     * @covers ::icon
     */
    public function testIconFromBlueprint()
    {
        $page = new ModelPage([
            'slug' => 'test',
            'blueprint' => [
                'name' => 'test',
                'icon' => 'test'
            ]
        ]);

        $icon = (new Page($page))->icon();

        $this->assertSame([
            'type'  => 'test',
            'ratio' => null,
            'back'  => 'pattern',
            'color' => '#c5c9c6'
        ], $icon);
    }

    /**
     * @covers ::icon
     */
    public function testIconWithRatio()
    {
        $page = new ModelPage([
            'slug' => 'test'
        ]);

        $icon = (new Page($page))->icon(['ratio' => '3/2']);

        $this->assertSame([
            'type'  => 'page',
            'ratio' => '3/2',
            'back'  => 'pattern',
            'color' => '#c5c9c6'
        ], $icon);
    }

    /**
     * @covers ::icon
     */
    public function testIconWithEmoji()
    {
        $page = new ModelPage([
            'slug' => 'test',
            'blueprint' => [
                'name' => 'test',
                'icon' => $emoji = '❤️'
            ]
        ]);

        $icon = (new Page($page))->icon();

        $this->assertSame($emoji, $icon['type']);
        $this->assertSame('pattern', $icon['back']);
        $this->assertSame(null, $icon['ratio']);
    }

    /**
     * @covers ::id
     */
    public function testId()
    {
        $parent = new ModelPage(['slug' => 'foo']);
        $page   = new ModelPage([
            'slug'   => 'bar',
            'parent' => $parent
        ]);

        $id = (new Page($page))->id();
        $this->assertSame('foo+bar', $id);
    }

    /**
     * @covers ::imageSource
     */
    public function testImage()
    {
        $page = new ModelPage([
            'slug'  => 'test',
            'files' => [
                ['filename' => 'test.jpg']
            ]
        ]);

        // fallback to model itself
        $image = (new Page($page))->image();
        $this->assertTrue(Str::endsWith($image['url'], '/test.jpg'));
    }

    /**
     * @covers ::imageSource
     */
    public function testImageCover()
    {
        $app = $this->app->clone([
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

        $page  = $app->page('test');
        $panel = new Page($page);

        $hash = $page->image()->mediaHash();
        $mediaUrl = $page->mediaUrl() . '/' . $hash;

        // cover disabled as default
        $this->assertSame([
            'ratio' => '3/2',
            'back' => 'pattern',
            'cover' => false,
            'url' => $mediaUrl . '/test.jpg',
            'cards' => [
                'url' => Model::imagePlaceholder(),
                'srcset' => $mediaUrl . '/test-352x.jpg 352w, ' . $mediaUrl . '/test-864x.jpg 864w, ' . $mediaUrl . '/test-1408x.jpg 1408w'
            ],
            'list' => [
                'url' => Model::imagePlaceholder(),
                'srcset' => $mediaUrl . '/test-38x.jpg 38w, ' . $mediaUrl . '/test-76x.jpg 76w'
            ]
        ], $panel->image());

        // cover enabled
        $this->assertSame([
            'ratio' => '3/2',
            'back' => 'pattern',
            'cover' => true,
            'url' => $mediaUrl . '/test.jpg',
            'cards' => [
                'url' => Model::imagePlaceholder(),
                'srcset' => $mediaUrl . '/test-352x.jpg 352w, ' . $mediaUrl . '/test-864x.jpg 864w, ' . $mediaUrl . '/test-1408x.jpg 1408w'
            ],
            'list' => [
                'url' => Model::imagePlaceholder(),
                'srcset' => $mediaUrl . '/test-38x38.jpg 1x, ' . $mediaUrl . '/test-76x76.jpg 2x'
            ]
        ], $panel->image(['cover' => true]));
    }

    /**
     * @covers \Kirby\Panel\Model::options
     */
    public function testOptions()
    {
        $page = new ModelPage([
            'slug' => 'test',
        ]);

        $page->kirby()->impersonate('kirby');

        $expected = [
            'changeSlug'     => true,
            'changeStatus'   => true,
            'changeTemplate' => false, // no other template available in this scenario
            'changeTitle'    => true,
            'create'         => true,
            'delete'         => true,
            'duplicate'      => true,
            'read'           => true,
            'preview'        => true,
            'sort'           => false, // drafts cannot be sorted
            'update'         => true,
        ];

        $panel = new Page($page);
        $this->assertSame($expected, $panel->options());
    }

    /**
     * @covers \Kirby\Panel\Model::options
     */
    public function testOptionsWithLockedPage()
    {
        $page = new ModelPageTestForceLocked([
            'slug' => 'test',
        ]);

        $page->kirby()->impersonate('kirby');

        // without override
        $expected = [
            'changeSlug'     => false,
            'changeStatus'   => false,
            'changeTemplate' => false,
            'changeTitle'    => false,
            'create'         => false,
            'delete'         => false,
            'duplicate'      => false,
            'read'           => false,
            'preview'        => false,
            'sort'           => false,
            'update'         => false,
        ];

        $panel = new Page($page);
        $this->assertSame($expected, $panel->options());

        // with override
        $expected = [
            'changeSlug'     => false,
            'changeStatus'   => false,
            'changeTemplate' => false,
            'changeTitle'    => false,
            'create'         => false,
            'delete'         => false,
            'duplicate'      => false,
            'read'           => false,
            'preview'        => true,
            'sort'           => false,
            'update'         => false,
        ];

        $panel = new Page($page);
        $this->assertSame($expected, $panel->options(['preview']));
    }

    /**
     * @covers ::path
     */
    public function testPath()
    {
        $page = new ModelPage([
            'slug'  => 'test'
        ]);

        $panel = new Page($page);
        $this->assertSame('pages/test', $panel->path());
    }

    /**
     * @covers ::pickerData
     * @covers \Kirby\Panel\Model::pickerData
     */
    public function testPickerDataDefault()
    {
        $page = new ModelPage([
            'slug' => 'test',
            'content' => [
                'title' => 'Test Title'
            ]
        ]);

        $panel = new Page($page);
        $data  = $panel->pickerData();

        $this->assertSame('(link: test text: Test Title)', $data['dragText']);
        $this->assertSame('test', $data['id']);
        $this->assertSame('/pages/test', $data['link']);
        $this->assertSame('Test Title', $data['text']);
    }

    /**
     * @covers ::props
     */
    public function testProps()
    {
        $page = new ModelPage([
            'slug'  => 'test'
        ]);

        $panel = new Page($page);
        $props = $panel->props();

        $this->assertArrayHasKey('model', $props);
        $this->assertArrayHasKey('content', $props['model']);
        $this->assertArrayHasKey('id', $props['model']);
        $this->assertArrayHasKey('parent', $props['model']);
        $this->assertArrayHasKey('previewUrl', $props['model']);
        $this->assertArrayHasKey('status', $props['model']);
        $this->assertArrayHasKey('title', $props['model']);

        // inherited props
        $this->assertArrayHasKey('blueprint', $props);
        $this->assertArrayHasKey('lock', $props);
        $this->assertArrayHasKey('permissions', $props);
        $this->assertArrayHasKey('tab', $props);
        $this->assertArrayHasKey('tabs', $props);
    }

    /**
     * @covers ::route
     */
    public function testRoute()
    {
        $page = new ModelPage([
            'slug'  => 'test',
        ]);

        $panel = new Page($page);
        $route = $panel->route();

        $this->assertArrayHasKey('props', $route);
        $this->assertSame('k-page-view', $route['component']);
        $this->assertSame('test', $route['title']);
        $this->assertSame('test', $route['breadcrumb']()[0]['label']);
    }

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
                                'slug' => 'child'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $page  = $app->page('mother/child');
        $panel = new Page($page);

        $this->assertSame('https://getkirby.com/panel/pages/mother+child', $panel->url());
        $this->assertSame('/pages/mother+child', $panel->url(true));
    }
}
