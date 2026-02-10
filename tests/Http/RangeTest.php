<?php

namespace Kirby\Http;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Range::class)]
class RangeTest extends TestCase
{
	public const FIXTURES = __DIR__ . '/fixtures';

	public function testParse(): void
	{
		// invalid size
		$result = Range::parse('bytes=0-1', 0);
		$this->assertFalse($result);

		// standard range
		$result = Range::parse('bytes=0-100', 1000);
		$this->assertSame([0, 100], $result);

		// open-ended range (to end of file)
		$result = Range::parse('bytes=500-', 1000);
		$this->assertSame([500, 999], $result);

		// open-ended range with whitespace
		$result = Range::parse('bytes=0- ', 1000);
		$this->assertSame([0, 999], $result);

		// Safari probe
		$result = Range::parse('bytes=0-1', 1000);
		$this->assertSame([0, 1], $result);

		// case-insensitive unit
		$result = Range::parse('Bytes=0-1', 1000);
		$this->assertSame([0, 1], $result);

		// single byte
		$result = Range::parse('bytes=0-0', 1000);
		$this->assertSame([0, 0], $result);

		// last byte
		$result = Range::parse('bytes=999-999', 1000);
		$this->assertSame([999, 999], $result);

		// not bytes unit
		$result = Range::parse('items=0-100', 1000);
		$this->assertFalse($result);

		// multiple ranges
		$result = Range::parse('bytes=0-100,200-300', 1000);
		$this->assertFalse($result);

		// suffix byte range (last N bytes)
		$result = Range::parse('bytes=-500', 1000);
		$this->assertSame([500, 999], $result);

		// suffix byte range with invalid length
		$result = Range::parse('bytes=-0', 1000);
		$this->assertFalse($result);

		// suffix byte range exceeding size (clamped)
		$result = Range::parse('bytes=-1500', 1000);
		$this->assertSame([0, 999], $result);

		// start beyond file size
		$result = Range::parse('bytes=2000-3000', 1000);
		$this->assertFalse($result);

		// end before start
		$result = Range::parse('bytes=100-50', 1000);
		$this->assertFalse($result);

		// start equals file size
		$result = Range::parse('bytes=1000-1000', 1000);
		$this->assertFalse($result);

		// end beyond file size (clamped)
		$result = Range::parse('bytes=0-1000', 1000);
		$this->assertSame([0, 999], $result);

		// negative start
		$result = Range::parse('bytes=-5-10', 1000);
		$this->assertFalse($result);

		// empty range
		$result = Range::parse('bytes=', 1000);
		$this->assertFalse($result);

		// malformed range
		$result = Range::parse('bytes=abc-def', 1000);
		$this->assertFalse($result);

		// non-numeric end
		$result = Range::parse('bytes=0-abc', 1000);
		$this->assertFalse($result);

		// missing equals
		$result = Range::parse('bytes 0-100', 1000);
		$this->assertFalse($result);

		// malformed with text
		$result = Range::parse('bytes=start-end', 1000);
		$this->assertFalse($result);
	}

	public function testResponseWithValidRange(): void
	{
		$file = static::FIXTURES . '/download.json';
		$size = filesize($file);

		$response = Range::response($file, 'bytes=0-5');

		$this->assertSame(206, $response->code());
		$this->assertSame('bytes', $response->header('Accept-Ranges'));
		$this->assertSame('bytes 0-5/' . $size, $response->header('Content-Range'));
		$this->assertSame(6, (int)$response->header('Content-Length'));
		$this->assertSame('{"foo"', $response->body());
	}

	public function testResponseWithOpenEndedRange(): void
	{
		$file = static::FIXTURES . '/download.json';
		$size = filesize($file);

		$response = Range::response($file, 'bytes=8-');

		$this->assertSame(206, $response->code());
		$this->assertSame('bytes 8-' . ($size - 1) . '/' . $size, $response->header('Content-Range'));
		$this->assertSame('"bar"}', $response->body());
	}

	public function testResponseWithSafariProbe(): void
	{
		$file = static::FIXTURES . '/download.json';
		$size = filesize($file);

		// Safari sends bytes=0-1 as an initial probe
		$response = Range::response($file, 'bytes=0-1');

		$this->assertSame(206, $response->code());
		$this->assertSame('bytes 0-1/' . $size, $response->header('Content-Range'));
		$this->assertSame(2, (int)$response->header('Content-Length'));
		$this->assertSame('{"', $response->body());
	}

	public function testResponseWithInvalidRange(): void
	{
		$file = static::FIXTURES . '/download.json';
		$size = filesize($file);

		// beyond file size
		$response = Range::response($file, 'bytes=1000-2000');

		$this->assertSame(416, $response->code());
		$this->assertSame('bytes */' . $size, $response->header('Content-Range'));
		$this->assertSame('Requested Range Not Satisfiable', $response->body());
	}

	public function testResponseWithInvalidMimeType(): void
	{
		$file     = static::FIXTURES . '/download.xyz';
		$response = Range::response($file, 'bytes=0-3');

		$this->assertSame(206, $response->code());
		$this->assertSame('text/plain', $response->type());
		$this->assertSame('nosniff', $response->header('X-Content-Type-Options'));
		$this->assertSame('test', $response->body());
	}
}
