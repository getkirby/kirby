<?php

namespace Kirby\Data;

use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

#[CoversClass(Txt::class)]
class TxtTest extends TestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures';

	public function testEncodeDecode(): void
	{
		$array = [
			'title' => 'Title',
			'text'  => 'Text'
		];

		$data = Txt::encode($array);
		$this->assertSame(
			"Title: Title\n\n----\n\nText: Text",
			$data
		);

		$result = Txt::decode($data);
		$this->assertSame($array, $result);

		$this->assertSame('', Txt::encode([]));
		$this->assertSame([], Txt::decode(''));

		$this->assertSame([], Txt::decode(null));
		$this->assertSame(['this is' => 'an array'], Txt::decode(['this is' => 'an array']));
	}

	public function testEncodeDecodeMixedCase(): void
	{
		$array = [
			'title' => 'Title',
			'text'  => 'Text',
			'tItLe' => 'Another title',
			'TEXT'  => 'UPPERTEXT'
		];

		$data = Txt::encode($array);
		$this->assertSame(
			"Title: Another title\n\n----\n\nText: UPPERTEXT",
			$data
		);

		$result = Txt::decode($data);
		$this->assertSame([
			'title' => 'Another title',
			'text'  => 'UPPERTEXT'
		], $result);
	}

	public function testEncodeMissingValues(): void
	{
		$array = [
			'title' => 'Title',
			'text'  => null,
			''      => 'text',
			'field' => 'content'
		];

		$data = Txt::encode($array);
		$this->assertSame(
			"Title: Title\n\n----\n\nField: content",
			$data
		);
	}

	public function testEncodeMultiline(): void
	{
		$array = [
			'title' => 'Title',
			'text'  => "Text\nText"
		];

		$data = Txt::encode($array);
		$this->assertSame(
			"Title: Title\n\n----\n\nText:\n\nText\nText",
			$data
		);
	}

	public function testEncodeDecodeDivider(): void
	{
		$array = [
			'title' => 'Title',
			'text'  => "----\n----\nText\n\n----Field:\nValue\n----  \n----"
		];

		$data = Txt::encode($array);
		$this->assertSame(
			"Title: Title\n\n----\n\nText:\n\n\\----\n\\----\n" .
			"Text\n\n\\----Field:\nValue\n\\----  \n\\----",
			$data
		);

		$this->assertSame($array, Txt::decode($data));
	}

	public function testEncodeArray(): void
	{
		$array = [
			'title' => 'Title',
			'text'  => ['a', 'b', 'c'],
			'text2' => [],
			'text3' => ['a'],
		];

		$data = Txt::encode($array);
		$this->assertSame(file_get_contents(static::FIXTURES . '/test.txt'), $data);
	}

	public function testEncodeBool(): void
	{
		$data = Txt::encode([
			'bool' => true
		]);

		$this->assertSame('Bool: true', $data);

		$data = Txt::encode([
			'bool' => false
		]);

		$this->assertSame('Bool: false', $data);
	}

	public function testEncodeFloat(): void
	{
		$data = Txt::encode([
			'number' => (float)3.2
		]);

		$this->assertSame('Number: 3.2', $data);
	}

	public function testEncodeFloatWithLocaleSetting(): void
	{
		$currentLocale = setlocale(LC_ALL, 0);
		setlocale(LC_ALL, 'de_DE');

		$data = Txt::encode([
			'number' => (float)3.2
		]);

		$this->assertSame('Number: 3.2', $data);

		setlocale(LC_ALL, $currentLocale);
	}

	public function testDecodeFile(): void
	{
		$array = [
			'title_with_spaces' => 'Title',
			'text_with_dashes'  => 'Text'
		];

		$data = Txt::decode(file_get_contents(static::FIXTURES . '/decode.txt'));
		$this->assertSame($array, $data);
	}

	public function testDecodeBom1(): void
	{
		$string = "\xEF\xBB\xBFTitle: title field with BOM \xEF\xBB\xBF\n----\nText: text field";
		$array  = [
			'title' => "title field with BOM \xEF\xBB\xBF",
			'text'  => 'text field'
		];

		$this->assertSame($array, Txt::decode($string));
	}

	public function testDecodeBom2(): void
	{
		$string = "\xEF\xBB\xBFTitle: title field with BOM\n--\xEF\xBB\xBF--\nand more text\n----\nText: text field";
		$array  = [
			'title' => "title field with BOM\n--\xEF\xBB\xBF--\nand more text",
			'text'  => 'text field'
		];

		$this->assertSame($array, Txt::decode($string));
	}

	public function testDecodeInvalid1(): void
	{
		// pass invalid object
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid TXT data; please pass a string');
		Txt::decode(new stdClass());
	}

	public function testDecodeInvalid2(): void
	{
		// pass invalid int
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid TXT data; please pass a string');
		Txt::decode(1);
	}
}
