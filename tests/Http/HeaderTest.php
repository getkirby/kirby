<?php

namespace Kirby\Http;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Header::class)]
class HeaderTest extends TestCase
{
	// incomplete list compared to Header::$codes, mostly for
	// testing Header::success and other named methods
	protected array $statusHeaders = [
		200 => 'HTTP/1.1 200 OK',
		201 => 'HTTP/1.1 201 Created',
		202 => 'HTTP/1.1 202 Accepted',
		301 => 'HTTP/1.1 301 Moved Permanently',
		302 => 'HTTP/1.1 302 Found',
		400 => 'HTTP/1.1 400 Bad Request',
		403 => 'HTTP/1.1 403 Forbidden',
		404 => 'HTTP/1.1 404 Not Found',
		410 => 'HTTP/1.1 410 Gone',
		451 => 'HTTP/1.1 451 Unavailable For Legal Reasons',
		500 => 'HTTP/1.1 500 Internal Server Error',
		503 => 'HTTP/1.1 503 Service Unavailable'
	];

	public function testCreateSingle(): void
	{
		$this->assertSame('Key: Value', Header::create('Key', 'Value'));
	}

	public function testCreateMultiple(): void
	{
		$input = [
			'a' => 'value a',
			'b' => 'value b',
		];

		$expected = [
			'a: value a',
			'b: value b'
		];

		$this->assertSame(implode("\r\n", $expected), Header::create($input));
	}

	public function testNamedStatuses(): void
	{
		$h = $this->statusHeaders;
		$this->assertSame($h[200], Header::success(false), 'success status should be 200');
		$this->assertSame($h[201], Header::created(false), 'created status should be 201');
		$this->assertSame($h[202], Header::accepted(false), 'accepted status should be 202');
		$this->assertSame($h[400], Header::error(false), 'error status should be 400');
		$this->assertSame($h[403], Header::forbidden(false), 'forbidden status should be 403');
		$this->assertSame($h[404], Header::notfound(false), 'notfound status should be 404');
		$this->assertSame($h[404], Header::missing(false), 'missing status should be 404');
		$this->assertSame($h[410], Header::gone(false), 'gone status should be 410');
		$this->assertSame($h[500], Header::panic(false), 'panic status should be 500');
		$this->assertSame($h[503], Header::unavailable(false), 'unavailable status should be 503');
	}

	public function testStatusCodeOnly(): void
	{
		$h = $this->statusHeaders;

		// code only
		$this->assertSame(
			$h[200],
			Header::status(200, false),
			'Accepts integer status code'
		);
		$this->assertSame(
			$h[200],
			Header::status('200', false),
			'Accepts string status code'
		);
		$this->assertSame(
			$h[451],
			Header::status('451', false),
			'Can send HTTP 451 code (RFC 7725)'
		);
		$this->assertSame(
			$h[500],
			Header::status(null, false),
			'Null code results in 500'
		);
		$this->assertSame(
			Header::status(500, false),
			Header::status(999, false),
			'Unknown code results in 500'
		);

		// with reason in code parameter
		$this->assertSame(
			$h[200],
			Header::status('200 OK', false),
			'Can send "200 OK"'
		);
		$this->assertSame(
			'HTTP/1.1 999 Custom Header',
			Header::status('999 Custom Header', false),
			'Can send a well-formed custom status code and reason'
		);
		$this->assertSame(
			$h[500],
			Header::status("999 This is\nNOT OKAY", false),
			'Newlines inside of reason results in a 500 code'
		);
	}

	public function testRedirect(): void
	{
		$h = $this->statusHeaders;
		$this->assertSame(
			$h[301] . "\r\nLocation:/x",
			Header::redirect('/x', 301, false),
			'Can send a 301 redirect'
		);
		$this->assertSame(
			$h[302] . "\r\nLocation:/x",
			Header::redirect('/x', 302, false),
			'Can send a 302 redirect'
		);
	}

	public function testContentType(): void
	{
		$this->assertSame(
			'Content-type: video/webm',
			Header::contentType('webm', '', false),
			'Can send Content-type header with no encoding'
		);

		$this->assertSame(
			'Content-type: video/webm',
			Header::type('webm', '', false),
			'Can send Content-type header with no encoding'
		);

		$this->assertSame(
			'Content-type: application/json; charset=ISO-8859-1',
			Header::contentType('json', 'ISO-8859-1', false),
			'Can send Content-type header with custom charset'
		);

		$this->assertSame(
			'Content-type: application/json; charset=ISO-8859-1',
			Header::type('json', 'ISO-8859-1', false),
			'Can send Content-type header with custom charset'
		);
	}
}
