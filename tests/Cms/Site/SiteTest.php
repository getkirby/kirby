<?php

namespace Kirby\Cms;

use Kirby\Toolkit\F;

class SiteTest extends TestCase
{
    public function testUrl()
    {
        $site = new Site([
            'url' => $url = 'https://getkirby.com'
        ]);

        $this->assertEquals($url, $site->url());
    }

    public function testToString()
    {
        $site = new Site(['url' => 'https://getkirby.com']);
        $this->assertEquals('https://getkirby.com', $site->toString('{{ site.url }}'));
    }

    public function testBreadcrumb()
    {
        $site = new Site([
            'children' => [
                [
                    'slug' => 'home',
                ],
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

        $site->visit('grandma/mother/child');

        $crumb = $site->breadcrumb();

        $this->assertEquals($site->find('home'), $crumb->nth(0));
        $this->assertEquals($site->find('grandma'), $crumb->nth(1));
        $this->assertEquals($site->find('grandma/mother'), $crumb->nth(2));
        $this->assertEquals($site->find('grandma/mother/child'), $crumb->nth(3));
    }

    public function testBreadcrumbSideEffects()
    {
        $site = new Site([
            'children' => [
                [
                    'slug' => 'home',
                ],
                [
                    'slug' => 'grandma',
                    'children' => [
                        [
                            'slug' => 'mother',
                            'children' => [
                                [
                                    'slug' => 'child-a'
                                ],
                                [
                                    'slug' => 'child-b'
                                ],
                                [
                                    'slug' => 'child-c'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $page  = $site->visit('grandma/mother/child-b');
        $crumb = $site->breadcrumb();

        $this->assertEquals($site->find('home'), $crumb->nth(0));
        $this->assertEquals($site->find('grandma'), $crumb->nth(1));
        $this->assertEquals($site->find('grandma/mother'), $crumb->nth(2));
        $this->assertEquals($site->find('grandma/mother/child-b'), $crumb->nth(3));

        $this->assertEquals('child-a', $page->prev()->slug());
        $this->assertEquals('child-c', $page->next()->slug());
    }

    public function testModified()
    {
        $app = new App([
            'roots' => [
                'index'   => $index = __DIR__ . '/fixtures/SitePropsTest/modified',
                'content' => $index
            ]
        ]);

        // create the site file
        F::write($file = $index . '/site.txt', 'test');

        $modified = filemtime($file);
        $site     = $app->site();

        $this->assertEquals($modified, $site->modified());

        // default date handler
        $format = 'd.m.Y';
        $this->assertEquals(date($format, $modified), $site->modified($format));

        // custom date handler
        $format = '%d.%m.%Y';
        $this->assertEquals(strftime($format, $modified), $site->modified($format, 'strftime'));

        Dir::remove($index);
    }

    public function testModifiedInMultilangInstallation()
    {
        $app = new App([
            'roots' => [
                'index'   => $index = __DIR__ . '/fixtures/SitePropsTest/modified',
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

        // create the english site
        F::write($file = $index . '/site.en.txt', 'test');

        $modified = filemtime($file);
        $site     = $app->site();

        $this->assertEquals($modified, $site->modified());

        sleep(1);

        // create the german site
        F::write($file = $index . '/site.de.txt', 'test');

        // change the language
        $app->setCurrentLanguage('de');
        $app->setCurrentTranslation('de');

        $modified = filemtime($file);
        $site     = $app->site();

        $this->assertEquals($modified, $site->modified());

        Dir::remove($index);
    }

    public function testIs()
    {
        $appA = new App([
            'roots' => [
                'index' => '/dev/null/a',
            ]
        ]);

        $appB = new App([
            'roots' => [
                'index' => '/dev/null/b',
            ]
        ]);

        $a = $appA->site();
        $b = $appB->site();
        $c = new Page(['slug' => 'test']);

        $this->assertTrue($a->is($a));
        $this->assertFalse($a->is($b));
        $this->assertFalse($a->is($c));
        $this->assertFalse($b->is($c));
    }


    public function previewUrlProvider()
    {
        return [
            [null, '/'],
            ['https://test.com', 'https://test.com'],
            ['{{ site.url }}#test', '/#test'],
            [false, null],
        ];
    }

    /**
     * @dataProvider previewUrlProvider
     */
    public function testCustomPreviewUrl($input, $expected)
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
        $site = new Site([
            'blueprint' => [
                'name'    => 'site',
                'options' => $options
            ]
        ]);

        $this->assertEquals($expected, $site->previewUrl());
    }
}
