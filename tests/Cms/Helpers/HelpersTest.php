<?php

namespace Kirby\Cms;

use Kirby\Cms\App as Kirby;
use Kirby\Filesystem\Asset;
use Kirby\Filesystem\Dir;
use Kirby\Http\Server;
use Kirby\Http\Uri;
use Kirby\Toolkit\Collection;

class HelpersTest extends TestCase
{
    protected $fixtures;
    protected $kirby;

    public function setUp(): void
    {
        $this->kirby = new Kirby([
            'roots' => [
                'index' => $this->fixtures = __DIR__ . '/fixtures/HelpersTest'
            ],
            'urls' => [
                'index' => 'https://getkirby.com'
            ]
        ]);

        Dir::make($this->fixtures . '/site');
    }

    public function tearDown(): void
    {
        Dir::remove($this->fixtures . '/site');
    }

    public function testAttrWithBeforeValue()
    {
        $attr = attr(['test' => 'test'], ' ');
        $this->assertSame(' test="test"', $attr);
    }

    public function testAttrWithAfterValue()
    {
        $attr = attr(['test' => 'test'], null, ' ');
        $this->assertSame('test="test" ', $attr);
    }

    public function testAttrWithoutValues()
    {
        $attr = attr([]);
        $this->assertNull($attr);
    }

    public function testAsset()
    {
        $asset = asset('something.jpg');
        $this->assertInstanceOf(Asset::class, $asset);
    }

    public function testCollectionHelper()
    {
        $app = $this->kirby->clone([
            'site' => [
                'children' => [
                    ['slug' => 'test']
                ]
            ],
            'collections' => [
                'test' => function ($pages) {
                    return $pages;
                }
            ]
        ]);

        $collection = collection('test');

        $this->assertCount(1, $collection);
        $this->assertSame('test', $collection->first()->slug());
    }

    public function testCsrfHelper()
    {
        $session = $this->kirby->session();

        // should generate token
        $session->remove('kirby.csrf');
        $token = csrf();
        $this->assertIsString($token);
        $this->assertStringMatchesFormat('%x', $token);
        $this->assertSame(64, strlen($token));
        $this->assertSame($session->get('kirby.csrf'), $token);

        // should not regenerate when a param is passed
        $this->assertFalse(csrf(null));
        $this->assertFalse(csrf(false));
        $this->assertFalse(csrf(123));
        $this->assertFalse(csrf('some invalid string'));
        $this->assertSame($token, $session->get('kirby.csrf'));

        // should not regenerate if there is already a token
        $token2 = csrf();
        $this->assertSame($token, $token2);

        // should regenerate if there is an invalid token
        $session->set('kirby.csrf', 123);
        $token3 = csrf();
        $this->assertNotEquals($token, $token3);
        $this->assertSame(64, strlen($token3));
        $this->assertSame($session->get('kirby.csrf'), $token3);

        // should verify token
        $this->assertTrue(csrf($token3));
        $this->assertFalse(csrf($token2));
        $this->assertFalse(csrf(null));
        $this->assertFalse(csrf(false));
        $this->assertFalse(csrf(123));
        $this->assertFalse(csrf('some invalid string'));

        $session->destroy();
    }

    public function testCssHelper()
    {
        $result   = css('assets/css/index.css');
        $expected = '<link href="https://getkirby.com/assets/css/index.css" rel="stylesheet">';

        $this->assertSame($expected, $result);
    }

    public function testCssHelperWithMediaOption()
    {
        $result   = css('assets/css/index.css', 'print');
        $expected = '<link href="https://getkirby.com/assets/css/index.css" media="print" rel="stylesheet">';

        $this->assertSame($expected, $result);
    }

    public function testCssHelperWithAttrs()
    {
        $result   = css('assets/css/index.css', ['integrity' => 'nope']);
        $expected = '<link href="https://getkirby.com/assets/css/index.css" integrity="nope" rel="stylesheet">';

        $this->assertSame($expected, $result);
    }

    public function testCssHelperWithValidRelAttr()
    {
        $result   = css('assets/css/index.css', ['rel' => 'alternate stylesheet', 'title' => 'High contrast']);
        $expected = '<link href="https://getkirby.com/assets/css/index.css" rel="alternate stylesheet" title="High contrast">';

        $this->assertSame($expected, $result);
    }

    public function testCssHelperWithInvalidRelAttr()
    {
        $result   = css('assets/css/index.css', ['rel' => 'alternate', 'title' => 'High contrast']);
        $expected = '<link href="https://getkirby.com/assets/css/index.css" rel="stylesheet" title="High contrast">';

        $this->assertSame($expected, $result);
    }

    public function testCssHelperWithRelAttrButNoTitle()
    {
        $result   = css('assets/css/index.css', ['rel' => 'alternate stylesheet']);
        $expected = '<link href="https://getkirby.com/assets/css/index.css" rel="stylesheet">';

        $this->assertSame($expected, $result);
    }

    public function testCssHelperWithArray()
    {
        $result = css([
            'assets/css/a.css',
            'assets/css/b.css'
        ]);

        $expected  = '<link href="https://getkirby.com/assets/css/a.css" rel="stylesheet">' . PHP_EOL;
        $expected .= '<link href="https://getkirby.com/assets/css/b.css" rel="stylesheet">';

        $this->assertSame($expected, $result);
    }

    public function testDeprecated()
    {
        // with disabled debug mode
        $this->assertFalse(deprecated('The xyz method is deprecated.'));

        $this->kirby = $this->kirby->clone([
            'options' => [
                'debug' => true
            ]
        ]);

        // with enabled debug mode
        $this->expectException('Whoops\Exception\ErrorException');
        $this->expectExceptionMessage('The xyz method is deprecated.');
        deprecated('The xyz method is deprecated.');
    }

    public function testDumpHelperOnCli()
    {
        $this->assertSame("test\n", dump('test', false));
    }

    public function testDumpHelperOnServer()
    {
        Server::$cli = false;
        $this->assertSame('<pre>test</pre>', dump('test', false));
        Server::$cli = null;
    }

    public function testEscWithInvalidContext()
    {
        $escaped = esc('test', 'does-not-exist');
        $this->assertSame('test', $escaped);
    }

    public function testGist()
    {
        $gist     = gist('https://gist.github.com/bastianallgeier/d61ab782cd5c2cc02b6f6fec54fd1985', 'static.php');
        $expected = '<script src="https://gist.github.com/bastianallgeier/d61ab782cd5c2cc02b6f6fec54fd1985.js?file=static.php"></script>';

        $this->assertSame($gist, $expected);
    }

    public function testH()
    {
        $html = h('Guns & Roses');
        $this->assertSame('Guns &amp; Roses', $html);
    }

    public function testHtml()
    {
        $html = html('Guns & Roses');
        $this->assertSame('Guns &amp; Roses', $html);
    }

    public function testImageHelper()
    {
        $app = $this->kirby->clone([
            'site' => [
                'files' => [
                    ['filename' => 'sitefile.jpg']
                ],
                'children' => [
                    [
                        'slug' => 'test',
                        'files' => [
                            ['filename' => 'pagefile.jpg']
                        ]
                    ]
                ]
            ]
        ]);

        $image = image('test/pagefile.jpg');
        $this->assertInstanceOf(File::class, $image);

        $image = image('/sitefile.jpg');
        $this->assertInstanceOf(File::class, $image);

        // get the first image of the current page
        $app->site()->visit('test');
        $image = image();
        $this->assertInstanceOf(File::class, $image);

        $image = image('pagefile.jpg');
        $this->assertInstanceOf(File::class, $image);
    }

    public function testInvalid()
    {
        $data = [
            'username' => 123,
            'email'    => 'homersimpson.com',
            'zip'      => 'abc',
            'website'  => '',
            'created'  => '9999-99-99',
        ];

        $rules = [
            'username' => ['alpha'],
            'email'    => ['required', 'email'],
            'zip'      => ['integer'],
            'website'  => ['url'],
            'created'  => ['date']
        ];

        $messages = [
            'username' => 'The username must not contain numbers',
            'email'    => 'Invalid email',
            'zip'      => 'The ZIP must contain only numbers',
            'created'  => 'Invalid date',
        ];

        $result = invalid($data, $rules, $messages);
        $this->assertSame($messages, $result);

        $data = [
            'username' => 'homer',
            'email'    => 'homer@simpson.com',
            'zip'      => 123,
            'website'  => 'http://example.com',
            'created'  => '2021-01-01',
        ];

        $result = invalid($data, $rules, $messages);
        $this->assertSame([], $result);
    }

    public function testInvalidSimple()
    {
        $data   = ['homer', null];
        $rules  = [['alpha'], ['required']];
        $result = invalid($data, $rules);
        $this->assertSame(1, $result[1]);
    }

    public function testInvalidRequired()
    {
        $rules    = ['email' => ['required']];
        $messages = ['email' => ''];

        $result = invalid(['email' => null], $rules, $messages);
        $this->assertSame($messages, $result);

        $result = invalid(['name' => 'homer'], $rules, $messages);
        $this->assertSame($messages, $result);

        $result = invalid(['email' => ''], $rules, $messages);
        $this->assertSame($messages, $result);

        $result = invalid(['email' => []], $rules, $messages);
        $this->assertSame($messages, $result);

        $result = invalid(['email' => '0'], $rules, $messages);
        $this->assertSame([], $result);

        $result = invalid(['email' => 0], $rules, $messages);
        $this->assertSame([], $result);

        $result = invalid(['email' => false], $rules, $messages);
        $this->assertSame([], $result);

        $result = invalid(['email' => 'homer@simpson.com'], $rules, $messages);
        $this->assertSame([], $result);
    }

    public function testInvalidOptions()
    {
        $rules = [
            'username' => ['min' => 6]
        ];

        $messages = [
            'username' => ''
        ];

        $result = invalid(['username' => 'homer'], $rules, $messages);
        $this->assertSame($messages, $result);

        $result = invalid(['username' => 'homersimpson'], $rules, $messages);
        $this->assertSame([], $result);

        $rules = [
            'username' => ['between' => [3, 6]]
        ];

        $result = invalid(['username' => 'ho'], $rules, $messages);
        $this->assertSame($messages, $result);

        $result = invalid(['username' => 'homersimpson'], $rules, $messages);
        $this->assertSame($messages, $result);

        $result = invalid(['username' => 'homer'], $rules, $messages);
        $this->assertSame([], $result);
    }

    public function testInvalidWithMultipleMessages()
    {
        $data     = ['username' => ''];
        $rules    = ['username' => ['required', 'alpha', 'min' => 4]];
        $messages = ['username' => [
            'The username is required',
            'The username must contain only letters',
            'The username must be at least 4 characters long',
        ]];

        $result   = invalid(['username' => ''], $rules, $messages);
        $expected = [
            'username' => [
                'The username is required',
            ]
        ];
        $this->assertSame($expected, $result);

        $result   = invalid(['username' => 'a1'], $rules, $messages);
        $expected = [
            'username' => [
                'The username must contain only letters',
                'The username must be at least 4 characters long',
            ]
        ];
        $this->assertSame($expected, $result);

        $result   = invalid(['username' => 'ab'], $rules, $messages);
        $expected = [
            'username' => [
                'The username must be at least 4 characters long',
            ]
        ];
        $this->assertSame($expected, $result);

        $result = invalid(['username' => 'abcd'], $rules, $messages);
        $this->assertSame([], $result);
    }

    public function testJsHelper()
    {
        $result   = js('assets/js/index.js');
        $expected = '<script src="https://getkirby.com/assets/js/index.js"></script>';

        $this->assertSame($expected, $result);
    }

    public function testJsHelperWithAsyncOption()
    {
        $result   = js('assets/js/index.js', true);
        $expected = '<script async src="https://getkirby.com/assets/js/index.js"></script>';

        $this->assertSame($expected, $result);
    }

    public function testJsHelperWithAttrs()
    {
        $result   = js('assets/js/index.js', ['integrity' => 'nope']);
        $expected = '<script integrity="nope" src="https://getkirby.com/assets/js/index.js"></script>';

        $this->assertSame($expected, $result);
    }

    public function testJsHelperWithArray()
    {
        $result = js([
            'assets/js/a.js',
            'assets/js/b.js'
        ]);

        $expected  = '<script src="https://getkirby.com/assets/js/a.js"></script>' . PHP_EOL;
        $expected .= '<script src="https://getkirby.com/assets/js/b.js"></script>';

        $this->assertSame($expected, $result);
    }

    public function testKirbyHelper()
    {
        $this->assertSame($this->kirby, kirby());
    }

    public function testKirbyTagHelper()
    {
        $tag = kirbytag('link', 'https://getkirby.com', ['text' => 'Kirby']);
        $expected = '<a href="https://getkirby.com">Kirby</a>';

        $this->assertSame($expected, $tag);
    }

    public function testKirbyTagsHelper()
    {
        $tag = kirbytags('(link: https://getkirby.com text: Kirby)');
        $expected = '<a href="https://getkirby.com">Kirby</a>';

        $this->assertSame($expected, $tag);
    }

    public function testKirbyTextHelper()
    {
        $text     = 'This is **just** a text.';
        $expected = '<p>This is <strong>just</strong> a text.</p>';

        $this->assertSame($expected, kirbytext($text));
        $this->assertSame($expected, kt($text));
    }

    public function testKirbyTextHelperWithSafeMode()
    {
        $text     = '<h1>Kirby</h1>';
        $expected = '<p>&lt;h1&gt;Kirby&lt;/h1&gt;</p>';

        $this->assertSame($expected, kirbytext($text, ['markdown' => ['safe' => true]]));
        $this->assertSame($expected, kt($text, ['markdown' => ['safe' => true]]));
    }

    public function testKirbyTextInlineHelper()
    {
        $text     = 'This is **just** a text.';
        $expected = 'This is <strong>just</strong> a text.';

        $this->assertSame($expected, kirbytextinline($text));
        $this->assertSame($expected, kti($text));
    }

    public function testKirbyTextInlineHelperWithSafeMode()
    {
        $text     = 'This is <b>just</b> a text.';
        $expected = 'This is &lt;b&gt;just&lt;/b&gt; a text.';

        $this->assertSame($expected, kirbytextinline($text, ['markdown' => ['safe' => true]]));
        $this->assertSame($expected, kti($text, ['markdown' => ['safe' => true]]));
    }

    public function testLoad()
    {
        load([
            'helperstest\\a' => __DIR__ . '/fixtures/HelpersTest/load/a/a.php',
            'HelpersTest\\B' => __DIR__ . '/fixtures/HelpersTest/load/B/B.php',
        ]);

        $this->assertTrue(class_exists('HelpersTest\\A'));
        $this->assertTrue(class_exists('HelpersTest\\B'));
    }

    public function testMarkdownHelper()
    {
        $tag = markdown('# Kirby');
        $expected = '<h1>Kirby</h1>';

        $this->assertSame($expected, $tag);
    }

    public function testMarkdownHelperWithSafeMode()
    {
        $tag = markdown('<h1>Kirby</h1>', ['safe' => true]);
        $expected = '<p>&lt;h1&gt;Kirby&lt;/h1&gt;</p>';

        $this->assertSame($expected, $tag);
    }

    public function testOptionHelper()
    {
        $app = $this->kirby->clone([
            'options' => [
                'foo' => 'bar'
            ]
        ]);

        $this->assertSame('bar', option('foo'));
        $this->assertSame('fallback', option('does-not-exist', 'fallback'));
    }

    public function testPageHelper()
    {
        $app = $this->kirby->clone([
            'site' => [
                'children' => [
                    [
                        'slug' => 'home'
                    ],
                    [
                        'slug' => 'test',
                    ]
                ]
            ]
        ]);

        // get the current page without browsing
        $page = page();
        $this->assertSame('home', $page->slug());

        // get the current page after changing the current page
        $app->site()->visit('test');
        $page = page();
        $this->assertSame('test', $page->slug());

        // get a specific page
        $page = page('test');
        $this->assertSame('test', $page->slug());
    }

    public function testPagesHelper()
    {
        $app = $this->kirby->clone([
            'site' => [
                'children' => [
                    [
                        'slug' => 'a'
                    ],
                    [
                        'slug' => 'b',
                    ]
                ]
            ]
        ]);

        $pages = pages('a', 'b');
        $this->assertCount(2, $pages);
    }

    public function testParam()
    {
        Uri::$current = new Uri('https://getkirby.com/projects/filter:current');

        $app = $this->kirby->clone();

        $this->assertSame('current', param('filter'));

        Uri::$current = null;
    }

    public function testParams()
    {
        Uri::$current = new Uri('https://getkirby.com/projects/a:value-a/b:value-b');

        $app = $this->kirby->clone();

        $this->assertSame(['a' => 'value-a', 'b' => 'value-b'], params());

        Uri::$current = null;
    }

    public function testR()
    {
        $this->assertSame('a', r(1 === 1, 'a', 'b'));
        $this->assertSame('b', r(1 === 2, 'a', 'b'));
        $this->assertSame(null, r(1 === 2, 'a'));
    }

    public function testRouter()
    {
        $routes = [
            [
                'pattern' => 'a/(:any)',
                'method'  => 'POST',
                'action'  => function () {
                    return 'nonono';
                }
            ],
            [
                'pattern' => 'b/(:any)',
                'method'  => 'ALL',
                'action'  => function () {
                    return 'nonono';
                }
            ],
            [
                'pattern' => 'a/(:any)',
                'method'  => 'GET',
                'action'  => function ($path) {
                    return 'yes: ' . $path;
                }
            ]
        ];

        $result = router('a/foo', 'GET', $routes);
        $this->assertSame('yes: foo', $result);
    }

    public function testSite()
    {
        $this->assertSame($this->kirby->site(), site());
    }

    public function testSize()
    {
        // number
        $this->assertSame(3, size(3));

        // string
        $this->assertSame(3, size('abc'));

        // array
        $this->assertSame(3, size(['a', 'b', 'c']));

        // collection
        $this->assertSame(3, size(new Collection(['a', 'b', 'c'])));
    }

    public function testSmartypants()
    {
        $text     = smartypants('"Test"');
        $expected = '&#8220;Test&#8221;';

        $this->assertSame($expected, $text);
    }

    public function testSmartypantsWithKirbytext()
    {
        new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => [
                'smartypants' => true
            ]
        ]);

        $text     = kirbytextinline('"Test"');
        $expected = '&#8220;Test&#8221;';

        $this->assertSame($expected, $text);
    }

    public function testSnippet()
    {
        $app = $this->kirby->clone([
            'roots' => [
                'index'     => $index = __DIR__ . '/fixtures/HelpersTest',
                'snippets'  => $index,
            ]
        ]);

        $result = snippet('snippet', ['message' => 'world'], true);
        $this->assertSame('Hello world', $result);
    }

    public function testSnippetAlternatives()
    {
        $app = $this->kirby->clone([
            'roots' => [
                'index'     => $index = __DIR__ . '/fixtures/HelpersTest',
                'snippets'  => $index,
            ]
        ]);

        $result = snippet(['does-not-exist', 'does-not-exist-either', 'snippet'], ['message' => 'world'], true);
        $this->assertSame('Hello world', $result);
    }

    public function testSvg()
    {
        $result = svg('test.svg');
        $this->assertSame('<svg>test</svg>', trim($result));
    }

    public function testSvgWithAbsolutePath()
    {
        $result = svg(__DIR__ . '/fixtures/HelpersTest/test.svg');
        $this->assertSame('<svg>test</svg>', trim($result));
    }

    public function testSvgWithInvalidFileType()
    {
        $this->assertFalse(svg('somefile.jpg'));
    }

    public function testSvgWithMissingFile()
    {
        $this->assertFalse(svg('somefile.svg'));
    }

    public function testSvgWithFileObject()
    {
        $file = $this->getMockBuilder(File::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__call'])
            ->addMethods(['extension'])
            ->getMock();
        $file->method('__call')->willReturn('test');
        $file->method('extension')->willReturn('svg');

        $this->assertSame('test', svg($file));
    }

    public function testTimestamp()
    {
        $result = timestamp('2021-12-12 12:12:12');
        $this->assertSame('2021-12-12 12:12:12', date('Y-m-d H:i:s', $result));
    }

    public function testTimestampWithStep()
    {
        $result = timestamp('2021-12-12 12:12:12', [
            'unit' => 'minute',
            'size' => 5
        ]);

        $this->assertSame('2021-12-12 12:10:00', date('Y-m-d H:i:s', $result));
    }

    public function testTimestampWithInvalidDate()
    {
        $result = timestamp('invalid date');
        $this->assertNull($result);
    }

    public function testTcHelper()
    {
        $this->kirby->clone([
            'translations' => [
                'en' => [
                    'car' => ['No cars', 'One car', 'Two cars', 'Many cars']
                ]
            ]
        ]);

        $this->assertSame('No cars', tc('car', 0));
        $this->assertSame('One car', tc('car', 1));
        $this->assertSame('Two cars', tc('car', 2));
        $this->assertSame('Many cars', tc('car', 3));
        $this->assertSame('Many cars', tc('car', 4));
    }

    public function testTcHelperWithPlaceholders()
    {
        $this->kirby->clone([
            'translations' => [
                'en' => [
                    'car' => ['No cars', 'One car', '{{ count }} cars']
                ],
                'de' => [
                    'car' => ['Keine Autos', 'Ein Auto', '{{ count }} Autos']
                ]
            ]
        ]);

        $this->assertSame('2 cars', tc('car', 2));
        $this->assertSame('3 cars', tc('car', 3));
        $this->assertSame('1,234,567 cars', tc('car', 1234567));
        $this->assertSame('1,234,567 cars', tc('car', 1234567, null));
        $this->assertSame('1,234,567 cars', tc('car', 1234567, null, true));
        $this->assertSame('1234567 cars', tc('car', 1234567, null, false));
        $this->assertSame('1.234.567 Autos', tc('car', 1234567, 'de'));
        $this->assertSame('1.234.567 Autos', tc('car', 1234567, 'de', true));
        $this->assertSame('1234567 Autos', tc('car', 1234567, 'de', false));
    }

    public function testTwitter()
    {
        // simple
        $result   = twitter('getkirby');
        $expected = '<a href="https://twitter.com/getkirby">@getkirby</a>';

        $this->assertSame($expected, $result);

        // with attributes
        $result   = twitter('getkirby', 'Follow us', 'Kirby on Twitter', 'twitter');
        $expected = '<a class="twitter" href="https://twitter.com/getkirby" title="Kirby on Twitter">Follow us</a>';

        $this->assertSame($expected, $result);
    }

    public function testUrlHelper()
    {
        $app = $this->kirby->clone([
            'urls' => [
                'index' => $url = 'https://getkirby.com'
            ]
        ]);

        $this->assertSame($url . '/test', url('test'));
        $this->assertSame($url . '/test', u('test'));
    }

    public function testUrlHelperWithOptions()
    {
        $app = $this->kirby->clone([
            'urls' => [
                'index' => $url = 'https://getkirby.com'
            ]
        ]);

        $options = [
            'params' => 'foo:bar',
            'query'  => 'q=search'
        ];

        $expected = $url . '/test/foo:bar?q=search';

        $this->assertSame($expected, url('test', $options));
        $this->assertSame($expected, u('test', $options));
    }

    public function testVideo()
    {
        $video    = video('https://youtube.com/watch?v=xB3s_f7PzYk');
        $expected = '<iframe allow="fullscreen" allowfullscreen src="https://youtube.com/embed/xB3s_f7PzYk"></iframe>';

        $this->assertSame($expected, $video);
    }

    public function testYoutubeVideoWithOptions()
    {
        $video = video('https://youtube.com/watch?v=xB3s_f7PzYk', [
            'youtube' => [
                'controls' => 0
            ]
        ]);

        $expected = '<iframe allow="fullscreen" allowfullscreen src="https://youtube.com/embed/xB3s_f7PzYk?controls=0"></iframe>';

        $this->assertSame($expected, $video);
    }

    public function testVimeoVideoWithOptions()
    {
        $video = video('https://vimeo.com/335292911', [
            'vimeo' => [
                'controls' => 0
            ]
        ]);

        $expected = '<iframe allow="fullscreen" allowfullscreen src="https://player.vimeo.com/video/335292911?controls=0"></iframe>';

        $this->assertSame($expected, $video);
    }

    public function testVimeo()
    {
        $video    = vimeo('https://vimeo.com/335292911');
        $expected = '<iframe allow="fullscreen" allowfullscreen src="https://player.vimeo.com/video/335292911"></iframe>';

        $this->assertSame($expected, $video);
    }

    public function testVimeoWithOptions()
    {
        $video    = vimeo('https://vimeo.com/335292911', ['controls' => 0]);
        $expected = '<iframe allow="fullscreen" allowfullscreen src="https://player.vimeo.com/video/335292911?controls=0"></iframe>';

        $this->assertSame($expected, $video);
    }

    public function testWidont()
    {
        $result   = widont('This is a headline');
        $expected = 'This is a&nbsp;headline';

        $this->assertSame($expected, $result);
    }

    public function testYoutube()
    {
        $video    = youtube('https://youtube.com/watch?v=xB3s_f7PzYk');
        $expected = '<iframe allow="fullscreen" allowfullscreen src="https://youtube.com/embed/xB3s_f7PzYk"></iframe>';

        $this->assertSame($expected, $video);
    }

    public function testYoutubeWithOptions()
    {
        $video    = youtube('https://youtube.com/watch?v=xB3s_f7PzYk', ['controls' => 0]);
        $expected = '<iframe allow="fullscreen" allowfullscreen src="https://youtube.com/embed/xB3s_f7PzYk?controls=0"></iframe>';

        $this->assertSame($expected, $video);
    }
}
