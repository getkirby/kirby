<?php

namespace Kirby\Http;

use Exception;
use Kirby\Exception\LogicException;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

#[CoversClass(Response::class)]
class ResponseTest extends TestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures';

	public function tearDown(): void
	{
		HeadersSent::$value = false;
	}

	public function testBody(): void
	{
		$response = new Response();
		$this->assertSame('', $response->body());

		$response = new Response('test');
		$this->assertSame('test', $response->body());

		$response = new Response([
			'body' => 'test'
		]);

		$this->assertSame('test', $response->body());
	}

	public function testDownload(): void
	{
		$response = Response::download(__FILE__);

		$this->assertSame($body = file_get_contents(__FILE__), $response->body());
		$this->assertSame(200, $response->code());
		$this->assertSame([
			'Pragma'                    => 'public',
			'Cache-Control'             => 'no-cache, no-store, must-revalidate',
			'Last-Modified'             => gmdate('D, d M Y H:i:s', filemtime(__FILE__)) . ' GMT',
			'Content-Disposition'       => 'attachment; filename="' . basename(__FILE__) . '"',
			'Content-Transfer-Encoding' => 'binary',
			'Content-Length'            => strlen($body),
			'Connection'                => 'close'
		], $response->headers());

		$response = Response::download(__FILE__, 'test.php');

		$this->assertSame($body, $response->body());
		$this->assertSame(200, $response->code());
		$this->assertSame([
			'Pragma'                    => 'public',
			'Cache-Control'             => 'no-cache, no-store, must-revalidate',
			'Last-Modified'             => gmdate('D, d M Y H:i:s', filemtime(__FILE__)) . ' GMT',
			'Content-Disposition'       => 'attachment; filename="test.php"',
			'Content-Transfer-Encoding' => 'binary',
			'Content-Length'            => strlen($body),
			'Connection'                => 'close'
		], $response->headers());

		$response = Response::download(__FILE__, 'test.php', [
			'code'    => '201',
			'headers' => [
				'Pragma' => 'no-cache',
				'X-Test' => 'Test'
			]
		]);

		$this->assertSame($body, $response->body());
		$this->assertSame(201, $response->code());
		$this->assertSame([
			'Pragma'                    => 'no-cache',
			'Cache-Control'             => 'no-cache, no-store, must-revalidate',
			'Last-Modified'             => gmdate('D, d M Y H:i:s', filemtime(__FILE__)) . ' GMT',
			'Content-Disposition'       => 'attachment; filename="test.php"',
			'Content-Transfer-Encoding' => 'binary',
			'Content-Length'            => strlen($body),
			'Connection'                => 'close',
			'X-Test'                    => 'Test'
		], $response->headers());
	}

	public function testDownloadWithMissingFile(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('The file could not be found');

		Response::download('does/not/exist.txt');
	}

	public function testGuardAgainstOutput(): void
	{
		$result = Response::guardAgainstOutput(
			fn ($arg1, $arg2) => $arg1 . '-' . $arg2,
			'12',
			'34'
		);

		$this->assertSame('12-34', $result);
	}

	public function testGuardAgainstOutputWithSubsequentOutput(): void
	{
		HeadersSent::$value = true;

		$result = Response::guardAgainstOutput(
			fn ($arg1, $arg2) => $arg1 . '-' . $arg2,
			'12',
			'34'
		);

		$this->assertSame('12-34', $result);
	}

	public function testGuardAgainstOutputWithFirstOutput(): void
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Disallowed output from file file.php:123, possible accidental whitespace?');

		Response::guardAgainstOutput(function () {
			HeadersSent::$value = true;
		});
	}

	public function testHeaders(): void
	{
		$response = new Response();
		$this->assertSame([], $response->headers());

		$response = new Response([
			'headers' => [
				'test' => 'test'
			]
		]);

		$this->assertSame(['test' => 'test'], $response->headers());
	}

	public function testHeader(): void
	{
		$response = new Response();
		$this->assertNull($response->header('test'));

		$response = new Response([
			'headers' => [
				'test' => 'test'
			]
		]);

		$this->assertSame('test', $response->header('test'));
	}

	public function testJson(): void
	{
		$response = Response::json();

		$this->assertSame('application/json', $response->type());
		$this->assertSame(200, $response->code());
		$this->assertSame('', $response->body());
	}

	public function testJsonWithArray(): void
	{
		$data     = ['foo' => 'bar'];
		$expected = json_encode($data);
		$response = Response::json($data);

		$this->assertSame($expected, $response->body());
	}

	public function testJsonWithPrettyArray(): void
	{
		$data     = ['foo' => 'bar'];
		$expected = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
		$response = Response::json($data, 200, true);

		$this->assertSame($expected, $response->body());
	}

	public function testFile(): void
	{
		$file = static::FIXTURES . '/download.json';

		$response = Response::file($file);

		$this->assertSame('application/json', $response->type());
		$this->assertSame(200, $response->code());
		$this->assertSame('{"foo": "bar"}', $response->body());

		$response = Response::file($file, [
			'code'    => '201',
			'headers' => [
				'Pragma' => 'no-cache'
			]
		]);

		$this->assertSame('application/json', $response->type());
		$this->assertSame(201, $response->code());
		$this->assertSame('{"foo": "bar"}', $response->body());
		$this->assertSame([
			'Pragma' => 'no-cache'
		], $response->headers());
	}

	public function testFileInvalid(): void
	{
		$file = static::FIXTURES . '/download.xyz';

		$response = Response::file($file);

		$this->assertSame('text/plain', $response->type());
		$this->assertSame(200, $response->code());
		$this->assertSame('test', $response->body());
		$this->assertSame([
			'X-Content-Type-Options' => 'nosniff',
		], $response->headers());

		$response = Response::file($file, [
			'code'    => '201',
			'headers' => [
				'Pragma' => 'no-cache'
			]
		]);

		$this->assertSame('text/plain', $response->type());
		$this->assertSame(201, $response->code());
		$this->assertSame('test', $response->body());
		$this->assertSame([
			'Pragma' => 'no-cache',
			'X-Content-Type-Options' => 'nosniff',
		], $response->headers());
	}

	public function testType(): void
	{
		$response = new Response();
		$this->assertSame('text/html', $response->type());

		$response = new Response('', 'image/jpeg');
		$this->assertSame('image/jpeg', $response->type());

		$response = new Response([
			'type' => 'image/jpeg'
		]);

		$this->assertSame('image/jpeg', $response->type());
	}

	public function testCharset(): void
	{
		$response = new Response();
		$this->assertSame('UTF-8', $response->charset());

		$response = new Response('', 'text/html', 200, [], 'test');
		$this->assertSame('test', $response->charset());

		$response = new Response([
			'charset' => 'test'
		]);

		$this->assertSame('test', $response->charset());
	}

	public function testCode(): void
	{
		$response = new Response();
		$this->assertSame(200, $response->code());

		$response = new Response('', 'text/html', 404);
		$this->assertSame(404, $response->code());

		$response = new Response([
			'code' => 404
		]);

		$this->assertSame(404, $response->code());
	}

	public function testRedirect(): void
	{
		$response = Response::redirect();
		$this->assertSame('', $response->body());
		$this->assertSame(302, $response->code());
		$this->assertEquals(['Location' => '/'], $response->headers()); // cannot use strict assertion (Uri object)
	}

	public function testRedirectWithLocation(): void
	{
		$response = Response::redirect('https://getkirby.com');
		$this->assertSame('', $response->body());
		$this->assertSame(302, $response->code());
		$this->assertEquals(['Location' => 'https://getkirby.com'], $response->headers()); // cannot use strict assertion (Uri object)
	}

	public function testRedirectWithInternationalLocation(): void
	{
		$response = Response::redirect('https://täst.de');
		$this->assertSame('', $response->body());
		$this->assertSame(302, $response->code());
		$this->assertEquals(['Location' => 'https://xn--tst-qla.de'], $response->headers()); // cannot use strict assertion (Uri object)
	}

	public function testRedirectWithResponseCode(): void
	{
		$response = Response::redirect('/', 301);
		$this->assertSame('', $response->body());
		$this->assertSame(301, $response->code());
		$this->assertEquals(['Location' => '/'], $response->headers()); // cannot use strict assertion (Uri object)
	}

	public function testRefresh(): void
	{
		$response = Response::refresh();
		$this->assertSame('', $response->body());
		$this->assertSame(302, $response->code());
		$this->assertEquals(['Refresh' => '0; url=/'], $response->headers());
	}

	public function testRefreshWithLocation(): void
	{
		$response = Response::refresh('https://getkirby.com');
		$this->assertSame('', $response->body());
		$this->assertSame(302, $response->code());
		$this->assertEquals(['Refresh' => '0; url=https://getkirby.com'], $response->headers());
	}

	public function testRefreshWithTime(): void
	{
		$response = Response::refresh('https://getkirby.com', 302, 5);
		$this->assertSame('', $response->body());
		$this->assertSame(302, $response->code());
		$this->assertEquals(['Refresh' => '5; url=https://getkirby.com'], $response->headers());
	}

	public function testSetHeaderFallbacks(): void
	{
		$response = new Response([
			'headers' => ['a' => 'b']
		]);
		$response->setHeaderFallbacks(['a' => 'z', 'c' => 'd']);
		$this->assertEquals(['a' => 'b', 'c' => 'd'], $response->headers());
	}

	#[RunInSeparateProcess]
	#[PreserveGlobalState(false)]
	public function testSend(): void
	{
		$response = new Response([
			'body'    => 'test',
			'headers' => [
				'foo' => 'bar'
			]
		]);

		ob_start();

		echo $response->send();

		$code = http_response_code();
		$body = ob_get_contents();

		ob_end_clean();

		$this->assertSame($body, 'test');
		$this->assertSame($code, 200);
	}

	#[RunInSeparateProcess]
	#[PreserveGlobalState(false)]
	public function testToString(): void
	{
		$response = new Response([
			'body'    => 'test',
			'headers' => [
				'foo' => 'bar'
			]
		]);

		ob_start();

		echo $response;

		$code = http_response_code();
		$body = ob_get_contents();

		ob_end_clean();

		$this->assertSame($body, 'test');
		$this->assertSame($code, 200);
	}

	public function testToArray(): void
	{
		// default setup
		$response = new Response();
		$expected = [
			'type'    => 'text/html',
			'charset' => 'UTF-8',
			'code'    => 200,
			'headers' => [],
			'body'    => '',
		];

		$this->assertSame($expected, $response->toArray());
	}
}
