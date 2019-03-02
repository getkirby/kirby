<?php

namespace Kirby\Cms;

use Kirby\Toolkit\F;

class PagePropsTest extends TestCase
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

        $this->assertEquals(['A', 'B', 'C'], array_column($page->blueprints(), 'title'));

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
        $this->assertEquals('[test](/test)', $page->dragText('markdown'));
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
        $this->assertEquals('[Test Title](/test)', $page->dragText('markdown'));
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
            [null, '/test', true],
            [true, '/test', false],
            [true, '/test', true],
            ['/something/different', '/something/different', false],
            ['/something/different', '/something/different', true],
            ['{{ site.url }}#{{ page.slug }}', '/#test', false],
            ['{{ site.url }}#{{ page.slug }}', '/#test', true],
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
            $expected .= '?token=' . sha1($page->id() . $page->template());
        }

        $this->assertEquals($expected, $page->previewUrl());
    }

    public function testSlug()
    {
        $page = new Page(['slug' => 'test']);
        $this->assertEquals('test', $page->slug());
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
                'index'   => $index = __DIR__ . '/fixtures/PagePropsTest/modified',
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

        Dir::remove($index);
    }

    public function testModifiedInMultilangInstallation()
    {
        $app = new App([
            'roots' => [
                'index'   => $index = __DIR__ . '/fixtures/PagePropsTest/modified',
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

        $modified = filemtime($file);
        $page     = $app->page('test');

        $this->assertEquals($modified, $page->modified());

        sleep(1);

        // create the german page
        F::write($file = $index . '/test/test.de.txt', 'test');

        // change the language
        $app->setCurrentLanguage('de');
        $app->setCurrentTranslation('de');

        $modified = filemtime($file);
        $page     = $app->page('test');

        $this->assertEquals($modified, $page->modified());

        Dir::remove($index);
    }

    public function testPanelIconDefault()
    {
        $page = new Page([
            'slug' => 'test'
        ]);

        $icon     = $page->panelIcon();
        $expected = [
            'type'  => 'page',
            'back'  => 'black',
            'ratio' => null
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
            'back'  => 'black',
            'ratio' => null
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
            'back'  => 'black',
            'ratio' => '3/2'
        ];

        $this->assertEquals($expected, $icon);
    }
}
