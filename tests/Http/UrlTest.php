<?php

namespace Kirby\Http;

use Kirby\Cms\App;
use Kirby\TestCase;

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

	public function testCurrent()
	{
		$this->assertSame('/', Url::current());

		Url::$current = $this->_yts;
		$this->assertSame($this->_yts, Url::current());
	}

	public function testCurrentDir()
	{
		Url::$current = $this->_yts;
		$this->assertSame('https://www.youtube.com', Url::currentDir());
	}

	public function testHome()
	{
		$this->assertSame('/', Url::home());
	}

	public function testTo()
	{
		$this->assertSame('/', Url::to());
		$this->assertSame($this->_yt, Url::to($this->_yt));
		$this->assertSame('/getkirby.com', Url::to('getkirby.com'));
		$this->assertSame('./something', Url::to('./something'));
		$this->assertSame('../something', Url::to('../something'));
	}

	public function testLast()
	{
		$this->assertSame('', Url::last());
	}

	public function testBuild()
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

	public function testIsAbsolute()
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

	public function testMakeAbsolute()
	{
		$this->assertSame('http://getkirby.com', Url::makeAbsolute('http://getkirby.com'));
		$this->assertSame('/docs/cheatsheet', Url::makeAbsolute('docs/cheatsheet'));
		$this->assertSame('http://getkirby.com/docs/cheatsheet', Url::makeAbsolute('docs/cheatsheet', 'http://getkirby.com'));
		$this->assertSame('http://getkirby.com', Url::makeAbsolute('', 'http://getkirby.com'));
	}

	public function testFix()
	{
		$this->assertSame('http://', Url::fix());
		$this->assertSame('http://', Url::fix(''));
		$this->assertSame('http://getkirby.com', Url::fix('getkirby.com'));
		$this->assertSame('ftp://getkirby.com', Url::fix('ftp://getkirby.com'));
	}

	public function testBase()
	{
		$this->assertNull(Url::base());
		$this->assertSame('http://getkirby.com', Url::base('http://getkirby.com/docs/cheatsheet'));
	}

	public function testPath()
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

	public function testStripPath()
	{
		$this->assertSame('https://getkirby.com', Url::stripPath('https://getkirby.com/a/b'));
		$this->assertSame('https://getkirby.com/', Url::stripPath('https://getkirby.com/a/b/'));
	}

	public function testStripQuery()
	{
		$this->assertSame('https://getkirby.com', Url::stripQuery('https://getkirby.com?a=b'));
		$this->assertSame('https://getkirby.com/', Url::stripQuery('https://getkirby.com/?a=b'));
	}

	public function testStripFragment()
	{
		$this->assertSame('https://getkirby.com', Url::stripFragment('https://getkirby.com#a/b'));
		$this->assertSame('https://getkirby.com/', Url::stripFragment('https://getkirby.com/#a/b'));
	}

	public function testQuery()
	{
		$this->assertSame('', Url::query('https://getkirby.com'));
		$this->assertSame('a=b', Url::query('?a=b'));
		$this->assertSame('a=b', Url::query('https://getkirby.com?a=b'));
		$this->assertSame('a=b', Url::query('https://getkirby.com/?a=b'));
	}

	public function testShort()
	{
		$this->assertSame('getkirby.com/docs', Url::short($this->_docs));
		$this->assertSame('getkirby.com/docs', Url::short($this->_docs, 100));
		$this->assertSame('getkirby.com…', Url::short($this->_docs, 12));
		$this->assertSame('getkirby.com', Url::short($this->_docs, 20, true));
		$this->assertSame('getkirby.com###', Url::short($this->_docs, 12, false, '###'));
	}

	public function testIdn()
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

	/**
	 * @dataProvider scriptNameProvider
	 */
	public function testIndex($host, $scriptName, $expected)
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
