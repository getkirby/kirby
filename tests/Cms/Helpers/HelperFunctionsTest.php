<?php

namespace Kirby\Cms;

use Kirby\Cms\App as Kirby;
use Kirby\Filesystem\Asset;
use Kirby\Filesystem\Dir;
use Kirby\Image\QrCode;
use Kirby\Toolkit\Collection;
use Kirby\Toolkit\Obj;

class HelperFunctionsTest extends HelpersTestCase
{
	public const FIXTURES = __DIR__ . '/fixtures/HelpersTest';
	public const TMP      = KIRBY_TMP_DIR . '/Cms.HelperFunctions';

	public function setUp(): void
	{
		Dir::copy(static::FIXTURES, static::TMP);

		$this->app = new Kirby([
			'roots' => [
				'index' => static::TMP
			],
			'urls' => [
				'index' => 'https://getkirby.com'
			]
		]);

		Dir::make(static::TMP . '/site');
	}

	public function tearDown(): void
	{
		parent::tearDown();
		Dir::remove(static::TMP);
	}

	public function testAttrWithBeforeValue(): void
	{
		$attr = attr(['test' => 'test'], ' ');
		$this->assertSame(' test="test"', $attr);
	}

	public function testAttrWithAfterValue(): void
	{
		$attr = attr(['test' => 'test'], null, ' ');
		$this->assertSame('test="test" ', $attr);
	}

	public function testAttrWithoutValues(): void
	{
		$attr = attr([]);
		$this->assertNull($attr);
	}

	public function testAsset(): void
	{
		$asset = asset('something.jpg');
		$this->assertInstanceOf(Asset::class, $asset);
	}

	public function testCollection(): void
	{
		$this->app->clone([
			'site' => [
				'children' => [
					['slug' => 'test']
				]
			],
			'collections' => [
				'test'    => fn ($pages) => $pages,
				'options' => fn (int $b, int $a) => $a + $b
			]
		]);

		$collection = collection('test');
		$this->assertCount(1, $collection);
		$this->assertSame('test', $collection->first()->slug());

		$collection = collection('options', ['a' => 5, 'b' => 3]);
		$this->assertSame(8, $collection);
	}

	public function testCsrf(): void
	{
		$session = $this->app->session();

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

	public function testCss(): void
	{
		$result   = css('assets/css/index.css');
		$expected = '<link href="https://getkirby.com/assets/css/index.css" rel="stylesheet">';

		$this->assertSame($expected, $result);
	}

	public function testCssWithMediaOption(): void
	{
		$result   = css('assets/css/index.css', 'print');
		$expected = '<link href="https://getkirby.com/assets/css/index.css" media="print" rel="stylesheet">';

		$this->assertSame($expected, $result);
	}

	public function testCssWithAttrs(): void
	{
		$result   = css('assets/css/index.css', ['integrity' => 'nope']);
		$expected = '<link href="https://getkirby.com/assets/css/index.css" integrity="nope" rel="stylesheet">';

		$this->assertSame($expected, $result);
	}

	public function testCssWithValidRelAttr(): void
	{
		$result   = css('assets/css/index.css', ['rel' => 'alternate stylesheet', 'title' => 'High contrast']);
		$expected = '<link href="https://getkirby.com/assets/css/index.css" rel="alternate stylesheet" title="High contrast">';

		$this->assertSame($expected, $result);
	}

	public function testCssWithInvalidRelAttr(): void
	{
		$result   = css('assets/css/index.css', ['rel' => 'alternate', 'title' => 'High contrast']);
		$expected = '<link href="https://getkirby.com/assets/css/index.css" rel="stylesheet" title="High contrast">';

		$this->assertSame($expected, $result);
	}

	public function testCssWithRelAttrButNoTitle(): void
	{
		$result   = css('assets/css/index.css', ['rel' => 'alternate stylesheet']);
		$expected = '<link href="https://getkirby.com/assets/css/index.css" rel="stylesheet">';

		$this->assertSame($expected, $result);
	}

	public function testCssWithArray(): void
	{
		$result = css([
			'assets/css/a.css',
			'assets/css/b.css'
		]);

		$expected  = '<link href="https://getkirby.com/assets/css/a.css" rel="stylesheet">' . PHP_EOL;
		$expected .= '<link href="https://getkirby.com/assets/css/b.css" rel="stylesheet">';

		$this->assertSame($expected, $result);
	}

	public function testDeprecated(): void
	{
		$this->assertError(
			E_USER_DEPRECATED,
			'The xyz method is deprecated.',
			fn () => deprecated('The xyz method is deprecated.')
		);
	}

	public function testDumpOnCli(): void
	{
		if (KIRBY_DUMP_OVERRIDDEN === true) {
			$this->markTestSkipped('The dump() helper was externally overridden.');
		}

		$this->assertSame("test\n", dump('test', false));

		$this->expectOutputString("test1\ntest2\n");
		dump('test1');
		dump('test2', true);
	}

	public function testDumpOnServer(): void
	{
		if (KIRBY_DUMP_OVERRIDDEN === true) {
			$this->markTestSkipped('The dump() helper was externally overridden.');
		}

		$this->app = $this->app->clone([
			'cli' => false
		]);

		$this->assertSame('<pre>test</pre>', dump('test', false));

		$this->expectOutputString('<pre>test1</pre><pre>test2</pre>');
		dump('test1');
		dump('test2', true);
	}

	public function testE(): void
	{
		$this->expectOutputString('ad');

		e(1 === 1, 'a', 'b');
		e(1 === 2, 'c', 'd');
		e(1 === 2, 'e');
	}

	public function testEscWithInvalidContext(): void
	{
		$escaped = esc('test', 'does-not-exist');
		$this->assertSame('test', $escaped);
	}

	public function testGist(): void
	{
		$gist     = gist('https://gist.github.com/bastianallgeier/d61ab782cd5c2cc02b6f6fec54fd1985', 'static.php');
		$expected = '<script src="https://gist.github.com/bastianallgeier/d61ab782cd5c2cc02b6f6fec54fd1985.js?file=static.php"></script>';

		$this->assertSame($gist, $expected);
	}

	public function testH(): void
	{
		$html = h('Guns & Roses');
		$this->assertSame('Guns &amp; Roses', $html);
	}

	public function testHtml(): void
	{
		$html = html('Guns & Roses');
		$this->assertSame('Guns &amp; Roses', $html);
	}

	public function testImage(): void
	{
		$app = $this->app->clone([
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
		$this->assertIsFile($image);

		$image = image('/sitefile.jpg');
		$this->assertIsFile($image);

		// get the first image of the current page
		$app->site()->visit('test');
		$image = image();
		$this->assertIsFile($image);

		$image = image('pagefile.jpg');
		$this->assertIsFile($image);

		$image = image('does-not-exist.jpg');
		$this->assertNull($image);
	}

	public function testInvalid(): void
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

	public function testInvalidSimple(): void
	{
		$data   = ['homer', null];
		$rules  = [['alpha'], ['required']];
		$result = invalid($data, $rules);
		$this->assertSame(1, $result[1]);
	}

	public function testInvalidRequired(): void
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

	public function testInvalidOptions(): void
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

	public function testInvalidWithMultipleMessages(): void
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

	public function testJs(): void
	{
		$result   = js('assets/js/index.js');
		$expected = '<script src="https://getkirby.com/assets/js/index.js"></script>';

		$this->assertSame($expected, $result);
	}

	public function testJsWithAsyncOption(): void
	{
		$result   = js('assets/js/index.js', true);
		$expected = '<script async src="https://getkirby.com/assets/js/index.js"></script>';

		$this->assertSame($expected, $result);
	}

	public function testJsWithAttrs(): void
	{
		$result   = js('assets/js/index.js', ['integrity' => 'nope']);
		$expected = '<script integrity="nope" src="https://getkirby.com/assets/js/index.js"></script>';

		$this->assertSame($expected, $result);
	}

	public function testJsWithArray(): void
	{
		$result = js([
			'assets/js/a.js',
			'assets/js/b.js'
		]);

		$expected  = '<script src="https://getkirby.com/assets/js/a.js"></script>' . PHP_EOL;
		$expected .= '<script src="https://getkirby.com/assets/js/b.js"></script>';

		$this->assertSame($expected, $result);
	}

	public function testKirby(): void
	{
		$this->assertSame($this->app, kirby());
	}

	public function testKirbyTag(): void
	{
		$tag = kirbytag('link', 'https://getkirby.com', ['text' => 'Kirby']);
		$expected = '<a href="https://getkirby.com">Kirby</a>';

		$this->assertSame($expected, $tag);
	}

	public function testKirbyTags(): void
	{
		$tag = kirbytags('(link: https://getkirby.com text: Kirby)');
		$expected = '<a href="https://getkirby.com">Kirby</a>';

		$this->assertSame($expected, $tag);
	}

	public function testKirbyText(): void
	{
		$text     = 'This is **just** a text.';
		$expected = '<p>This is <strong>just</strong> a text.</p>';

		$this->assertSame($expected, kirbytext($text));
		$this->assertSame($expected, kt($text));
	}

	public function testKirbyTextWithSafeMode(): void
	{
		$text     = '<h1>Kirby</h1>';
		$expected = '<p>&lt;h1&gt;Kirby&lt;/h1&gt;</p>';

		$this->assertSame($expected, kirbytext($text, ['markdown' => ['safe' => true]]));
		$this->assertSame($expected, kt($text, ['markdown' => ['safe' => true]]));
	}

	public function testKirbyTextInline(): void
	{
		$text     = 'This is **just** a text.';
		$expected = 'This is <strong>just</strong> a text.';

		$this->assertSame($expected, kirbytextinline($text));
		$this->assertSame($expected, kti($text));
	}

	public function testKirbyTextInlineWithSafeMode(): void
	{
		$text     = 'This is <b>just</b> a text.';
		$expected = 'This is &lt;b&gt;just&lt;/b&gt; a text.';

		$this->assertSame($expected, kirbytextinline($text, ['markdown' => ['safe' => true]]));
		$this->assertSame($expected, kti($text, ['markdown' => ['safe' => true]]));
	}

	public function testLoad(): void
	{
		load([
			'helperstest\\a' => static::FIXTURES . '/load/a/a.php',
		]);

		load([
			'HelpersTest\\B' => 'B.php',
		], static::FIXTURES . '/load/B');

		$this->assertTrue(class_exists('HelpersTest\\A'));
		$this->assertTrue(class_exists('HelpersTest\\B'));
		$this->assertFalse(class_exists('HelpersTest\\C'));
	}

	public function testMarkdown(): void
	{
		$tag = markdown('# Kirby');
		$expected = '<h1>Kirby</h1>';

		$this->assertSame($expected, $tag);
	}

	public function testMarkdownWithSafeMode(): void
	{
		$tag = markdown('<h1>Kirby</h1>', ['safe' => true]);
		$expected = '<p>&lt;h1&gt;Kirby&lt;/h1&gt;</p>';

		$this->assertSame($expected, $tag);
	}

	public function testOption(): void
	{
		$app = $this->app->clone([
			'options' => [
				'foo' => 'bar'
			]
		]);

		$this->assertSame('bar', option('foo'));
		$this->assertSame('fallback', option('does-not-exist', 'fallback'));
	}

	public function testPage(): void
	{
		$app = $this->app->clone([
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

	public function testPages(): void
	{
		$app = $this->app->clone([
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

	public function testParam(): void
	{
		$this->app->clone([
			'server' => [
				'REQUEST_URI' => '/projects/filter:current/b%2Fb%3A:value-B%2FB%3A'
			]
		]);

		$this->assertSame('current', param('filter'));
		$this->assertSame('value-B/B:', param('b/b:'));
	}

	public function testParams(): void
	{
		$this->app->clone([
			'server' => [
				'REQUEST_URI' => '/projects/a:value-a/b%2Fb%3A:value-B%2FB%3A?foo=path'
			],
		]);

		$this->assertSame(['a' => 'value-a', 'b/b:' => 'value-B/B:'], params());
	}

	public function testQr(): void
	{
		$url = 'https://getkirby.com';
		$qr    = qr($url);

		$this->assertInstanceOf(QrCode::class, $qr);
		$this->assertSame($url, $qr->data);

		$app = $this->app->clone([
			'site' => [
				'children' => [
					[
						'slug' => 'test',
					]
				]
			]
		]);
		$page  = $app->page('test');
		$qr    = qr($page);

		$this->assertInstanceOf(QrCode::class, $qr);
		$this->assertSame($page->url(), $qr->data);
	}

	public function testR(): void
	{
		$this->assertSame('a', r(1 === 1, 'a', 'b'));
		$this->assertSame('b', r(1 === 2, 'a', 'b'));
		$this->assertNull(r(1 === 2, 'a'));
	}

	public function testRouter(): void
	{
		$routes = [
			[
				'pattern' => 'a/(:any)',
				'method'  => 'POST',
				'action'  => fn () => 'nonono'
			],
			[
				'pattern' => 'b/(:any)',
				'method'  => 'ALL',
				'action'  => fn () => 'nonono'
			],
			[
				'pattern' => 'a/(:any)',
				'method'  => 'GET',
				'action'  => fn ($path) => 'yes: ' . $path
			]
		];

		$result = router('a/foo', 'GET', $routes);
		$this->assertSame('yes: foo', $result);
	}

	public function testSite(): void
	{
		$this->assertSame($this->app->site(), site());
	}

	public function testSize(): void
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

	public function testSmartypants(): void
	{
		$text     = smartypants('"Test"');
		$expected = '&#8220;Test&#8221;';

		$this->assertSame($expected, $text);
	}

	public function testSmartypantsWithKirbytext(): void
	{
		$this->app->clone([
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

	public function testSnippet(): void
	{
		$this->app->clone([
			'roots' => [
				'index'     => '/dev/null',
				'snippets'  => static::FIXTURES,
			]
		]);

		$result = snippet('snippet', ['message' => 'world'], true);
		$this->assertSame('Hello world', $result);
	}

	public function testSnippetNullArgument(): void
	{
		$this->app->clone([
			'roots' => [
				'index'     => '/dev/null',
				'snippets'  => static::FIXTURES,
			]
		]);

		$result = snippet(null, ['message' => 'world'], true);
		$this->assertSame('', $result);
	}

	public function testSnippetNotExists(): void
	{
		$this->app->clone([
			'roots' => [
				'index'     => '/dev/null',
				'snippets'  => static::FIXTURES,
			]
		]);

		$result = snippet('not-exist', ['message' => 'world'], true);
		$this->assertSame('', $result);
	}

	public function testSnippetAlternatives(): void
	{
		$this->app->clone([
			'roots' => [
				'index'     => '/dev/null',
				'snippets'  => static::FIXTURES,
			]
		]);

		$result = snippet(
			[null, 'does-not-exist', 'does-not-exist-either', 'snippet'],
			['message' => 'world'],
			true
		);
		$this->assertSame('Hello world', $result);
	}

	public function testSnippetObject(): void
	{
		$this->app->clone([
			'roots' => [
				'index'     => '/dev/null',
				'snippets'  => static::FIXTURES,
			]
		]);

		$result = snippet('snippet-item', new Obj(['method' => 'another world']), true);
		$this->assertSame('Hello another world', $result);
	}

	public function testSnippetEcho(): void
	{
		$this->expectOutputString('Hello world');

		$this->app->clone([
			'roots' => [
				'index'     => '/dev/null',
				'snippets'  => static::FIXTURES,
			]
		]);

		snippet('snippet', ['message' => 'world']);
	}

	public function testSnippetWithSlots(): void
	{
		$this->app->clone([
			'roots' => [
				'snippets' => static::FIXTURES
			]
		]);

		ob_start();

		snippet('snippet-slots', slots: true);
		slot();
		echo 'Test';
		endslot();
		endsnippet();

		$this->assertSame('Test', ob_get_clean());
	}

	public function testSvg(): void
	{
		$result = svg('test.svg');
		$this->assertSame('<svg>test</svg>', trim($result));
	}

	public function testSvgWithAbsolutePath(): void
	{
		$result = svg(static::FIXTURES . '/test.svg');
		$this->assertSame('<svg>test</svg>', trim($result));
	}

	public function testSvgWithInvalidFileType(): void
	{
		$this->assertFalse(svg(123));
	}

	public function testSvgWithMissingFile(): void
	{
		$this->assertFalse(svg('somefile.svg'));
	}

	public function testSvgWithFileObject(): void
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

	public function testTimestamp(): void
	{
		$result = timestamp('2021-12-12 12:12:12');
		$this->assertSame('2021-12-12 12:12:12', date('Y-m-d H:i:s', $result));
	}

	public function testTimestampWithStep(): void
	{
		$result = timestamp('2021-12-12 12:12:12', [
			'unit' => 'minute',
			'size' => 5
		]);

		$this->assertSame('2021-12-12 12:10:00', date('Y-m-d H:i:s', $result));
	}

	public function testTimestampWithInvalidDate(): void
	{
		$result = timestamp('invalid date');
		$this->assertNull($result);
	}

	public function testTc(): void
	{
		$this->app->clone([
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

	public function testTcWithPlaceholders(): void
	{
		$this->app->clone([
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

	public function testUrl(): void
	{
		$this->app->clone([
			'options' => [
				'url' => $url = 'https://getkirby.com'
			]
		]);

		$this->assertSame($url . '/test', url('test'));
		$this->assertSame($url . '/test', u('test'));
	}

	public function testUrlWithOptions(): void
	{
		$this->app->clone([
			'options' => [
				'url' => $url = 'https://getkirby.com'
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

	public function testVideo(): void
	{
		$video    = video('https://youtube.com/watch?v=xB3s_f7PzYk');
		$expected = '<iframe allow="fullscreen" allowfullscreen src="https://youtube.com/embed/xB3s_f7PzYk"></iframe>';

		$this->assertSame($expected, $video);
	}

	public function testYoutubeVideoWithOptions(): void
	{
		$video = video('https://youtube.com/watch?v=xB3s_f7PzYk', [
			'youtube' => [
				'controls' => 0
			]
		]);

		$expected = '<iframe allow="fullscreen" allowfullscreen src="https://youtube.com/embed/xB3s_f7PzYk?controls=0"></iframe>';

		$this->assertSame($expected, $video);
	}

	public function testVimeoVideoWithOptions(): void
	{
		$video = video('https://vimeo.com/335292911', [
			'vimeo' => [
				'controls' => 0
			]
		]);

		$expected = '<iframe allow="fullscreen" allowfullscreen src="https://player.vimeo.com/video/335292911?controls=0"></iframe>';

		$this->assertSame($expected, $video);
	}

	public function testVimeo(): void
	{
		$video    = vimeo('https://vimeo.com/335292911');
		$expected = '<iframe allow="fullscreen" allowfullscreen src="https://player.vimeo.com/video/335292911"></iframe>';

		$this->assertSame($expected, $video);
	}

	public function testVimeoWithOptions(): void
	{
		$video    = vimeo('https://vimeo.com/335292911', ['controls' => 0]);
		$expected = '<iframe allow="fullscreen" allowfullscreen src="https://player.vimeo.com/video/335292911?controls=0"></iframe>';

		$this->assertSame($expected, $video);
	}

	public function testWidont(): void
	{
		$result   = widont('This is a headline');
		$expected = 'This is a&nbsp;headline';

		$this->assertSame($expected, $result);
	}

	public function testYoutube(): void
	{
		$video    = youtube('https://youtube.com/watch?v=xB3s_f7PzYk');
		$expected = '<iframe allow="fullscreen" allowfullscreen src="https://youtube.com/embed/xB3s_f7PzYk"></iframe>';

		$this->assertSame($expected, $video);
	}

	public function testYoutubeWithOptions(): void
	{
		$video    = youtube('https://youtube.com/watch?v=xB3s_f7PzYk', ['controls' => 0]);
		$expected = '<iframe allow="fullscreen" allowfullscreen src="https://youtube.com/embed/xB3s_f7PzYk?controls=0"></iframe>';

		$this->assertSame($expected, $video);
	}
}
