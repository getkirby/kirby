<?php

namespace Kirby\Data;

use Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Json::class)]
class JsonTest extends TestCase
{
	public function testEncodeDecode()
	{
		$array = [
			'name'     => 'Homer',
			'children' => ['Lisa', 'Bart', 'Maggie']
		];

		$data = Json::encode($array);
		$this->assertSame('{"name":"Homer","children":["Lisa","Bart","Maggie"]}', $data);

		$result = Json::decode($data);
		$this->assertSame($array, $result);

		$this->assertSame([], Json::decode(null));
		$this->assertSame([], Json::decode(''));
		$this->assertSame([], Json::decode('{}'));
		$this->assertSame([], Json::decode('[]'));
		$this->assertSame(['this is' => 'an array'], Json::decode(['this is' => 'an array']));
	}

	public function testDecodeInvalid1()
	{
		// pass invalid object
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid JSON data; please pass a string');
		Json::decode(new \stdClass());
	}

	public function testDecodeInvalid2()
	{
		// pass invalid int
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid JSON data; please pass a string');
		Json::decode(1);
	}

	public function testDecodeCorrupted1()
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('JSON string is invalid');

		Json::decode('some gibberish');
	}

	public function testDecodeCorrupted2()
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('JSON string is invalid');

		Json::decode('true');
	}

	public function testEncodePretty()
	{
		$array = [
			'name'     => 'Homer',
			'children' => ['Lisa', 'Bart', 'Maggie']
		];

		$data = Json::encode($array, pretty: true);
		$this->assertSame('{
    "name": "Homer",
    "children": [
        "Lisa",
        "Bart",
        "Maggie"
    ]
}', $data);
	}

	public function testEncodeUnicode()
	{
		$string  = 'здравей';
		$this->assertSame('"' . $string . '"', Json::encode($string));
	}
}
