<?php

namespace Kirby\Http;

use Kirby\Cms\App;
use Kirby\Http\Request\Auth\BasicAuth;
use Kirby\Http\Request\Auth\BearerAuth;
use Kirby\Http\Request\Body;
use Kirby\Http\Request\Files;
use Kirby\Http\Request\Query;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class RequestTest extends TestCase
{
	public function tearDown(): void
	{
		App::destroy();
	}

	public function testCustomProps(): void
	{
		$file = [
			'name'     => 'test.txt',
			'tmp_name' => '/tmp/abc',
			'size'     => 123,
			'error'    => 0
		];

		// simple
		$request = new Request([
			'method' => 'POST',
			'body'   => ['a' => 'a'],
			'query'  => ['b' => 'b'],
			'files'  => ['upload' => $file],
			'url'    => 'https://getkirby.com'
		]);

		$this->assertTrue($request->is('POST'));
		$this->assertSame('a', $request->body()->get('a'));
		$this->assertSame('b', $request->query()->get('b'));
		$this->assertSame($file, $request->file('upload'));
		$this->assertSame('https://getkirby.com', $request->url()->toString());

		// with instances
		$request = new Request([
			'method' => 'POST',
			'body'   => new Body(['a' => 'a']),
			'query'  => new Query(['b' => 'b']),
			'files'  => new Files(['upload' => $file]),
			'url'    => new Uri('https://getkirby.com')
		]);

		$this->assertTrue($request->is('POST'));
		$this->assertSame('a', $request->body()->get('a'));
		$this->assertSame('b', $request->query()->get('b'));
		$this->assertSame($file, $request->file('upload'));
		$this->assertSame('https://getkirby.com', $request->url()->toString());
	}

	public function testData(): void
	{
		$request = new Request([
			'body'   => ['a' => 'a'],
			'query'  => ['b' => 'b'],
		]);

		$this->assertSame(['a' => 'a', 'b' => 'b'], $request->data());
		$this->assertSame('a', $request->get('a'));
		$this->assertSame('b', $request->get('b'));
		$this->assertNull($request->get('c'));
	}

	public function testDataNumeric(): void
	{
		$request = new Request([
			'body'   => [1 => 'a'],
			'query'  => [
				'0' => 'b',
				2   => 'c'
			]
		]);

		$this->assertSame([1 => 'a', 0 => 'b', 2 => 'c'], $request->data());
		$this->assertSame('b', $request->get(0));
		$this->assertSame('b', $request->get('0'));
		$this->assertSame('a', $request->get(1));
		$this->assertSame('a', $request->get('1'));
		$this->assertSame('c', $request->get(2));
		$this->assertSame('c', $request->get('2'));
	}

	public function test__debuginfo(): void
	{
		$request = new Request();
		$info    = $request->__debugInfo();

		$this->assertArrayHasKey('body', $info);
		$this->assertArrayHasKey('query', $info);
		$this->assertArrayHasKey('files', $info);
		$this->assertArrayHasKey('method', $info);
		$this->assertArrayHasKey('url', $info);
	}

	public function testAuthMissing(): void
	{
		$request = new Request();
		$this->assertFalse($request->auth());
	}

	public function testBasicAuth(): void
	{
		new App([
			'server' => [
				'HTTP_AUTHORIZATION' => 'Basic ' . base64_encode($credentials = 'testuser:testpass')
			]
		]);

		$request = new Request();

		$this->assertInstanceOf(BasicAuth::class, $request->auth());
		$this->assertSame('testuser', $request->auth()->username());
		$this->assertSame('testpass', $request->auth()->password());
	}

	public function testBearerAuth(): void
	{
		new App([
			'server' => [
				'HTTP_AUTHORIZATION' => 'Bearer abcd'
			]
		]);

		$request = new Request();

		$this->assertInstanceOf(BearerAuth::class, $request->auth());
		$this->assertSame('abcd', $request->auth()->token());
	}

	public function testCli(): void
	{
		$request = new Request();
		$this->assertTrue($request->cli());

		$request = new Request(['cli' => true]);
		$this->assertTrue($request->cli());

		$request = new Request(['cli' => false]);
		$this->assertFalse($request->cli());
	}


	public function testUnknownAuth(): void
	{
		new App([
			'server' => [
				'HTTP_AUTHORIZATION' => 'Unknown abcd'
			]
		]);

		$request = new Request();

		$this->assertFalse($request->auth());
	}

	public function testAuthTrack(): void
	{
		$app = new App([
			'roots' => [
				'index' => '/dev/null'
			]
		]);

		$this->assertFalse($app->response()->usesAuth());

		$request = new Request();
		$request->auth();

		$this->assertTrue($app->response()->usesAuth());
	}

	#[DataProvider('hasAuthProvider')]
	public function testHasAuth($option, $header, $expected): void
	{
		new App([
			'server' => [
				'HTTP_AUTHORIZATION' => $header
			]
		]);

		$request = new Request([
			'auth' => $option
		]);

		$this->assertSame($expected, $request->hasAuth());
	}

	public static function hasAuthProvider(): array
	{
		return [
			[null, null, false],
			[null, '', false],
			['', null, false],
			['', '', false],
			['Basic abc', '', true],
			['', 'Basic abc', true],
			['Basic abc', null, true],
			[null, 'Basic abc', true],
			['Basic abc', 'Basic def', true],
		];
	}

	public function testMethod(): void
	{
		$request = new Request();

		$this->assertInstanceOf(Query::class, $request->query());
		$this->assertInstanceOf(Body::class, $request->body());
		$this->assertInstanceOf(Files::class, $request->files());
	}

	public function testQuery(): void
	{
		$request = new Request();
		$this->assertInstanceOf(Query::class, $request->query());
	}

	public function testBody(): void
	{
		$request = new Request();
		$this->assertInstanceOf(Body::class, $request->body());
	}

	public function testFiles(): void
	{
		$request = new Request();
		$this->assertInstanceOf(Files::class, $request->files());
	}

	public function testFile(): void
	{
		$request = new Request();
		$this->assertNull($request->file('test'));
	}

	public function testIs(): void
	{
		$request = new Request();
		$this->assertTrue($request->is('GET'));
	}

	public function testIsWithLowerCaseInput(): void
	{
		$request = new Request();
		$this->assertTrue($request->is('get'));
	}

	public function testUrl(): void
	{
		$request = new Request();
		$this->assertInstanceOf(Uri::class, $request->url());
	}

	public function testUrlUpdates(): void
	{
		$request = new Request();

		$uriBefore = $request->url();

		$clone = $request->url([
			'host'  => 'getkirby.com',
			'path'  => 'yay',
			'query' => ['foo' => 'bar'],
			'slash' => false,
		]);

		$uriAfter = $request->url();

		$this->assertNotSame($uriBefore, $clone);
		$this->assertSame($uriBefore, $uriAfter);
		$this->assertSame('http://getkirby.com/yay?foo=bar', $clone->toString());
	}

	public function testPath(): void
	{
		$request = new Request();
		$this->assertInstanceOf(Path::class, $request->path());
	}

	public function testDomain(): void
	{
		$request = new Request([
			'url' => 'https://getkirby.com/a/b'
		]);

		$this->assertSame('getkirby.com', $request->domain());
	}
}
