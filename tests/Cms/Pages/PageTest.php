<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;
use ReflectionMethod;

class PageTestModel extends Page
{
}

class PageTestForceLocked extends Page
{
    public function isLocked(): bool
    {
        return true;
    }
}


class PageTest extends TestCase
{
    /**
     * Deregister any plugins for the page
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Dir::remove(__DIR__ . '/fixtures/PageTest');
    }

    public function testBlueprints()
    {
        new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'blueprints' => [
                'pages/a' => [
                    'title' => 'A'
                ],
                'pages/b' => [
                    'title' => 'B'
                ],
                'pages/c' => [
                    'title' => 'C'
                ]
            ],
            'templates' => [
                'a' => __FILE__,
                'c' => __FILE__
            ]
        ]);

        // no blueprints
        $page = new Page(['slug' => 'test', 'template' => 'a']);

        $this->assertEquals(['A'], array_column($page->blueprints(), 'title'));

        // two different blueprints
        $page = new Page([
            'slug' => 'test',
            'template' => 'c',
            'blueprint' => [
                'options' => [
                    'template' => [
                        'a',
                        'b'
                    ]
                ]
            ]
        ]);

        $this->assertEquals(['C', 'A', 'B'], array_column($page->blueprints(), 'title'));

        // including the same blueprint
        $page = new Page([
            'slug' => 'test',
            'template' => 'a',
            'blueprint' => [
                'options' => [
                    'template' => [
                        'a',
                        'b'
                    ]
                ]
            ]
        ]);

        $this->assertEquals(['A', 'B'], array_column($page->blueprints(), 'title'));

        // template option is simply true
        $page = new Page([
            'slug' => 'test',
            'template' => 'a',
            'blueprint' => [
                'options' => [
                    'template' => true
                ]
            ]
        ]);

        $this->assertEquals(['A'], array_column($page->blueprints(), 'title'));
    }

    public function testDepth()
    {
        $site = new Site([
            'children' => [
                [
                    'slug' => 'grandma',
                    'children' => [
                        [
                            'slug' => 'mother',
                            'children' => [
                                [
                                    'slug' => 'child',
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertEquals(1, $site->find('grandma')->depth());
        $this->assertEquals(2, $site->find('grandma/mother')->depth());
        $this->assertEquals(3, $site->find('grandma/mother/child')->depth());
    }


    public function testDragText()
    {
        $page = new Page([
            'slug' => 'test'
        ]);

        $this->assertEquals('(link: test text: test)', $page->dragText());
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
                        'slug' => 'test'
                    ]
                ]
            ]
        ]);

        $page = $app->page('test');
        $this->assertEquals('[test](/test)', $page->dragText());
    }

    public function testDragTextWithTitle()
    {
        $page = new Page([
            'slug' => 'test',
            'content' => [
                'title' => 'Test Title'
            ]
        ]);

        $this->assertEquals('(link: test text: Test Title)', $page->dragText());
    }

    public function testDragTextWithTitleMarkdown()
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
                        'content' => [
                            'title' => 'Test Title'
                        ]
                    ]
                ]
            ]
        ]);

        $page = $app->page('test');
        $this->assertEquals('[Test Title](/test)', $page->dragText());
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

        $page = $app->page('test');
        $this->assertEquals('Links sind toll: /test', $page->dragText());
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

        $page = $app->page('test');
        $this->assertEquals('Links sind toll: /test', $page->dragText());
    }



    public function testId()
    {
        $page = new Page([
            'slug' => 'test'
        ]);

        $this->assertEquals('test', $page->id());
    }

    public function testEmptyId()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('The property "slug" is required');

        $page = new Page(['slug' => null]);
    }

    public function testErrors()
    {
        $page = new Page([
            'slug' => 'test',
            'blueprint' => [
                'name' => 'test',
                'fields' => [
                    'intro' => [
                        'required' => true,
                        'type'     => 'text'
                    ]
                ]
            ]
        ]);

        $this->assertEquals([
            'intro' => [
                'label' => 'Intro',
                'message' => [
                    'required' => 'Please enter something'
                ]
            ]
        ], $page->errors());
    }

    public function testErrorsWithoutBlueprint()
    {
        $page = new Page(['slug' => 'test']);
        $this->assertEquals([], $page->errors());
    }

    public function testErrorsWithInfoSectionInBlueprint()
    {
        $page = new Page([
            'slug' => 'test',
            'blueprint' => [
                'name' => 'test',
                'sections' => [
                    'info' => [
                        'type'     => 'info',
                        'headline' => 'Info',
                        'text'     => 'info'
                    ]
                ]
            ]
        ]);

        $this->assertEquals([], $page->errors());
    }

    public function testInvalidId()
    {
        $this->expectException('TypeError');

        $page = new Page([
            'slug' => []
        ]);
    }

    public function testNum()
    {
        $page = new Page([
            'slug'  => 'test',
            'num' => 1
        ]);

        $this->assertEquals(1, $page->num());
    }

    public function testInvalidNum()
    {
        $this->expectException('TypeError');

        $page = new Page([
            'slug'  => 'test',
            'num' => []
        ]);
    }

    public function testEmptyNum()
    {
        $page = new Page([
            'slug'  => 'test',
            'num' => null
        ]);

        $this->assertNull($page->num());
    }

    public function testParent()
    {
        $parent = new Page([
            'slug' => 'test'
        ]);

        $page = new Page([
            'slug'     => 'test/child',
            'parent' => $parent
        ]);

        $this->assertEquals($parent, $page->parent());
    }

    public function testParentId()
    {
        $mother = new Page([
            'slug' => 'mother',
            'children' => [
                [
                    'slug' => 'child'
                ]
            ]
        ]);

        $this->assertEquals(null, $mother->parentId());
        $this->assertEquals('mother', $mother->find('child')->parentId());
    }

    public function testParentPrevNext()
    {
        $app = new App([
            'site' => [
                'children' => [
                    [
                        'slug' => 'projects',
                        'children' => [
                            [
                                'slug' => 'project-a',
                            ],
                            [
                                'slug' => 'project-b',
                            ]
                        ]
                    ],
                    [
                        'slug' => 'blog'
                    ]
                ]
            ]
        ]);

        $child = $app->page('projects/project-a');
        $blog  = $app->page('blog');

        $this->assertEquals($blog, $child->parent()->next());
        $this->assertEquals(null, $child->parent()->prev());
    }

    public function testInvalidParent()
    {
        $this->expectException('TypeError');

        $page = new Page([
            'slug'     => 'test/child',
            'parent' => 'some parent'
        ]);
    }

    public function testSite()
    {
        $site = new Site();
        $page = new Page([
            'slug'   => 'test',
            'site' => $site
        ]);

        $this->assertEquals($site, $page->site());
    }

    public function testInvalidSite()
    {
        $this->expectException('TypeError');

        $page = new Page([
            'slug'   => 'test',
            'site' => 'mysite'
        ]);
    }

    public function testDefaultTemplate()
    {
        $page = new Page([
            'slug' => 'test',
        ]);

        $this->assertEquals('default', $page->template());
    }

    public function testIntendedTemplate()
    {
        $page = new Page([
            'slug'     => 'test',
            'template' => 'testTemplate'
        ]);

        $this->assertEquals('testtemplate', $page->intendedTemplate()->name());
    }

    public function testInvalidTemplate()
    {
        $this->expectException('TypeError');

        $page = new Page([
            'slug'       => 'test',
            'template' => []
        ]);
    }

    public function testUrl()
    {
        $page = new Page([
            'slug'  => 'test',
            'url' => 'https://getkirby.com/test'
        ]);

        $this->assertEquals('https://getkirby.com/test', $page->url());
    }

    public function testUrlWithOptions()
    {
        $page = new Page([
            'slug'  => 'test',
            'url' => 'https://getkirby.com/test'
        ]);

        $this->assertEquals('https://getkirby.com/test/foo:bar?q=search', $page->url([
            'params' => 'foo:bar',
            'query'  => 'q=search'
        ]));
    }

    public function testDefaultUrl()
    {
        $page = new Page([
            'slug' => 'test'
        ]);

        $this->assertEquals('/test', $page->url());
    }

    public function testInvalidUrl()
    {
        $this->expectException('TypeError');

        $page = new Page([
            'slug'  => 'test',
            'url' => []
        ]);
    }

    public function testHomeUrl()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'children' => [
                    ['slug' => 'home']
                ]
            ]
        ]);

        $this->assertEquals('/', $app->site()->find('home')->url());
    }

    public function testHomeChildUrl()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'home',
                        'children' => [
                            ['slug' => 'a']
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertEquals('/home/a', $app->site()->find('home/a')->url());
    }

    public function testMultiLangHomeChildUrl()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => [
                'languages' => true
            ],
            'languages' => [
                [
                    'code'    => 'en',
                    'default' => true,
                ],
                [
                    'code'    => 'de',
                ]
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'home',
                        'children' => [
                            ['slug' => 'a']
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertEquals('/en/home/a', $app->site()->find('home/a')->url());
        $this->assertEquals('/de/home/a', $app->site()->find('home/a')->url('de'));
    }

    public function testPreviewUrl()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'urls' => [
                'index' => '/'
            ]
        ]);

        $page = new Page([
            'slug' => 'test'
        ]);

        $this->assertEquals('/test', $page->previewUrl());
    }

    public function previewUrlProvider()
    {
        return [
            [null, '/test', false],
            [null, '/test?{token}', true],
            [true, '/test', false],
            [true, '/test?{token}', true],
            ['/something/different', '/something/different', false],
            ['/something/different', '/something/different?{token}', true],
            ['{{ site.url }}#{{ page.slug }}', '/#test', false],
            ['{{ site.url }}#{{ page.slug }}', '/?{token}#test', true],
            ['{{ page.url }}?preview=true', '/test?preview=true&{token}', true],
            [false, null, false],
            [false, null, true],
        ];
    }

    /**
     * @dataProvider previewUrlProvider
     */
    public function testCustomPreviewUrl($input, $expected, $draft)
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'urls' => [
                'index' => '/'
            ]
        ]);

        $options = [];

        if ($input !== null) {
            $options = [
                'preview' => $input
            ];
        }

        // simple
        $page = new Page([
            'slug' => 'test',
            'isDraft' => $draft,
            'blueprint' => [
                'name'    => 'test',
                'options' => $options
            ]
        ]);

        if ($draft === true && $expected !== null) {
            $expected = str_replace('{token}', 'token=' . hash_hmac('sha1', $page->id() . $page->template(), $page->root()), $expected);
        }

        $this->assertEquals($expected, $page->previewUrl());
    }

    public function testSlug()
    {
        $page = new Page(['slug' => 'test']);
        $this->assertEquals('test', $page->slug());
    }

    public function testToken()
    {
        $page = new Page([
            'slug'     => 'test',
            'root'     => '/var/www/content/test',
            'template' => 'default'
        ]);

        $method = new ReflectionMethod('Kirby\Cms\Page', 'token');
        $method->setAccessible(true);

        $expected = hash_hmac('sha1', 'test' . 'default', '/var/www/content/test');
        $this->assertSame($expected, $method->invoke($page));
    }

    public function testTokenWithCustomSalt()
    {
        new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => [
                'content' => [
                    'salt' => 'testsalt'
                ]
            ]
        ]);

        $page = new Page([
            'slug'     => 'test',
            'template' => 'default'
        ]);

        $method = new ReflectionMethod('Kirby\Cms\Page', 'token');
        $method->setAccessible(true);

        $expected = hash_hmac('sha1', 'test' . 'default', 'testsalt');
        $this->assertSame($expected, $method->invoke($page));
    }

    public function testTokenWithSaltCallback()
    {
        new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => [
                'content' => [
                    'salt' => function ($page) {
                        return $page->date();
                    }
                ]
            ]
        ]);

        $page = new Page([
            'slug'     => 'test',
            'template' => 'default',
            'content'  => [
                'date' => '2012-12-12'
            ]
        ]);

        $method = new ReflectionMethod('Kirby\Cms\Page', 'token');
        $method->setAccessible(true);

        $expected = hash_hmac('sha1', 'test' . 'default', '2012-12-12');
        $this->assertSame($expected, $method->invoke($page));
    }

    public function testToString()
    {
        $page = new Page(['slug' => 'test']);
        $this->assertEquals('test', $page->toString('{{ page.slug }}'));
    }

    public function testUid()
    {
        $page = new Page(['slug' => 'test']);
        $this->assertEquals('test', $page->uid());
    }

    public function testUri()
    {
        $site = new Site([
            'children' => [
                [
                    'slug' => 'grandma',
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
            ]
        ]);

        $this->assertEquals('grandma/mother/child', $site->find('grandma/mother/child')->uri());
    }

    public function testUriTranslated()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'languages' => [
                [
                    'code' => 'en'
                ],
                [
                    'code' => 'de'
                ],
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'grandma',
                        'translations' => [
                            [
                                'code' => 'en',
                            ],
                            [
                                'code' => 'de',
                                'slug' => 'oma'
                            ],
                        ],
                        'children' => [
                            [
                                'slug' => 'mother',
                                'translations' => [
                                    [
                                        'code' => 'en'
                                    ],
                                    [
                                        'code' => 'de',
                                        'slug' => 'mutter'
                                    ],
                                ],
                            ]
                        ]
                    ]
                ]
            ]
        ]);


        $this->assertEquals('grandma/mother', $app->site()->find('grandma/mother')->uri());
        $this->assertEquals('oma/mutter', $app->site()->find('grandma/mother')->uri('de'));
    }

    public function testModified()
    {
        $app = new App([
            'roots' => [
                'index'   => $index = __DIR__ . '/fixtures/PageTest/modified',
                'content' => $index
            ]
        ]);

        // create a page
        F::write($file = $index . '/test/test.txt', 'test');

        $modified = filemtime($file);
        $page     = $app->page('test');

        $this->assertEquals($modified, $page->modified());

        // default date handler
        $format = 'd.m.Y';
        $this->assertEquals(date($format, $modified), $page->modified($format));

        // custom date handler without format
        $this->assertEquals($modified, $page->modified(null, 'strftime'));

        // custom date handler with format
        $format = '%d.%m.%Y';
        $this->assertEquals(strftime($format, $modified), $page->modified($format, 'strftime'));
    }

    public function testModifiedInMultilangInstallation()
    {
        $app = new App([
            'roots' => [
                'index'   => $index = __DIR__ . '/fixtures/PageTest/modified',
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

        // create the english page
        F::write($file = $index . '/test/test.en.txt', 'test');
        touch($file, $modified = \time() + 2);

        $this->assertEquals($modified, $app->page('test')->modified());

        // create the german page
        F::write($file = $index . '/test/test.de.txt', 'test');
        touch($file, $modified = \time() + 5);

        // change the language
        $app->setCurrentLanguage('de');
        $app->setCurrentTranslation('de');

        $this->assertEquals($modified, $app->page('test')->modified());
    }

    public function testModifiedSpecifyingLanguage()
    {
        $app = new App([
            'roots' => [
                'index'   => $index = __DIR__ . '/fixtures/PageTest/modified',
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

        // create the english page
        F::write($file = $index . '/test/test.en.txt', 'test');
        touch($file, $modifiedEnContent = \time() + 2);

        // create the german page
        F::write($file = $index . '/test/test.de.txt', 'test');
        touch($file, $modifiedDeContent = \time() + 5);

        $page = $app->page('test');

        $this->assertEquals($modifiedEnContent, $page->modified(null, null, 'en'));
        $this->assertEquals($modifiedDeContent, $page->modified(null, null, 'de'));
    }

    public function testPanelIconDefault()
    {
        $page = new Page([
            'slug' => 'test'
        ]);

        $icon     = $page->panelIcon();
        $expected = [
            'type'  => 'page',
            'back'  => 'pattern',
            'ratio' => null,
            'color' => '#c5c9c6'
        ];

        $this->assertEquals($expected, $icon);
    }

    public function testPanelIconFromBlueprint()
    {
        $page = new Page([
            'slug' => 'test',
            'blueprint' => [
                'name' => 'test',
                'icon' => 'test'
            ]
        ]);

        $icon     = $page->panelIcon();
        $expected = [
            'type'  => 'test',
            'back'  => 'pattern',
            'ratio' => null,
            'color' => '#c5c9c6'
        ];

        $this->assertEquals($expected, $icon);
    }

    public function testPanelIconWithRatio()
    {
        $page = new Page([
            'slug' => 'test'
        ]);

        $icon     = $page->panelIcon(['ratio' => '3/2']);
        $expected = [
            'type'  => 'page',
            'back'  => 'pattern',
            'ratio' => '3/2',
            'color' => '#c5c9c6'
        ];

        $this->assertEquals($expected, $icon);
    }

    public function testPanelIconWithEmoji()
    {
        $page = new Page([
            'slug' => 'test',
            'blueprint' => [
                'name' => 'test',
                'icon' => $emoji = '❤️'
            ]
        ]);

        $icon = $page->panelIcon();

        $this->assertEquals($emoji, $icon['type']);
        $this->assertEquals('pattern', $icon['back']);
        $this->assertEquals(null, $icon['ratio']);
    }

    public function testPanelOptions()
    {
        $page = new Page([
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

        $this->assertEquals($expected, $page->panelOptions());
    }

    public function testPanelOptionsWithLockedPage()
    {
        $page = new PageTestForceLocked([
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

        $this->assertEquals($expected, $page->panelOptions());

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

        $this->assertEquals($expected, $page->panelOptions(['preview']));
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
                                'slug' => 'child'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $page = $app->page('mother/child');

        $this->assertEquals('https://getkirby.com/panel/pages/mother+child', $page->panelUrl());
        $this->assertEquals('/pages/mother+child', $page->panelUrl(true));
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
                                'slug' => 'child'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $page = $app->page('mother/child');

        $this->assertEquals('https://getkirby.com/api/pages/mother+child', $page->apiUrl());
        $this->assertEquals('pages/mother+child', $page->apiUrl(true));
    }

    public function testPageMethods()
    {
        Page::$methods = [
            'test' => function () {
                return 'homer';
            }
        ];

        $page = new Page(['slug' => 'test']);

        $this->assertEquals('homer', $page->test());

        Page::$methods = [];
    }

    public function testPageModel()
    {
        Page::$models = [
            'dummy' => PageTestModel::class
        ];

        $page = Page::factory([
            'slug'  => 'test',
            'model' => 'dummy'
        ]);

        $this->assertInstanceOf(PageTestModel::class, $page);

        Page::$models = [];
    }

    public function testController()
    {
        $app = new App([
            'roots' => [
                'index' => __DIR__ . '/fixtures/PageTest'
            ],
            'templates' => [
                'foo' => __DIR__ . '/fixtures/PageTemplateTest/template.php',
                'bar' => __DIR__ . '/fixtures/PageTemplateTest/template.php',
            ],
            'site' => [
                'children' => [
                    [
                        'slug'      => 'foo',
                        'template'  => 'foo',
                        'content'   => [
                            'title' => 'Foo Title',
                        ]
                    ],
                    [
                        'slug'      => 'bar',
                        'template'  => 'bar',
                        'content'   => [
                            'title' => 'Bar Title',
                        ]
                    ]
                ],
            ],
            'controllers' => [
                // valid return
                'foo' => function ($page) {
                    $page = $page->changeTitle('New Foo Title');

                    return compact('page');
                },
                // invalid return
                'bar' => function ($page) {
                    return ['page' => 'string'];
                }
            ]
        ]);

        $app->impersonate('kirby');

        // valid test
        $page = $app->page('foo');
        $data = $page->controller();

        $this->assertCount(4, $data);
        $this->assertSame($app, $data['kirby']);
        $this->assertSame($app->site(), $data['site']);
        $this->assertSame($app->site()->children(), $data['pages']);
        $this->assertInstanceOf('Kirby\Cms\Page', $data['page']);
        $this->assertSame('New Foo Title', $data['page']->title()->value());

        // invalid test
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('The returned variable "page" from the controller "bar" is not of the required type "Kirby\Cms\Page"');

        $page = $app->page('bar');
        $page->controller();
    }
}
