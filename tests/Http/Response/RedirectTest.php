<?php

namespace Kirby\Http\Response;

use PHPUnit\Framework\TestCase;

class RedirectTest extends TestCase
{

    public function testConstruct()
    {
        $redirect = new Redirect;
        $this->assertEquals('/', $redirect->location());
        $this->assertEquals(301, $redirect->code());
    }

    public function testLocation()
    {
        $redirect = new Redirect;
        $this->assertEquals('/', $redirect->location());
        $this->assertEquals('https://getkirby.com', $redirect->location('https://getkirby.com'));
        $this->assertEquals('https://getkirby.com', $redirect->location());
    }

    public function testInternationalLocation()
    {
        $redirect = new Redirect('https://täst.de');
        $this->assertEquals('https://xn--tst-qla.de', $redirect->location());
    }

    /**
     * @runInSeparateProcess
     */
    public function testSend()
    {
        $redirect = new Redirect('/');

        ob_start();

        echo $redirect->send();

        $code = http_response_code();
        $body = ob_get_contents();

        ob_end_clean();

        $this->assertEquals($body, '');
        $this->assertEquals($code, 301);
    }

    public function testToArray()
    {
        // defaults
        $redirect = new Redirect('/');
        $expected = [
            'location' => '/',
            'code'     => 301
        ];

        $this->assertEquals($expected, $redirect->toArray());

        // custom
        $redirect = new Redirect('https://getkirby.com', 307);
        $expected = [
            'location' => 'https://getkirby.com',
            'code'     => 307
        ];

        $this->assertEquals($expected, $redirect->toArray());
    }
}
