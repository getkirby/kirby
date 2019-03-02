<?php

namespace Kirby\Data;

use Kirby\Data\Json;
use Kirby\Data\Yaml;
use Kirby\Data\Php;
use Kirby\Data\Txt;

use PHPUnit\Framework\TestCase;

class CustomHandler extends Json
{
}

class DataTest extends TestCase
{
    public function testDefaultHandlers()
    {
        $this->assertInstanceOf(Yaml::class, Data::handler('yaml'));
        $this->assertInstanceOf(Json::class, Data::handler('json'));
        $this->assertInstanceOf(Txt::class, Data::handler('txt'));
    }

    public function testCustomHandler()
    {
        Data::$handlers['test'] = CustomHandler::class;
        $this->assertInstanceOf(CustomHandler::class, Data::handler('test'));
    }

    public function testMissingHandler()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Missing Handler for type: "foo"');

        Data::handler('foo');
    }

    public function testReadWrite()
    {
        $data = [
            'name'  => 'Homer Simpson',
            'email' => 'homer@simpson.com'
        ];

        $handlers = ['json', 'yml', 'txt'];

        foreach ($handlers as $handler) {
            $file = __DIR__ . '/tmp/data.' . $handler;

            // clean up first
            @unlink($file);

            Data::write($file, $data);
            $result = Data::read($file);

            $this->assertEquals($data, $result);
        }
    }
}
