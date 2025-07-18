<?php

namespace Kirby\Data;

use Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;

/**
 * @coversDefaultClass \Kirby\Data\Json
 */
class JsonTest extends TestCase
{
	/**
	 * @covers ::encode
	 * @covers ::decode
	 */
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

	/**
	 * @covers ::decode
	 */
	public function testDecodeInvalid1()
	{
		// pass invalid object
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid JSON data; please pass a string');
		Json::decode(new \stdClass());
	}

	/**
	 * @covers ::decode
	 */
	public function testDecodeInvalid2()
	{
		// pass invalid int
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid JSON data; please pass a string');
		Json::decode(1);
	}

	/**
	 * @covers ::decode
	 */
	public function testDecodeCorrupted1()
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('JSON string is invalid');

		Json::decode('some gibberish');
	}

	/**
	 * @covers ::decode
	 */
	public function testDecodeCorrupted2()
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('JSON string is invalid');

		Json::decode('true');
	}

	/**
	 * @covers ::encode
	 */
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

	/**
	 * @covers ::encode
	 */
	public function testEncodeUnicode()
	{
		$string  = 'здравей';
		$this->assertSame('"' . $string . '"', Json::encode($string));
	}
}
