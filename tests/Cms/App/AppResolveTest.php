<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

class AppResolveTest extends TestCase
{
    protected $fixtures;

    public function setUp(): void
    {
        $this->fixtures = __DIR__ . '/fixtures/AppResolveTest';
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures);
    }

    public function testResolveHomePage()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'home'
                    ]
                ]
            ]
        ]);

        $result = $app->resolve(null);

        $this->assertInstanceOf(Page::class, $result);
        $this->assertTrue($result->isHomePage());
    }

    public function testResolveMainPage()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'test'
                    ]
                ]
            ]
        ]);

        $result = $app->resolve('test');

        $this->assertInstanceOf(Page::class, $result);
        $this->assertEquals('test', $result->id());
    }

    public function testResolveSubPage()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'children' => [
                            ['slug' => 'subpage']
                        ]
                    ]
                ]
            ]
        ]);

        $result = $app->resolve('test/subpage');

        $this->assertInstanceOf(Page::class, $result);
        $this->assertEquals('test/subpage', $result->id());
    }

    public function testResolvePageRepresentation()
    {
        F::write($template = $this->fixtures . '/test.php', 'html');
        F::write($template = $this->fixtures . '/test.xml.php', 'xml');
        F::write(
            $template = $this->fixtures . '/test.png.php',
            '<?php $kirby->response()->type("image/jpeg"); ?>png'
        );

        $app = new App([
            'roots' => [
                'index'     => '/dev/null',
                'templates' => $this->fixtures
            ],
            'site' => [
                'children' => [
                    [
                        'slug'     => 'test',
                        'template' => 'test'
                    ]
                ],
            ]
        ]);

        // missing representation
        $result = $app->resolve('test.json');
        $this->assertNull($result);

        // xml representation
        $result = $app->clone()->resolve('test.xml');
        $this->assertInstanceOf(Responder::class, $result);
        $this->assertSame('text/xml', $result->type());
        $this->assertSame('xml', $result->body());

        // representation with custom MIME type
        $result = $app->clone()->resolve('test.png');
        $this->assertInstanceOf(Responder::class, $result);
        $this->assertSame('image/jpeg', $result->type());
        $this->assertSame('png', $result->body());
    }

    public function testResolveSiteFile()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null',
            ],
            'site' => [
                'files' => [
                    ['filename' => 'test.jpg']
                ],
            ]
        ]);

        // missing file
        $result = $app->resolve('test.png');
        $this->assertNull($result);

        // existing file
        $result = $app->resolve('test.jpg');

        $this->assertInstanceOf(File::class, $result);
        $this->assertEquals('test.jpg', $result->id());
    }

    public function testResolvePageFile()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null',
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'test',
                        'files' => [
                            ['filename' => 'test.jpg']
                        ],
                    ]
                ]
            ]
        ]);

        // missing file
        $result = $app->resolve('test/test.png');
        $this->assertNull($result);

        // existing file
        $result = $app->resolve('test/test.jpg');

        $this->assertInstanceOf(File::class, $result);
        $this->assertEquals('test/test.jpg', $result->id());
    }

    public function testResolveMultilangPageRepresentation()
    {
        F::write($template = $this->fixtures . '/test.php', 'html');
        F::write($template = $this->fixtures . '/test.xml.php', 'xml');

        $app = new App([
            'roots' => [
                'index'     => '/dev/null',
                'templates' => $this->fixtures
            ],
            'site' => [
                'children' => [
                    [
                        'slug'     => 'test',
                        'template' => 'test'
                    ]
                ],
            ],
            'options' => [
                'languages' => true
            ],
            'languages' => [
                [
                    'code'    => 'de',
                    'default' => true,
                    'url'     => '/'
                ],
                [
                    'code' => 'en',
                ]
            ]
        ]);

        /**
         * Default language (DE)
         */

        // finding the page
        $result = $app->resolve('test');

        $this->assertInstanceOf(Page::class, $result);
        $this->assertEquals('test', $result->id());
        $this->assertEquals('de', $app->language()->code());

        // missing representation
        $result = $app->resolve('test.json');

        $this->assertNull($result);
        $this->assertEquals('de', $app->language()->code());

        // xml presentation
        $result = $app->resolve('test.xml');

        $this->assertInstanceOf(Responder::class, $result);
        $this->assertEquals('xml', $result->body());
        $this->assertEquals('de', $app->language()->code());

        /**
         * Secondary language (EN)
         */

        // finding the page
        $result = $app->resolve('test', 'en');

        $this->assertInstanceOf(Page::class, $result);
        $this->assertEquals('test', $result->id());
        $this->assertEquals('en', $app->language()->code());

        // missing representation
        $result = $app->resolve('test.json', 'en');

        $this->assertNull($result);
        $this->assertEquals('en', $app->language()->code());

        // xml presentation
        $result = $app->resolve('test.xml', 'en');

        $this->assertInstanceOf(Responder::class, $result);
        $this->assertEquals('xml', $result->body());
        $this->assertEquals('en', $app->language()->code());
    }

    public function testRepresentationErrorType()
    {
        $this->app = new App([
            'templates' => [
                'blog' => __DIR__ . '/fixtures/templates/test.php',
            ],
            'site' => [
                'children' => [
                    [
                        'slug' => 'blog',
                        'template' => 'blog'
                    ]
                ]
            ]
        ]);

        $this->assertNull($this->app->resolve('blog.php'));

        // there must be no forced php response type if the
        // representation cannot be found
        $this->assertNull($this->app->response()->type());
    }
}
