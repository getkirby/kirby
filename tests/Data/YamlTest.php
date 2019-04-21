<?php

namespace Kirby\Data;

use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass Kirby\Data\Yaml
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
        $this->assertEquals(
            "name: Homer\nchildren:\n  - Lisa\n  - Bart\n  - Maggie\n",
            $data
        );

        $result = Yaml::decode($data);
        $this->assertEquals($array, $result);

        $this->assertEquals([], Yaml::decode(null));
        $this->assertEquals(['this is' => 'an array'], Yaml::decode(['this is' => 'an array']));
    }

    /**
     * @covers ::encode
     */
    public function testEncodeFloat()
    {
        $data = Yaml::encode([
            'number' => 3.2
        ]);

        $this->assertEquals('number: 3.2' . PHP_EOL, $data);
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

        $this->assertEquals('number: 3.2' . PHP_EOL, $data);

        setlocale(LC_ALL, $locale);
    }
}
