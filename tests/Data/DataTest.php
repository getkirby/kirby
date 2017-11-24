<?php

namespace Kirby\Data;

use Kirby\Data\Handler\Json;
use Kirby\Data\Handler\Yaml;
use Kirby\Data\Handler\Php;
use Kirby\Data\Handler\Txt;

use PHPUnit\Framework\TestCase;

class CustomHandler extends Json {}

class DataTest extends TestCase
{

    public function testDefaultHandlers()
    {
        $this->assertInstanceOf(Yaml::class, Data::handler('yaml'));
        $this->assertInstanceOf(Json::class, Data::handler('json'));
        $this->assertInstanceOf(Php::class, Data::handler('php'));
        $this->assertInstanceOf(Txt::class, Data::handler('txt'));
    }

    public function testCustomHandler()
    {
        Data::handler('test', CustomHandler::class);
        $this->assertInstanceOf(CustomHandler::class, Data::handler('test'));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Missing Handler for type: "foo"
     */
    public function testMissingHandler()
    {
        Data::handler('foo');
    }

    /**
     * @expectedException  Exception
     * @expectedExceptionMessage stdClass must extend Kirby\Data\Handler
     */
    public function testInvalidHandler()
    {
        Data::handler('test', 'stdClass');
    }

    public function testReadWrite()
    {
        $data = [
            'name'  => 'Homer Simpson',
            'email' => 'homer@simpson.com'
        ];

        $handlers = ['json', 'yml', 'txt', 'php'];

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
