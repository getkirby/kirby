<?php

namespace Kirby\Data;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Data\Yaml
 */
class YamlTest extends TestCase
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

        $data = Yaml::encode($array);
        $this->assertSame(
            "name: Homer\nchildren:\n  - Lisa\n  - Bart\n  - Maggie\n",
            $data
        );

        $result = Yaml::decode($data);
        $this->assertSame($array, $result);

        $this->assertSame('', Yaml::encode([]));
        $this->assertSame([], Yaml::decode(''));

        $this->assertSame([], Yaml::decode(null));
        $this->assertSame(['this is' => 'an array'], Yaml::decode(['this is' => 'an array']));
    }

    /**
     * @covers ::decode
     */
    public function testDecodeInvalid1()
    {
        // pass invalid object
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid YAML data; please pass a string');
        Yaml::decode(new \stdClass());
    }

    /**
     * @covers ::decode
     */
    public function testDecodeInvalid2()
    {
        // pass invalid int
        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid YAML data; please pass a string');
        Yaml::decode(1);
    }

    /**
     * @covers ::encode
     */
    public function testEncodeFloat()
    {
        $data = Yaml::encode([
            'number' => 3.2
        ]);

        $this->assertSame('number: 3.2' . PHP_EOL, $data);
    }

    /**
     * @covers ::encode
     */
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
        $this->assertSame('test: ""' . PHP_EOL, $data);

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
        $this->assertSame('test: "string"' . PHP_EOL, $data);
    }
}
