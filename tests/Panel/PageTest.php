<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\Page as ModelPage;
use PHPUnit\Framework\TestCase;

class ModelPageTestForceLocked extends ModelPage
{
    public function isLocked(): bool
    {
        return true;
    }
}

class PageTest extends TestCase
{
    public function testDragText()
    {
        $page = new ModelPage([
            'slug' => 'test'
        ]);

        $panel = new Page($page);
        $this->assertEquals('(link: test text: test)', $panel->dragText());

        // with title
        $page = new ModelPage([
            'slug' => 'test',
            'content' => [
                'title' => 'Test Title'
            ]
        ]);

        $panel = new Page($page);
        $this->assertEquals('(link: test text: Test Title)', $panel->dragText());
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
        $this->assertEquals('[a](/a)', $panel->dragText());

        $panel = new Page($app->page('b'));
        $this->assertEquals('[Test Title](/b)', $panel->dragText());
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
        $this->assertEquals('Links sind toll: /test', $panel->dragText());
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
        $this->assertEquals('Links sind toll: /test', $panel->dragText());
    }

    public function testIconDefault()
    {
        $page = new ModelPage([
            'slug' => 'test'
        ]);

        $icon = (new Page($page))->icon();

        $this->assertEquals([
            'type'  => 'page',
            'back'  => 'pattern',
            'ratio' => null,
            'color' => '#c5c9c6'
        ], $icon);
    }

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

        $this->assertEquals([
            'type'  => 'test',
            'back'  => 'pattern',
            'ratio' => null,
            'color' => '#c5c9c6'
        ], $icon);
    }

    public function testIconWithRatio()
    {
        $page = new ModelPage([
            'slug' => 'test'
        ]);

        $icon = (new Page($page))->icon(['ratio' => '3/2']);

        $this->assertEquals([
            'type'  => 'page',
            'back'  => 'pattern',
            'ratio' => '3/2',
            'color' => '#c5c9c6'
        ], $icon);
    }

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

        $this->assertEquals($emoji, $icon['type']);
        $this->assertEquals('pattern', $icon['back']);
        $this->assertEquals(null, $icon['ratio']);
    }

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
        $this->assertEquals($expected, $panel->options());
    }

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
        $this->assertEquals($expected, $panel->options());

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
        $this->assertEquals($expected, $panel->options(['preview']));
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
                                'slug' => 'child'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $page  = $app->page('mother/child');
        $panel = new Page($page);

        $this->assertEquals('https://getkirby.com/panel/pages/mother+child', $panel->url());
        $this->assertEquals('/pages/mother+child', $panel->url(true));
    }
}
