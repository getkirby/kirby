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
    public function setUp()
    {
        parent::setUp();
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

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The property "slug" is required
     */
    public function testEmptyId()
    {
        $page = new Page(['slug' => null]);
    }

    /**
     * @expectedException TypeError
     */
    public function testInvalidId()
    {
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

    /**
     * @expectedException TypeError
     */
    public function testInvalidNum()
    {
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

    /**
     * @expectedException TypeError
     */
    public function testInvalidParent()
    {
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

    /**
     * @expectedException TypeError
     */
    public function testInvalidSite()
    {
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

    /**
     * @expectedException TypeError
     */
    public function testInvalidTemplate()
    {
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

    /**
     * @expectedException TypeError
     */
    public function testInvalidUrl()
    {
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

        // custom date handler
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
        $app->localize($app->language('de'));

        $modified = filemtime($file);
        $page     = $app->page('test');

        $this->assertEquals($modified, $page->modified());

        Dir::remove($index);

    }

}
