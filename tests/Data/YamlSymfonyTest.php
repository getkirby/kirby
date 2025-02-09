<?php

namespace Kirby\Data;

use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Yaml::class)]
#[CoversClass(YamlSymfony::class)]
class YamlSymfonyTest extends TestCase
{
	public function setUp(): void
	{
		new App(['options' => ['yaml.handler' => 'symfony']]);
	}

	public function tearDown(): void
	{
		new App([]);
	}

	public function testEncodeDecode()
	{
		$array = [
			'name'     => 'Homer',
			'children' => ['Lisa', 'Bart', 'Maggie']
		];

		$data = Yaml::encode($array);
		$this->assertSame(
			"name: Homer\nchildren:\n  - Lisa\n  - Bart\n  - Maggie\n",
			$data
		);

		$result = Yaml::decode($data);
		$this->assertSame($array, $result);

		$this->assertSame('[]', Yaml::encode([]));
		$this->assertSame([], Yaml::decode(''));

		$this->assertSame([], Yaml::decode(null));
		$this->assertSame(['this is' => 'an array'], Yaml::decode(['this is' => 'an array']));
	}

	public function testDecodeInvalid1()
	{
		// pass invalid object
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid YAML data; please pass a string');
		Yaml::decode(new \stdClass());
	}

	public function testDecodeInvalid2()
	{
		// pass invalid int
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid YAML data; please pass a string');
		Yaml::decode(1);
	}

	public function testEncodeFloat()
	{
		$data = Yaml::encode([
			'number' => 3.2
		]);

		$this->assertSame('number: 3.2' . PHP_EOL, $data);
	}

	public function testEncodeFloatWithNonUSLocale()
	{
		$locale = setlocale(LC_ALL, 0);

		setlocale(LC_ALL, 'de_DE');

		$data = Yaml::encode([
			'number' => 3.2
		]);

		$this->assertSame('number: 3.2' . PHP_EOL, $data);

		setlocale(LC_ALL, $locale);
	}

	public function testEncodeNodeTypes()
	{
		$data = Yaml::encode(['test' => '']);
		$this->assertSame('test: \'\'' . PHP_EOL, $data);

		$data = Yaml::encode(['test' => null]);
		$this->assertSame('test: null' . PHP_EOL, $data);

		$data = Yaml::encode(['test' => 0]);
		$this->assertSame('test: 0' . PHP_EOL, $data);

		$data = Yaml::encode(['test' => true]);
		$this->assertSame('test: true' . PHP_EOL, $data);

		$data = Yaml::encode(['test' => false]);
		$this->assertSame('test: false' . PHP_EOL, $data);

		$data = Yaml::encode(['test' => 'string']);
		$this->assertSame('test: string' . PHP_EOL, $data);

		$data = Yaml::encode(['test' => '"string"']);
		$this->assertSame('test: \'"string"\'' . PHP_EOL, $data);
	}
}
