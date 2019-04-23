<?php

namespace Kirby\Data;

use PHPUnit\Framework\TestCase;
use Kirby\Toolkit\F;

require_once(__DIR__ . '/mocks.php');

/**
 * @coversDefaultClass Kirby\Data\Data
 */
class DataTest extends TestCase
{
    /**
     * @covers ::handler
     */
    public function testDefaultHandlers()
    {
        $this->assertInstanceOf(Json::class, Data::handler('json'));
        $this->assertInstanceOf(PHP::class, Data::handler('php'));
        $this->assertInstanceOf(Txt::class, Data::handler('txt'));
        $this->assertInstanceOf(Yaml::class, Data::handler('yaml'));

        // aliases
        $this->assertInstanceOf(Txt::class, Data::handler('md'));
        $this->assertInstanceOf(Txt::class, Data::handler('mdown'));
        $this->assertInstanceOf(Yaml::class, Data::handler('yml'));

        // different case
        $this->assertInstanceOf(Json::class, Data::handler('JSON'));
        $this->assertInstanceOf(Json::class, Data::handler('JsOn'));
    }

    /**
     * @covers ::handler
     */
    public function testCustomHandler()
    {
        Data::$handlers['test'] = CustomHandler::class;
        $this->assertInstanceOf(CustomHandler::class, Data::handler('test'));
    }

    /**
     * @covers ::handler
     */
    public function testCustomAlias()
    {
        Data::$aliases['plaintext'] = 'txt';
        $this->assertInstanceOf(Txt::class, Data::handler('plaintext'));
    }

    /**
     * @covers ::handler
     */
    public function testMissingHandler()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Missing handler for type: "foo"');

        Data::handler('foo');
    }

    /**
     * @covers ::encode
     * @covers ::decode
     */
    public function testEncodeDecode()
    {
        $data = [
            'name'  => 'Homer Simpson',
            'email' => 'homer@simpson.com'
        ];

        $encodedJson = Data::encode($data, 'json');
        $this->assertEquals(Json::encode($data), $encodedJson);

        $encodedYaml = Data::encode($data, 'yaml');
        $this->assertEquals(Yaml::encode($data), $encodedYaml);

        $this->assertEquals($data, Data::decode($encodedJson, 'json'));
        $this->assertEquals($data, Data::decode($encodedYaml, 'yaml'));
    }

    /**
     * @covers ::encode
     * @covers ::handler
     */
    public function testEncodeInvalid()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Missing handler for type: "foo"');

        $data = [
            'name'  => 'Homer Simpson',
            'email' => 'homer@simpson.com'
        ];

        Data::encode($data, 'foo');
    }

    /**
     * @covers ::read
     * @covers ::write
     */
    public function testReadWrite()
    {
        $data = [
            'name'  => 'Homer Simpson',
            'email' => 'homer@simpson.com'
        ];

        $file = __DIR__ . '/tmp/data.json';

        // clean up first
        @unlink($file);

        // automatic type detection
        Data::write($file, $data);
        $this->assertFileExists($file);
        $this->assertEquals(Json::encode($data), F::read($file));

        $result = Data::read($file);
        $this->assertEquals($data, $result);

        // custom type
        Data::write($file, $data, 'yml');
        $this->assertFileExists($file);
        $this->assertEquals(Yaml::encode($data), F::read($file));

        $result = Data::read($file, 'yml');
        $this->assertEquals($data, $result);
    }

    /**
     * @covers ::read
     * @covers ::handler
     */
    public function testReadInvalid()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Missing handler for type: "foo"');

        Data::read(__DIR__ . '/tmp/data.foo');
    }

    /**
     * @covers ::write
     * @covers ::handler
     */
    public function testWriteInvalid()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Missing handler for type: "foo"');

        $data = [
            'name'  => 'Homer Simpson',
            'email' => 'homer@simpson.com'
        ];

        Data::write(__DIR__ . '/tmp/data.foo', $data);
    }
}
