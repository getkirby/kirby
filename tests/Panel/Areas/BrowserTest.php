<?php

namespace Kirby\Panel\Areas;

class BrowserTest extends AreaTestCase
{
    public function testBrowser(): void
    {
        $response = $this->response('browser');

        $this->assertInstanceOf('Kirby\Http\Response', $response);
        $this->assertSame(200, $response->code());
        $this->assertSame('text/html', $response->type());
        $this->assertStringContainsString('We are really sorry, but your browser does not support', $response->body());
    }
}
