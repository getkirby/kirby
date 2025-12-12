<?php

namespace Kirby\Http;

use Kirby\Cms\App;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(Url::class)]
class UrlTest extends TestCase
{
	protected string $_yt   = 'http://www.youtube.com/watch?v=9q_aXttJduk';
	protected string $_yts  = 'https://www.youtube.com/watch?v=9q_aXttJduk';
	protected string $_docs = 'http://getkirby.com/docs/';
	protected $_SERVER = null;

	public function setUp(): void
	{
		Uri::$current = null;
		Url::$current = null;
		Url::$home    = '/';
	}

	public function testCurrent(): void
	{
		$this->assertSame('/', Url::current());

		Url::$current = $this->_yts;
		$this->assertSame($this->_yts, Url::current());
	}

	public function testCurrentDir(): void
	{
		Url::$current = $this->_yts;
		$this->assertSame('https://www.youtube.com', Url::currentDir());
	}

	public function testEditor(): void
	{
		$file = '/test/index.php';

		$this->assertSame(
			'vscode://file/%2Ftest%2Findex.php:23',
			Url::editor(editor: 'vscode', file: $file, line: 23)
		);

		$this->assertNull(Url::editor(editor: false, file: $file, line: 23));
		$this->assertNull(Url::editor(editor: 'vscode', file: null, line: 23));
	}

	public function testHome(): void
	{
		$this->assertSame('/', Url::home());
	}

	public function testTo(): void
	{
		$this->assertSame('/', Url::to());
		$this->assertSame($this->_yt, Url::to($this->_yt));
		$this->assertSame('/getkirby.com', Url::to('getkirby.com'));
		$this->assertSame('./something', Url::to('./something'));
		$this->assertSame('../something', Url::to('../something'));
	}

	public function testLast(): void
	{
		$this->assertSame('', Url::last());
	}

	public function testBuild(): void
	{
		$this->assertSame('/', Url::build());

		Url::$current = $this->_yts;

		// build with defaults
		$this->assertSame('https://www.youtube.com/watch?v=9q_aXttJduk', Url::build());

		// build with different host
		$this->assertSame('https://kirbyvideo.com/watch?v=9q_aXttJduk', Url::build(['host' => 'kirbyvideo.com']));

		// build from parts
		$parts = [
			'path'     => ['hello', 'kitty', 'mickey', 'mouse'],
			'query'    => ['get' => 'kirby'],
			'fragment' => 'foo'
		];
		$result = 'http://getkirby.com/hello/kitty/mickey/mouse?get=kirby#foo';
		$this->assertSame($result, Url::build($parts, 'http://getkirby.com'));
	}

	public function testIsAbsolute(): void
	{
		$this->assertTrue(Url::isAbsolute('http://getkirby.com/docs'));
		$this->assertTrue(Url::isAbsolute('https://getkirby.com/docs'));
		$this->assertTrue(Url::isAbsolute('//getkirby.com/docs'));
		$this->assertTrue(Url::isAbsolute('mailto:mail@getkirby.com'));
		$this->assertTrue(Url::isAbsolute('tel:1234567'));
		$this->assertTrue(Url::isAbsolute('geo:49.0158,8.3239?z=11'));
		$this->assertFalse(Url::isAbsolute('../getkirby.com/docs'));
		$this->assertFalse(Url::isAbsolute('javascript:alert("XSS")'));
	}

	public function testMakeAbsolute(): void
	{
		$this->assertSame('http://getkirby.com', Url::makeAbsolute('http://getkirby.com'));
		$this->assertSame('/docs/cheatsheet', Url::makeAbsolute('docs/cheatsheet'));
		$this->assertSame('http://getkirby.com/docs/cheatsheet', Url::makeAbsolute('docs/cheatsheet', 'http://getkirby.com'));
		$this->assertSame('http://getkirby.com', Url::makeAbsolute('', 'http://getkirby.com'));
	}

	public function testFix(): void
	{
		$this->assertSame('http://', Url::fix());
		$this->assertSame('http://', Url::fix(''));
		$this->assertSame('http://getkirby.com', Url::fix('getkirby.com'));
		$this->assertSame('ftp://getkirby.com', Url::fix('ftp://getkirby.com'));
	}

	public function testBase(): void
	{
		$this->assertNull(Url::base());
		$this->assertSame('http://getkirby.com', Url::base('http://getkirby.com/docs/cheatsheet'));
	}

	public function testPath(): void
	{
		// stripped
		$this->assertSame('', Url::path('https://getkirby.com'));
		$this->assertSame('', Url::path('https://getkirby.com/'));
		$this->assertSame('a/b', Url::path('a/b'));
		$this->assertSame('a/b', Url::path('https://getkirby.com/a/b'));
		$this->assertSame('a/b', Url::path('https://getkirby.com/a/b/'));

		// leading slash
		$this->assertSame('', Url::path('https://getkirby.com', true));
		$this->assertSame('', Url::path('https://getkirby.com/', true));
		$this->assertSame('/a/b', Url::path('a/b', true));
		$this->assertSame('/a/b', Url::path('https://getkirby.com/a/b', true));
		$this->assertSame('/a/b', Url::path('https://getkirby.com/a/b/', true));

		// trailing slash
		$this->assertSame('', Url::path('https://getkirby.com', false, true));
		$this->assertSame('', Url::path('https://getkirby.com/', false, true));
		$this->assertSame('a/b/', Url::path('a/b', false, true));
		$this->assertSame('a/b/', Url::path('https://getkirby.com/a/b', false, true));
		$this->assertSame('a/b/', Url::path('https://getkirby.com/a/b/', false, true));

		// leading and trailing slash
		$this->assertSame('', Url::path('https://getkirby.com', true, true));
		$this->assertSame('', Url::path('https://getkirby.com/', true, true));
		$this->assertSame('/a/b/', Url::path('a/b', true, true));
		$this->assertSame('/a/b/', Url::path('https://getkirby.com/a/b', true, true));
		$this->assertSame('/a/b/', Url::path('https://getkirby.com/a/b/', true, true));
	}

	public function testStripPath(): void
	{
		$this->assertSame('https://getkirby.com', Url::stripPath('https://getkirby.com/a/b'));
		$this->assertSame('https://getkirby.com/', Url::stripPath('https://getkirby.com/a/b/'));
	}

	public function testStripQuery(): void
	{
		$this->assertSame('https://getkirby.com', Url::stripQuery('https://getkirby.com?a=b'));
		$this->assertSame('https://getkirby.com/', Url::stripQuery('https://getkirby.com/?a=b'));
	}

	public function testStripFragment(): void
	{
		$this->assertSame('https://getkirby.com', Url::stripFragment('https://getkirby.com#a/b'));
		$this->assertSame('https://getkirby.com/', Url::stripFragment('https://getkirby.com/#a/b'));
	}

	public function testQuery(): void
	{
		$this->assertSame('', Url::query('https://getkirby.com'));
		$this->assertSame('a=b', Url::query('?a=b'));
		$this->assertSame('a=b', Url::query('https://getkirby.com?a=b'));
		$this->assertSame('a=b', Url::query('https://getkirby.com/?a=b'));
	}

	public function testShort(): void
	{
		$this->assertSame('getkirby.com/docs', Url::short($this->_docs));
		$this->assertSame('getkirby.com/docs', Url::short($this->_docs, 100));
		$this->assertSame('getkirby.com…', Url::short($this->_docs, 12));
		$this->assertSame('getkirby.com', Url::short($this->_docs, 20, true));
		$this->assertSame('getkirby.com###', Url::short($this->_docs, 12, false, '###'));
	}

	public function testIdn(): void
	{
		$object = Url::idn('https://xn--tst-qla.de');
		$this->assertInstanceOf(Uri::class, $object);
		$this->assertSame('https://täst.de', $object->toString());
	}

	public static function scriptNameProvider(): array
	{
		return [
			[null, 'index.php', '/'],
			[null, '/index.php', '/'],
			[null, '', '/'],
			[null, '/', '/'],
			[null, '/kirby/index.php', '/kirby'],
			[null, 'kirby/index.php', '/kirby'],
			[null, '/kirby/super.php', '/kirby'],
			[null, 'kirby/super.php', '/kirby'],
			[null, 'kirby\super.php', '/kirby'],

			['localhost', 'index.php', 'http://localhost'],
			['localhost', '/index.php', 'http://localhost'],
			['localhost', '', 'http://localhost'],
			['localhost', '/', 'http://localhost'],
			['localhost', '/kirby/index.php', 'http://localhost/kirby'],
			['localhost', 'kirby/index.php', 'http://localhost/kirby'],
			['localhost', '/kirby/super.php', 'http://localhost/kirby'],
			['localhost', 'kirby/super.php', 'http://localhost/kirby'],
			['localhost', 'kirby\super.php', 'http://localhost/kirby'],
		];
	}

	#[DataProvider('scriptNameProvider')]
	public function testIndex($host, $scriptName, $expected): void
	{
		new App([
			'cli' => false,
			'roots' => [
				'index' => '/dev/null'
			],
			'server' => [
				'SERVER_NAME' => $host,
				'SCRIPT_NAME' => $scriptName
			]
		]);

		$this->assertSame($expected, Url::index());
	}
}
