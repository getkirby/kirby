<?php

namespace Kirby\Api;

use stdClass;
use PHPUnit\Framework\TestCase;

class MockModel
{
}

class ExtendedModel extends stdClass
{
}

class ApiTest extends TestCase
{
    public function testModelResolver()
    {
        $api = new Api([
            'models' => [
                'MockModel' => [
                    'type' => MockModel::class,
                ],
                'stdClass' => [
                    'type' => stdClass::class,
                ]
            ]
        ]);

        // resolve class with namespace
        $result = $api->resolve(new MockModel);
        $this->assertInstanceOf(Model::class, $result);

        // resolve class without namespace
        $result = $api->resolve(new stdClass);
        $this->assertInstanceOf(Model::class, $result);

        // resolve class extension
        $result = $api->resolve(new ExtendedModel);
        $this->assertInstanceOf(Model::class, $result);
    }

    public function testModelResolverWithMissingModel()
    {
        $this->expectException('Kirby\Exception\NotFoundException');

        $api = new Api([]);
        $api->resolve(new MockModel);
    }
}
