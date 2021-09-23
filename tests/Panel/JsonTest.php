<?php

namespace Kirby\Panel;

use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Kirby\Panel\Json
 */
class JsonTest extends TestCase
{
    /**
     * @covers ::response
     */
    public function testResponseThrowable()
    {
        $data     = new Exception();
        $response = Json::response($data);
        $this->assertSame(500, $response->code());
    }

    /**
     * @covers ::response
     */
    public function testResponseNoArray()
    {
        $data     = 'foo';
        $response = Json::response($data);
        $this->assertSame(500, $response->code());
    }
}
