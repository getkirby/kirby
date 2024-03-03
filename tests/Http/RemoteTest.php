<?php

namespace Kirby\Http;

use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;

class RemoteTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Http.Remote';

	protected $cwd;
	protected $defaults;

	public function setUp(): void
	{
		$this->cwd = getcwd();

		$this->defaults = Remote::$defaults;
		IniStore::$data['curl.cainfo'] = false;

		Remote::$defaults = array_merge($this->defaults, [
			'test' => true,
			'key'  => 'value'
		]);

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		chdir($this->cwd);

		Remote::$defaults = $this->defaults;
		unset(IniStore::$data['curl.cainfo']);

		Dir::remove(static::TMP);
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

		// default with php.ini setting (invalid): internal CA
		ini_set('curl.cainfo', __DIR__ . '/does-not-exist.pem');
		$request = Remote::get('https://getkirby.com');
		$this->assertTrue($request->curlopt[CURLOPT_SSL_VERIFYPEER]);
		$this->assertSame(dirname(__DIR__, 2) . '/cacert.pem', $request->curlopt[CURLOPT_CAINFO]);
		$this->assertArrayNotHasKey(CURLOPT_CAPATH, $request->curlopt);

		// default with php.ini setting (valid): system CA
		ini_set('curl.cainfo', __FILE__);
		$request = Remote::get('https://getkirby.com');
		$this->assertTrue($request->curlopt[CURLOPT_SSL_VERIFYPEER]);
		$this->assertArrayNotHasKey(CURLOPT_CAINFO, $request->curlopt);
		$this->assertArrayNotHasKey(CURLOPT_CAPATH, $request->curlopt);
		ini_restore('curl.cainfo');

		// explicit internal CA
		$request = Remote::get('https://getkirby.com', [
			'ca' => Remote::CA_INTERNAL
		]);
		$this->assertTrue($request->curlopt[CURLOPT_SSL_VERIFYPEER]);
		$this->assertSame(dirname(__DIR__, 2) . '/cacert.pem', $request->curlopt[CURLOPT_CAINFO]);
		$this->assertArrayNotHasKey(CURLOPT_CAPATH, $request->curlopt);

		// explicit internal CA with php.ini setting
		ini_set('curl.cainfo', __FILE__);
		$request = Remote::get('https://getkirby.com', [
			'ca' => Remote::CA_INTERNAL
		]);
		$this->assertTrue($request->curlopt[CURLOPT_SSL_VERIFYPEER]);
		$this->assertSame(dirname(__DIR__, 2) . '/cacert.pem', $request->curlopt[CURLOPT_CAINFO]);
		$this->assertArrayNotHasKey(CURLOPT_CAPATH, $request->curlopt);
		ini_restore('curl.cainfo');

		// explicit internal CA with an existing file named like the constant
		chdir(static::TMP);
		touch(Remote::CA_INTERNAL);
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

		// system CA with an existing file named like the constant
		chdir(static::TMP);
		touch(Remote::CA_SYSTEM);
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

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid "ca" option for the Remote class');
		$request = Remote::get('https://getkirby.com', [
			'ca' => 'does-not-exist'
		]);
	}

	public function testOptionsFromApp()
	{
		new App([
			'options' => [
				'remote' => [
					'key'  => 'different-value',
					'body' => false
				]
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
		$this->assertNull($request->content());
	}

	public function testCode()
	{
		$request = Remote::put('https://getkirby.com');
		$this->assertNull($request->code());
	}

	public function testDelete()
	{
		$request = Remote::delete('https://getkirby.com');
		$this->assertSame('DELETE', $request->method());
	}

	public function testGet()
	{
		// default
		$request = Remote::get('https://getkirby.com');
		$this->assertSame('GET', $request->method());

		// url without query string + query options
		$request = Remote::get('https://getkirby.com/a', ['data' => ['b' => 'foo']]);
		$this->assertSame('GET', $request->method());
		$this->assertSame('https://getkirby.com/a?b=foo', $request->url());

		// url with query string + query options
		$request = Remote::get('https://getkirby.com/a?b=c', ['data' => ['d' => 'foo']]);
		$this->assertSame('GET', $request->method());
		$this->assertSame('https://getkirby.com/a?b=c&d=foo', $request->url());
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
