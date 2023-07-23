<?php

namespace Kirby\Http;

use Kirby\Exception\LogicException;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/mocks.php';

/**
 * @coversDefaultClass \Kirby\Http\Response
 */
class ResponseTest extends TestCase
{
	public function tearDown(): void
	{
		HeadersSent::$value = false;
	}

	public function testBody()
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

	public function testDownload()
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

	public function testDownloadWithMissingFile()
	{
		$this->expectException('Exception');
		$this->expectExceptionMessage('The file could not be found');

		Response::download('does/not/exist.txt');
	}

	/**
	 * @covers ::guardAgainstOutput
	 */
	public function testGuardAgainstOutput()
	{
		$result = Response::guardAgainstOutput(function ($arg1, $arg2) {
			return $arg1 . '-' . $arg2;
		}, '12', '34');

		$this->assertSame('12-34', $result);
	}

	/**
	 * @covers ::guardAgainstOutput
	 */
	public function testGuardAgainstOutputWithSubsequentOutput()
	{
		HeadersSent::$value = true;

		$result = Response::guardAgainstOutput(function ($arg1, $arg2) {
			return $arg1 . '-' . $arg2;
		}, '12', '34');

		$this->assertSame('12-34', $result);
	}

	/**
	 * @covers ::guardAgainstOutput
	 */
	public function testGuardAgainstOutputWithFirstOutput()
	{
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Disallowed output from file file.php:123, possible accidental whitespace?');

		Response::guardAgainstOutput(function () {
			HeadersSent::$value = true;
		});
	}

	public function testHeaders()
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

	public function testHeader()
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

	public function testJson()
	{
		$response = Response::json();

		$this->assertSame('application/json', $response->type());
		$this->assertSame(200, $response->code());
		$this->assertSame('', $response->body());
	}

	public function testJsonWithArray()
	{
		$data     = ['foo' => 'bar'];
		$expected = json_encode($data);
		$response = Response::json($data);

		$this->assertSame($expected, $response->body());
	}

	public function testJsonWithPrettyArray()
	{
		$data     = ['foo' => 'bar'];
		$expected = json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
		$response = Response::json($data, 200, true);

		$this->assertSame($expected, $response->body());
	}

	public function testFile()
	{
		$file = __DIR__ . '/fixtures/download.json';

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

	public function testFileInvalid()
	{
		$file = __DIR__ . '/fixtures/download.xyz';

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

	public function testType()
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

	public function testCharset()
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

	public function testCode()
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

	public function testRedirect()
	{
		$response = Response::redirect();
		$this->assertSame('', $response->body());
		$this->assertSame(302, $response->code());
		$this->assertEquals(['Location' => '/'], $response->headers()); // cannot use strict assertion (Uri object)
	}

	public function testRedirectWithLocation()
	{
		$response = Response::redirect('https://getkirby.com');
		$this->assertSame('', $response->body());
		$this->assertSame(302, $response->code());
		$this->assertEquals(['Location' => 'https://getkirby.com'], $response->headers()); // cannot use strict assertion (Uri object)
	}

	public function testRedirectWithInternationalLocation()
	{
		$response = Response::redirect('https://täst.de');
		$this->assertSame('', $response->body());
		$this->assertSame(302, $response->code());
		$this->assertEquals(['Location' => 'https://xn--tst-qla.de'], $response->headers()); // cannot use strict assertion (Uri object)
	}

	public function testRedirectWithResponseCode()
	{
		$response = Response::redirect('/', 301);
		$this->assertSame('', $response->body());
		$this->assertSame(301, $response->code());
		$this->assertEquals(['Location' => '/'], $response->headers()); // cannot use strict assertion (Uri object)
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testSend()
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

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testToString()
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

	public function testToArray()
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
