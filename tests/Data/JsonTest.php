<?php

namespace Kirby\Data;

use Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

#[CoversClass(Json::class)]
class JsonTest extends TestCase
{
	public function testEncodeDecode(): void
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

	public function testDecodeInvalid1(): void
	{
		// pass invalid object
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid JSON data; please pass a string');
		Json::decode(new stdClass());
	}

	public function testDecodeInvalid2(): void
	{
		// pass invalid int
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid JSON data; please pass a string');
		Json::decode(1);
	}

	public function testDecodeCorrupted1(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('JSON string is invalid');

		Json::decode('some gibberish');
	}

	public function testDecodeCorrupted2(): void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('JSON string is invalid');

		Json::decode('true');
	}

	public function testEncodePretty(): void
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

	public function testEncodeUnicode(): void
	{
		$string  = 'здравей';
		$this->assertSame('"' . $string . '"', Json::encode($string));
	}
}
