<?php

namespace Kirby\Text\Markdown;

use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Data::class)]
class DataTest extends TestCase
{
	public function testGet(): void
	{
		$data = new Data();

		// unknown type
		$this->assertNull($data->get('LinkDefinition', 'id'));

		// known type, unknown id
		$value = ['url' => 'https://getkirby.com'];
		$data->set('LinkDefinition', 'id', $value);
		$this->assertNull($data->get('LinkDefinition', 'other'));

		// known type, known id
		$result = $data->get('LinkDefinition', 'id');
		$this->assertSame($value, $result);
	}

	public function testGetAllFromType(): void
	{
		$data = new Data();
		$data->set('Footnote', 'a', 1);
		$data->set('Footnote', 'b', 2);

		$this->assertSame(['a' => 1, 'b' => 2], $data->get('Footnote'));

		// an unknown type yields an empty array
		$this->assertSame([], $data->get('Foo'));
	}

	public function testReset(): void
	{
		$data = new Data();
		$data->set('LinkDefinition', 'id', 'value');
		$data->set('Footnote', 'a', 1);

		$data->reset();

		$this->assertNull($data->get('LinkDefinition', 'id'));
		$this->assertSame([], $data->get('Footnote'));
	}

	public function testSet(): void
	{
		$data = new Data();
		$data->set('Abbreviation', 'HTML', 'HyperText RawHtml Language');

		$this->assertSame('HyperText RawHtml Language', $data->get('Abbreviation', 'HTML'));
	}

	public function testSetOverwrite(): void
	{
		$data = new Data();
		$data->set('LinkDefinition', 'id', 'first');
		$data->set('LinkDefinition', 'id', 'second');

		// the latest definition under a type and id wins
		$this->assertSame('second', $data->get('LinkDefinition', 'id'));
	}

	public function testSetGroupsByType(): void
	{
		$data = new Data();
		$data->set('LinkDefinition', 'id', 'reference');
		$data->set('Footnote', 'id', 'footnote');

		// the same id under different types stays independent
		$this->assertSame('reference', $data->get('LinkDefinition', 'id'));
		$this->assertSame('footnote', $data->get('Footnote', 'id'));
	}
}
