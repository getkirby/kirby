<?php

namespace Kirby\Http\Request;

use Kirby\TestCase;

class BodyTest extends TestCase
{
	public function testContents(): void
	{
		// default contents
		$body = new Body();
		$this->assertSame('', $body->contents());

		// array content
		$contents = ['a' => 'a'];
		$body     = new Body($contents);
		$this->assertSame($contents, $body->contents());

		// string
		$contents = 'foo';
		$body     = new Body($contents);
		$this->assertSame($contents, $body->contents());

		// $_POST
		$body = new Body();
		$_POST = 'foo';
		$this->assertSame('foo', $body->contents());
	}

	public function testData(): void
	{
		// default
		$data = [];
		$body = new Body();
		$this->assertSame($data, $body->data());

		// array data
		$data = ['a' => 'a'];
		$body = new Body($data);
		$this->assertSame($data, $body->data());

		// json data
		$data = ['a' => 'a'];
		$body = new Body(json_encode($data));
		$this->assertSame($data, $body->data());

		// http query data
		$data = ['a' => 'a'];
		$body = new Body(http_build_query($data));
		$this->assertSame($data, $body->data());

		// unparsable string
		$data = 'foo';
		$body = new Body($data);
		$this->assertSame([], $body->data());
	}

	public function testToArrayAndDebuginfo(): void
	{
		$data = ['a' => 'a'];
		$body = new Body($data);
		$this->assertSame($data, $body->toArray());
		$this->assertSame($data, $body->__debugInfo());
	}

	public function testToJson(): void
	{
		$data = ['a' => 'a'];
		$body = new Body($data);
		$this->assertSame(json_encode($data), $body->toJson());
	}

	public function testToString(): void
	{
		// default
		$body = new Body();
		$this->assertSame('', $body->toString());
		$this->assertSame('', $body->__toString());
		$this->assertEquals('', $body); // cannot use strict assertion (string conversion)

		// with data
		$string = 'foo=bar';
		$body   = new Body(['foo' => 'bar']);
		$this->assertSame($string, $body->toString());
		$this->assertSame($string, $body->__toString());
		$this->assertEquals($string, $body); // cannot use strict assertion (string conversion)
	}
}
