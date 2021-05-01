<?php

namespace Kirby\Cms;

use Kirby\Cms\App as Kirby;
use Kirby\Http\Server;
use Kirby\Http\Uri;
use Kirby\Toolkit\Collection;
use Kirby\Toolkit\Dir;

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
        $this->assertEquals(' test="test"', $attr);
    }

    public function testAttrWithAfterValue()
    {
        $attr = attr(['test' => 'test'], null, ' ');
        $this->assertEquals('test="test" ', $attr);
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
        $this->assertEquals('test', $collection->first()->slug());
    }

    public function testCsrfHelper()
    {
        $session = $this->kirby->session();

        // should generate token
        $session->remove('kirby.csrf');
        $token = csrf();
        $this->assertIsString($token);
        $this->assertStringMatchesFormat('%x', $token);
        $this->assertEquals(64, strlen($token));
        $this->assertEquals($session->get('kirby.csrf'), $token);

        // should not regenerate when a param is passed
        $this->assertFalse(csrf(null));
        $this->assertFalse(csrf(false));
        $this->assertFalse(csrf(123));
        $this->assertFalse(csrf('some invalid string'));
        $this->assertEquals($token, $session->get('kirby.csrf'));

        // should not regenerate if there is already a token
        $token2 = csrf();
        $this->assertEquals($token, $token2);

        // should regenerate if there is an invalid token
        $session->set('kirby.csrf', 123);
        $token3 = csrf();
        $this->assertNotEquals($token, $token3);
        $this->assertEquals(64, strlen($token3));
        $this->assertEquals($session->get('kirby.csrf'), $token3);

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

        $this->assertEquals($expected, $result);
    }

    public function testCssHelperWithMediaOption()
    {
        $result   = css('assets/css/index.css', 'print');
        $expected = '<link href="https://getkirby.com/assets/css/index.css" media="print" rel="stylesheet">';

        $this->assertEquals($expected, $result);
    }

    public function testCssHelperWithAttrs()
    {
        $result   = css('assets/css/index.css', ['integrity' => 'nope']);
        $expected = '<link href="https://getkirby.com/assets/css/index.css" integrity="nope" rel="stylesheet">';

        $this->assertEquals($expected, $result);
    }

    public function testCssHelperWithArray()
    {
        $result = css([
            'assets/css/a.css',
            'assets/css/b.css'
        ]);

        $expected  = '<link href="https://getkirby.com/assets/css/a.css" rel="stylesheet">' . PHP_EOL;
        $expected .= '<link href="https://getkirby.com/assets/css/b.css" rel="stylesheet">';

        $this->assertEquals($expected, $result);
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
        $this->assertEquals("test\n", dump('test', false));
    }

    public function testDumpHelperOnServer()
    {
        Server::$cli = false;
        $this->assertEquals('<pre>test</pre>', dump('test', false));
        Server::$cli = null;
    }

    public function testEscWithInvalidContext()
    {
        $escaped = esc('test', 'does-not-exist');
        $this->assertEquals('test', $escaped);
    }

    public function testGist()
    {
        $gist     = gist('https://gist.github.com/bastianallgeier/d61ab782cd5c2cc02b6f6fec54fd1985', 'static.php');
        $expected = '<script src="https://gist.github.com/bastianallgeier/d61ab782cd5c2cc02b6f6fec54fd1985.js?file=static.php"></script>';

        $this->assertEquals($gist, $expected);
    }

    public function testH()
    {
        $html = h('Guns & Roses');
        $this->assertEquals('Guns &amp; Roses', $html);
    }

    public function testHtml()
    {
        $html = html('Guns & Roses');
        $this->assertEquals('Guns &amp; Roses', $html);
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
        $this->assertEquals(1, $result[1]);
    }

    public function testInvalidRequired()
    {
        $rules    = ['email' => ['required']];
        $messages = ['email' => ''];

        $result = invalid(['email' => null], $rules, $messages);
        $this->assertEquals($messages, $result);

        $result = invalid(['name' => 'homer'], $rules, $messages);
        $this->assertEquals($messages, $result);

        $result = invalid(['email' => ''], $rules, $messages);
        $this->assertEquals($messages, $result);

        $result = invalid(['email' => []], $rules, $messages);
        $this->assertEquals($messages, $result);

        $result = invalid(['email' => '0'], $rules, $messages);
        $this->assertEquals([], $result);

        $result = invalid(['email' => 0], $rules, $messages);
        $this->assertEquals([], $result);

        $result = invalid(['email' => false], $rules, $messages);
        $this->assertEquals([], $result);

        $result = invalid(['email' => 'homer@simpson.com'], $rules, $messages);
        $this->assertEquals([], $result);
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
        $this->assertEquals($messages, $result);

        $result = invalid(['username' => 'homersimpson'], $rules, $messages);
        $this->assertEquals([], $result);

        $rules = [
            'username' => ['between' => [3, 6]]
        ];

        $result = invalid(['username' => 'ho'], $rules, $messages);
        $this->assertEquals($messages, $result);

        $result = invalid(['username' => 'homersimpson'], $rules, $messages);
        $this->assertEquals($messages, $result);

        $result = invalid(['username' => 'homer'], $rules, $messages);
        $this->assertEquals([], $result);
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
        $this->assertEquals($expected, $result);

        $result   = invalid(['username' => 'a1'], $rules, $messages);
        $expected = [
            'username' => [
                'The username must contain only letters',
                'The username must be at least 4 characters long',
            ]
        ];
        $this->assertEquals($expected, $result);

        $result   = invalid(['username' => 'ab'], $rules, $messages);
        $expected = [
            'username' => [
                'The username must be at least 4 characters long',
            ]
        ];
        $this->assertEquals($expected, $result);

        $result = invalid(['username' => 'abcd'], $rules, $messages);
        $this->assertEquals([], $result);
    }

    public function testJsHelper()
    {
        $result   = js('assets/js/index.js');
        $expected = '<script src="https://getkirby.com/assets/js/index.js"></script>';

        $this->assertEquals($expected, $result);
    }

    public function testJsHelperWithAsyncOption()
    {
        $result   = js('assets/js/index.js', true);
        $expected = '<script async src="https://getkirby.com/assets/js/index.js"></script>';

        $this->assertEquals($expected, $result);
    }

    public function testJsHelperWithAttrs()
    {
        $result   = js('assets/js/index.js', ['integrity' => 'nope']);
        $expected = '<script integrity="nope" src="https://getkirby.com/assets/js/index.js"></script>';

        $this->assertEquals($expected, $result);
    }

    public function testJsHelperWithArray()
    {
        $result = js([
            'assets/js/a.js',
            'assets/js/b.js'
        ]);

        $expected  = '<script src="https://getkirby.com/assets/js/a.js"></script>' . PHP_EOL;
        $expected .= '<script src="https://getkirby.com/assets/js/b.js"></script>';

        $this->assertEquals($expected, $result);
    }

    public function testKirbyHelper()
    {
        $this->assertEquals($this->kirby, kirby());
    }

    public function testKirbyTagHelper()
    {
        $tag = kirbytag('link', 'https://getkirby.com', ['text' => 'Kirby']);
        $expected = '<a href="https://getkirby.com">Kirby</a>';

        $this->assertEquals($expected, $tag);
    }

    public function testKirbyTagsHelper()
    {
        $tag = kirbytags('(link: https://getkirby.com text: Kirby)');
        $expected = '<a href="https://getkirby.com">Kirby</a>';

        $this->assertEquals($expected, $tag);
    }

    public function testKirbyTextHelper()
    {
        $text   = 'This is **just** a text.';
        $normal = '<p>This is <strong>just</strong> a text.</p>';
        $inline = 'This is <strong>just</strong> a text.';

        $this->assertEquals($normal, kirbytext($text));
        $this->assertEquals($normal, kt($text));
        $this->assertEquals($inline, kirbytextinline($text));
        $this->assertEquals($inline, kti($text));
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

        $this->assertEquals($expected, $tag);
    }

    public function testOptionHelper()
    {
        $app = $this->kirby->clone([
            'options' => [
                'foo' => 'bar'
            ]
        ]);

        $this->assertEquals('bar', option('foo'));
        $this->assertEquals('fallback', option('does-not-exist', 'fallback'));
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
        $this->assertEquals('home', $page->slug());

        // get the current page after changing the current page
        $app->site()->visit('test');
        $page = page();
        $this->assertEquals('test', $page->slug());

        // get a specific page
        $page = page('test');
        $this->assertEquals('test', $page->slug());
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

        $this->assertEquals('current', param('filter'));

        Uri::$current = null;
    }

    public function testParams()
    {
        Uri::$current = new Uri('https://getkirby.com/projects/a:value-a/b:value-b');

        $app = $this->kirby->clone();

        $this->assertEquals(['a' => 'value-a', 'b' => 'value-b'], params());

        Uri::$current = null;
    }

    public function testRHelper()
    {
        $this->assertEquals('a', r(1 === 1, 'a', 'b'));
        $this->assertEquals('b', r(1 === 2, 'a', 'b'));
        $this->assertEquals(null, r(1 === 2, 'a'));
    }

    public function testSiteHelper()
    {
        $this->assertEquals($this->kirby->site(), site());
    }

    public function testSize()
    {
        // number
        $this->assertEquals(3, size(3));

        // string
        $this->assertEquals(3, size('abc'));

        // array
        $this->assertEquals(3, size(['a', 'b', 'c']));

        // collection
        $this->assertEquals(3, size(new Collection(['a', 'b', 'c'])));
    }

    public function testSmartypants()
    {
        $text     = smartypants('"Test"');
        $expected = '&#8220;Test&#8221;';

        $this->assertEquals($expected, $text);
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

        $this->assertEquals($expected, $text);
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
        $this->assertEquals('Hello world', $result);
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
        $this->assertEquals('Hello world', $result);
    }

    public function testSvg()
    {
        $result = svg('test.svg');
        $this->assertEquals('<svg>test</svg>', trim($result));
    }

    public function testSvgWithAbsolutePath()
    {
        $result = svg(__DIR__ . '/fixtures/HelpersTest/test.svg');
        $this->assertEquals('<svg>test</svg>', trim($result));
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

        $this->assertEquals('test', svg($file));
    }

    public function testTwitter()
    {
        // simple
        $result   = twitter('getkirby');
        $expected = '<a href="https://twitter.com/getkirby">@getkirby</a>';

        $this->assertEquals($expected, $result);

        // with attributes
        $result   = twitter('getkirby', 'Follow us', 'Kirby on Twitter', 'twitter');
        $expected = '<a class="twitter" href="https://twitter.com/getkirby" title="Kirby on Twitter">Follow us</a>';

        $this->assertEquals($expected, $result);
    }

    public function testUrlHelper()
    {
        $app = $this->kirby->clone([
            'urls' => [
                'index' => $url = 'https://getkirby.com'
            ]
        ]);

        $this->assertEquals($url . '/test', url('test'));
        $this->assertEquals($url . '/test', u('test'));
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

        $this->assertEquals($expected, url('test', $options));
        $this->assertEquals($expected, u('test', $options));
    }

    public function testVideo()
    {
        $video    = video('https://www.youtube.com/watch?v=xB3s_f7PzYk');
        $expected = '<iframe allowfullscreen src="https://youtube.com/embed/xB3s_f7PzYk"></iframe>';

        $this->assertEquals($expected, $video);
    }

    public function testYoutubeVideoWithOptions()
    {
        $video = video('https://www.youtube.com/watch?v=xB3s_f7PzYk', [
            'youtube' => [
                'controls' => 0
            ]
        ]);

        $expected = '<iframe allowfullscreen src="https://youtube.com/embed/xB3s_f7PzYk?controls=0"></iframe>';

        $this->assertEquals($expected, $video);
    }

    public function testVimeoVideoWithOptions()
    {
        $video = video('https://vimeo.com/335292911', [
            'vimeo' => [
                'controls' => 0
            ]
        ]);

        $expected = '<iframe allowfullscreen src="https://player.vimeo.com/video/335292911?controls=0"></iframe>';

        $this->assertEquals($expected, $video);
    }

    public function testVimeo()
    {
        $video    = vimeo('https://vimeo.com/335292911');
        $expected = '<iframe allowfullscreen src="https://player.vimeo.com/video/335292911"></iframe>';

        $this->assertEquals($expected, $video);
    }

    public function testVimeoWithOptions()
    {
        $video    = vimeo('https://vimeo.com/335292911', ['controls' => 0]);
        $expected = '<iframe allowfullscreen src="https://player.vimeo.com/video/335292911?controls=0"></iframe>';

        $this->assertEquals($expected, $video);
    }

    public function testWidont()
    {
        $result   = widont('This is a headline');
        $expected = 'This is a&nbsp;headline';

        $this->assertEquals($expected, $result);
    }

    public function testYoutube()
    {
        $video    = youtube('https://www.youtube.com/watch?v=xB3s_f7PzYk');
        $expected = '<iframe allowfullscreen src="https://youtube.com/embed/xB3s_f7PzYk"></iframe>';

        $this->assertEquals($expected, $video);
    }

    public function testYoutubeWithOptions()
    {
        $video    = youtube('https://www.youtube.com/watch?v=xB3s_f7PzYk', ['controls' => 0]);
        $expected = '<iframe allowfullscreen src="https://youtube.com/embed/xB3s_f7PzYk?controls=0"></iframe>';

        $this->assertEquals($expected, $video);
    }
}
