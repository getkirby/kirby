<?php

namespace Kirby\Http;

use Kirby\Cms\App;
use PHPUnit\Framework\TestCase;

class RemoteTest extends TestCase
{
    protected $defaults;

    public function setUp(): void
    {
        $this->defaults = Remote::$defaults;

        Remote::$defaults = array_merge($this->defaults, [
            'test' => true,
            'key'  => 'value'
        ]);
    }

    public function tearDown(): void
    {
        Remote::$defaults = $this->defaults;
    }

    public function testOptionsHeaders()
    {
        $request = Remote::get('https://getkirby.com', [
            'headers' => [
                'Accept' => 'application/json',
                'Accept-Charset: utf8'
            ]
        ]);
        $this->assertSame([
            'Accept: application/json',
            'Accept-Charset: utf8'
        ], $request->curlopt[CURLOPT_HTTPHEADER]);
    }

    public function testOptionsBasicAuth()
    {
        $request = Remote::get('https://getkirby.com', [
            'basicAuth' => 'user:pw'
        ]);
        $this->assertSame('user:pw', $request->curlopt[CURLOPT_USERPWD]);
    }

    public function testOptionsCa()
    {
        // default: internal CA
        $request = Remote::get('https://getkirby.com');
        $this->assertTrue($request->curlopt[CURLOPT_SSL_VERIFYPEER]);
        $this->assertSame(dirname(__DIR__, 2) . '/cacert.pem', $request->curlopt[CURLOPT_CAINFO]);
        $this->assertArrayNotHasKey(CURLOPT_CAPATH, $request->curlopt);

        // explicit internal CA
        $request = Remote::get('https://getkirby.com', [
            'ca' => Remote::CA_INTERNAL
        ]);
        $this->assertTrue($request->curlopt[CURLOPT_SSL_VERIFYPEER]);
        $this->assertSame(dirname(__DIR__, 2) . '/cacert.pem', $request->curlopt[CURLOPT_CAINFO]);
        $this->assertArrayNotHasKey(CURLOPT_CAPATH, $request->curlopt);

        // CA file
        $request = Remote::get('https://getkirby.com', [
            'ca' => __FILE__
        ]);
        $this->assertTrue($request->curlopt[CURLOPT_SSL_VERIFYPEER]);
        $this->assertSame(__FILE__, $request->curlopt[CURLOPT_CAINFO]);
        $this->assertArrayNotHasKey(CURLOPT_CAPATH, $request->curlopt);

        // CA directory
        $request = Remote::get('https://getkirby.com', [
            'ca' => __DIR__
        ]);
        $this->assertTrue($request->curlopt[CURLOPT_SSL_VERIFYPEER]);
        $this->assertArrayNotHasKey(CURLOPT_CAINFO, $request->curlopt);
        $this->assertSame(__DIR__, $request->curlopt[CURLOPT_CAPATH]);

        // system CA
        $request = Remote::get('https://getkirby.com', [
            'ca' => Remote::CA_SYSTEM
        ]);
        $this->assertTrue($request->curlopt[CURLOPT_SSL_VERIFYPEER]);
        $this->assertArrayNotHasKey(CURLOPT_CAINFO, $request->curlopt);
        $this->assertArrayNotHasKey(CURLOPT_CAPATH, $request->curlopt);

        // disabled
        $request = Remote::get('https://getkirby.com', [
            'ca' => false
        ]);
        $this->assertFalse($request->curlopt[CURLOPT_SSL_VERIFYPEER]);
        $this->assertArrayNotHasKey(CURLOPT_CAINFO, $request->curlopt);
        $this->assertArrayNotHasKey(CURLOPT_CAPATH, $request->curlopt);

        $this->expectException('Kirby\Exception\InvalidArgumentException');
        $this->expectExceptionMessage('Invalid "ca" option for the Remote class');
        $request = Remote::get('https://getkirby.com', [
            'ca' => 'does-not-exist'
        ]);
    }

    public function testOptionsFromApp()
    {
        new App([
            'options' => [
                'remote.key' => 'different-value',
                'remote.body' => false
            ]
        ]);

        $request = Remote::get('https://getkirby.com');

        $options = $request->options();
        $this->assertSame('different-value', $options['key']);
        $this->assertFalse($options['body']);
    }

    public function testContent()
    {
        $request = Remote::put('https://getkirby.com');
        $this->assertSame(null, $request->content());
    }

    public function testCode()
    {
        $request = Remote::put('https://getkirby.com');
        $this->assertSame(null, $request->code());
    }

    public function testDelete()
    {
        $request = Remote::delete('https://getkirby.com');
        $this->assertSame('DELETE', $request->method());
    }

    public function testGet()
    {
        $request = Remote::get('https://getkirby.com');
        $this->assertSame('GET', $request->method());
    }

    public function testHead()
    {
        $request = Remote::head('https://getkirby.com');
        $this->assertSame('HEAD', $request->method());
    }

    public function testHeaders()
    {
        $request = new Remote('https://getkirby.com');
        $this->assertSame([], $request->headers());
    }

    public function testInfo()
    {
        $request = new Remote('https://getkirby.com');
        $this->assertSame([], $request->info());
    }

    public function testPatch()
    {
        $request = Remote::patch('https://getkirby.com');
        $this->assertSame('PATCH', $request->method());
    }

    public function testPost()
    {
        $request = Remote::post('https://getkirby.com');
        $this->assertSame('POST', $request->method());
    }

    public function testPut()
    {
        $request = Remote::put('https://getkirby.com');
        $this->assertSame('PUT', $request->method());
    }

    public function testRequest()
    {
        $request = new Remote($url = 'https://getkirby.com');

        $this->assertSame($url, $request->url());
        $this->assertSame('GET', $request->method());
    }
}
