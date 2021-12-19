<?php

namespace Kirby\Data;

use Kirby\Cms\App;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Data\Yaml
 */
class YamlTest extends TestCase
{
    protected $app;

    public function setUp(): void
    {
        $this->app = new App([
            'options' => [
                'yaml' => 'symfony'
            ],
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);
    }

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

        $this->assertSame('[]', Yaml::encode([]));
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

    /**
     * @covers ::encode
     * @covers ::decode
     */
    public function testEncodeDecodeSpaces()
    {
        $array = [
            'builder' => [
                'blocks' => [
                    ['content' => 'This is a
     test to see,
         if indentation can be preservered
                     '],
                    ['content' => '
                     or not
                     ']
                ]
            ]
        ];

        $data   = Yaml::encode($array);
        $result = Yaml::decode($data);
        $this->assertSame($array, $result);
    }
}
