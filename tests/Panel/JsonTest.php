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
    public function testResponseEmptyArray()
    {
        $response = Json::response([]);
        $this->assertSame(404, $response->code());
    }

    /**
     * @covers ::response
     */
    public function testResponseRedirect()
    {
        $redirect = new Redirect('https://getkirby.com');
        $response = Json::response($redirect);

        $this->assertSame(302, $response->code());

        $body = json_decode($response->body(), true);

        $this->assertSame('https://getkirby.com', $body['$response']['redirect']);
        $this->assertSame(302, $body['$response']['code']);
    }

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
