<?php

namespace Kirby\Http;

use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;

#[CoversClass(Environment::class)]
class EnvironmentTest extends TestCase
{
	public const string FIXTURES = __DIR__ . '/fixtures/EnvironmentTest';

	public function tearDown(): void
	{
		App::destroy();
	}

	public function testAllowFromInsecureHost(): void
	{
		$env = new Environment([
			'allowed' => '*'
		], [
			'HTTP_HOST' => 'example.com'
		]);

		$this->assertSame('http://example.com', $env->baseUrl());
		$this->assertSame('example.com', $env->host());
	}

	public function testAllowFromInsecureForwardedHost(): void
	{
		$env = new Environment([
			'allowed' => '*'
		], [
			'HTTP_X_FORWARDED_HOST' => 'example.com'
		]);

		$this->assertSame('http://example.com', $env->baseUrl());
		$this->assertSame('example.com', $env->host());
	}

	public function testAllowFromRelativeUrl(): void
	{
		$env = new Environment([
			'allowed' => '/'
		], []);

		$this->assertSame('/', $env->baseUrl());
		$this->assertNull($env->host());
	}

	public function testAllowFromRelativeUrlWithSubfolder(): void
	{
		$env = new Environment([
			'allowed' => '/subfolder'
		], []);

		$this->assertSame('/subfolder', $env->baseUrl());
		$this->assertNull($env->host());
	}

	public function testAllowFromServerName(): void
	{
		$env = new Environment([], [
			'SERVER_NAME' => 'example.com'
		]);

		$this->assertSame('http://example.com', $env->baseUrl());
		$this->assertSame('example.com', $env->host());
	}

	public function testAllowFromUrl(): void
	{
		$env = new Environment([
			'allowed' => 'http://example.com'
		], [
			'HTTP_HOST' => 'example.com'
		]);

		$this->assertSame('http://example.com', $env->baseUrl());
		$this->assertSame('example.com', $env->host());
	}

	public function testAllowFromUrls(): void
	{
		$env = new Environment([
			'allowed' => [
				null,
				'http://example.com',
				'http://staging.example.com'
			]
		], [
			'HTTP_HOST' => 'example.com'
		]);

		$this->assertSame('http://example.com', $env->baseUrl());
		$this->assertSame('example.com', $env->host());
	}

	public function testAllowFromUrlsWithSubfolders(): void
	{
		$env = new Environment([
			'cli'     => false,
			'allowed' => [
				true,
				'http://localhost/path-a',
				'http://localhost/path-b'
			]
		], [
			'HTTP_HOST'   => 'localhost',
			'SCRIPT_NAME' => '/path-a/index.php'
		]);

		$this->assertSame('http://localhost/path-a', $env->baseUrl());
		$this->assertSame('localhost', $env->host());
	}

	public function testAllowFromUrlsWithSlash(): void
	{
		$env = new Environment([
			'allowed' => [
				'http://getkirby.com/',
			]
		], [
			'SERVER_NAME' => 'getkirby.com',
			'SCRIPT_NAME' => '/index.php'
		]);

		$this->assertSame('http://getkirby.com', $env->baseUrl());
		$this->assertSame('getkirby.com', $env->host());
	}

	public function testBaseUri(): void
	{
		// nothing given
		$env = new Environment();
		$this->assertInstanceOf(Uri::class, $env->baseUri());
	}

	public function testBaseUrl(): void
	{
		// nothing given
		$env = new Environment();
		$this->assertSame('/', $env->baseUrl());

		// host only
		$env = new Environment(['cli' => false], [
			'SERVER_NAME' => 'getkirby.com'
		]);

		$this->assertSame('http://getkirby.com', $env->baseUrl());

		// empty host in subfolder
		$env = new Environment(['cli' => false], [
			'SCRIPT_NAME' => '/subfolder/index.php'
		]);

		$this->assertSame('/subfolder', $env->baseUrl());

		// server address
		$env = new Environment(['cli' => false], [
			'SERVER_ADDR' => '127.0.0.1',
			'SERVER_PORT' => 8888
		]);

		$this->assertSame('http://127.0.0.1:8888', $env->baseUrl());

		// all parts
		$env = new Environment(['cli' => false], [
			'HTTPS'       => true,
			'SERVER_NAME' => 'getkirby.com',
			'SERVER_PORT' => 8888,
			'SCRIPT_NAME' => '/subfolder/index.php'
		]);

		$this->assertSame('https://getkirby.com:8888/subfolder', $env->baseUrl());

		// proxy with Forwarded header
		$env = new Environment(['cli' => false, 'allowed' => '*'], [
			'HTTP_FORWARDED' => 'proto=https;host="getkirby.com:8888"'
		]);

		$this->assertSame('https://getkirby.com:8888', $env->baseUrl());

		// proxy with X-Forwarded-* headers
		$env = new Environment(['cli' => false, 'allowed' => '*'], [
			'HTTP_X_FORWARDED_SSL'  => true,
			'HTTP_X_FORWARDED_HOST' => 'getkirby.com',
			'HTTP_X_FORWARDED_PORT' => 8888,
		]);

		$this->assertSame('https://getkirby.com:8888', $env->baseUrl());
	}

	public function testCli(): void
	{
		// enabled
		$env = new Environment();
		$this->assertTrue($env->cli());

		// force enabled
		$env = new Environment([
			'cli' => true
		]);
		$this->assertTrue($env->cli());

		// disabled
		$env = new Environment([
			'cli' => false
		]);
		$this->assertFalse($env->cli());
	}

	public function testDetect(): void
	{
		// empty server info
		$env = new Environment();
		$this->assertSame([], $env->detect(null, []));
	}

	public static function detectForwardedProvider(): array
	{
		return [
			[
				['SERVER_NAME' => 'trykirby.com', 'HTTP_FORWARDED' => 'host=getkirby.com'],
				'http://getkirby.com'
			],
			[
				['SERVER_NAME' => 'trykirby.com', 'HTTP_FORWARDED' => 'host=getkirby.com;proto=https'],
				'https://getkirby.com'
			],
			[
				['SERVER_NAME' => 'trykirby.com', 'HTTP_FORWARDED' => 'host=getkirby.com:443;proto=https'],
				'https://getkirby.com'
			],
			[
				['SERVER_NAME' => 'trykirby.com', 'HTTP_FORWARDED' => 'host=getkirby.com:8888'],
				'http://getkirby.com:8888'
			],
			[
				['SERVER_NAME' => 'trykirby.com', 'HTTP_FORWARDED' => 'host=getkirby.com:abc'],
				'http://getkirby.com'
			],
			[
				['SERVER_NAME' => 'trykirby.com', 'HTTP_FORWARDED' => 'host=getkirby.com:8888;proto=https'],
				'https://getkirby.com:8888'
			],
			[
				['SERVER_NAME' => 'trykirby.com', 'HTTP_FORWARDED' => 'host="getkirby.com";proto="https"'],
				'https://getkirby.com'
			],
			[
				['SERVER_NAME' => 'trykirby.com', 'HTTP_FORWARDED' => 'HOST="getkirby.com";PrOtO="https"'],
				'https://getkirby.com'
			],
			[
				[
					'SERVER_NAME' => 'trykirby.com',
					'HTTP_FORWARDED' => 'host="getkirby.com";proto="https", host="backend.getkirby.com:8080";proto=http'
				],
				'https://getkirby.com'
			],
			[
				[
					'SERVER_NAME' => 'trykirby.com',
					'HTTP_FORWARDED' => 'host="getkirby.com";field="with,comma";proto="https"'
				],
				'https://getkirby.com'
			],
			[
				[
					'SERVER_NAME' => 'trykirby.com',
					'HTTP_FORWARDED' => 'host="getkirby.com";proto="https";field="with;proto=http;"'
				],
				'https://getkirby.com'
			],
			[
				['SERVER_NAME' => 'trykirby.com', 'HTTP_FORWARDED' => 'proto=https'],
				'https://trykirby.com'
			],
			[
				['SERVER_NAME' => 'trykirby.com', 'HTTP_X_FORWARDED_HOST' => 'getkirby.com'],
				'http://getkirby.com'
			],
			[
				['SERVER_NAME' => 'trykirby.com', 'HTTP_X_FORWARDED_HOST' => 'getkirby.com', 'HTTP_X_FORWARDED_PROTO' => 'https'],
				'https://getkirby.com'
			],
			[
				['SERVER_NAME' => 'trykirby.com', 'HTTP_X_FORWARDED_HOST' => 'getkirby.com', 'HTTP_X_FORWARDED_PROTO' => 'http'],
				'http://getkirby.com'
			],
			[
				['SERVER_NAME' => 'trykirby.com', 'HTTP_X_FORWARDED_HOST' => 'getkirby.com', 'HTTP_X_FORWARDED_PROTO' => 'https, http'],
				'https://getkirby.com'
			],
			[
				['SERVER_NAME' => 'trykirby.com', 'HTTP_X_FORWARDED_HOST' => 'getkirby.com', 'HTTP_X_FORWARDED_SSL' => 'on'],
				'https://getkirby.com'
			],
			[
				['SERVER_NAME' => 'trykirby.com', 'HTTP_X_FORWARDED_HOST' => 'getkirby.com', 'HTTP_X_FORWARDED_PORT' => '8888'],
				'http://getkirby.com:8888'
			],
			[
				['SERVER_NAME' => 'trykirby.com', 'HTTP_X_FORWARDED_HOST' => 'getkirby.com:8888'],
				'http://getkirby.com:8888'
			],
			[
				['SERVER_NAME' => 'trykirby.com', 'HTTP_X_FORWARDED_HOST' => 'getkirby.com:8888', 'HTTP_X_FORWARDED_SSL' => 'on'],
				'https://getkirby.com:8888'
			],
			[
				['SERVER_NAME' => 'trykirby.com', 'HTTP_X_FORWARDED_HOST' => 'getkirby.com:abc', 'HTTP_X_FORWARDED_SSL' => 'on'],
				'https://getkirby.com'
			],
			[
				[
					'SERVER_NAME'            => 'trykirby.com',
					'HTTP_X_FORWARDED_HOST'  => 'getkirby.com',
					'HTTP_X_FORWARDED_PORT'  => '8888',
					'HTTP_X_FORWARDED_PROTO' => 'https'
				],
				'https://getkirby.com:8888'
			],
			[
				['SERVER_NAME' => 'trykirby.com', 'HTTP_X_FORWARDED_PORT' => '8888'],
				'http://trykirby.com:8888'
			],
			[
				['SERVER_NAME' => 'trykirby.com', 'HTTP_X_FORWARDED_PROTO' => 'https'],
				'https://trykirby.com'
			],
			[
				[
					'SERVER_NAME' => 'trykirby.com',
					'HTTP_X_FORWARDED_PORT' => '8888',
					'HTTP_X_FORWARDED_PROTO' => 'https'
				],
				'https://trykirby.com:8888'
			],
		];
	}

	#[DataProvider('detectForwardedProvider')]
	public function testDetectForwarded($info, $expected): void
	{
		$env = new Environment(['allowed' => '*'], $info);

		$this->assertSame($expected, $env->baseUrl());
	}

	public function testDisallowFromInsecureHost(): void
	{
		$env = new Environment([], [
			'HTTP_HOST' => 'example.com'
		]);

		$this->assertNull($env->host());
	}

	public function testDisallowFromInvalidSubfolders(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The environment is not allowed');

		new Environment([
			'allowed' => [
				'http://localhost/path-b',
				'http://localhost/path-c'
			]
		], [
			'HTTP_HOST'   => 'localhost',
			'SCRIPT_NAME' => '/path-a/index.php'
		]);
	}

	public function testGet(): void
	{
		$env = new Environment(null, $info = [
			'HTTP_HOST'        => 'something/GETKIRBY.COM',
			'HTTP_K_SOMETHING' => 'custom value',
			'argv'             => 'lower case stuff'
		]);
		$info['HTTP_HOST'] = 'getkirby.com';

		$this->assertSame($info, $env->get());
		$this->assertSame($info, $env->get(false));
		$this->assertSame($info, $env->get(null));
		$this->assertSame('getkirby.com', $env->get('HTTP_HOST'));
		$this->assertSame('getkirby.com', $env->get('http_host'));
		$this->assertSame('custom value', $env->get('HTTP_K_SOMETHING'));
		$this->assertSame('custom value', $env->get('http_k_something'));
		$this->assertSame('fallback', $env->get('http_does_not_exist', 'fallback'));
		$this->assertSame('lower case stuff', $env->get('argv'));
	}

	#[BackupGlobals(true)]
	public function testGetGlobally(): void
	{
		$_SERVER = [
			'HTTP_HOST'        => 'something/GETKIRBY.COM',
			'HTTP_K_SOMETHING' => 'custom value ($_SERVER)',
			'argv'             => 'lower case stuff ($_SERVER)'
		];
		$sanitized = $_SERVER;
		$sanitized['HTTP_HOST'] = 'getkirby.com';

		$this->assertSame($sanitized, Environment::getGlobally());
		$this->assertSame($sanitized, Environment::getGlobally(false));
		$this->assertSame($sanitized, Environment::getGlobally(null));
		$this->assertSame('getkirby.com', Environment::getGlobally('HTTP_HOST'));
		$this->assertSame('getkirby.com', Environment::getGlobally('http_host'));
		$this->assertSame('custom value ($_SERVER)', Environment::getGlobally('HTTP_K_SOMETHING'));
		$this->assertSame('custom value ($_SERVER)', Environment::getGlobally('http_k_something'));
		$this->assertSame('fallback', Environment::getGlobally('http_does_not_exist', 'fallback'));
		$this->assertSame('lower case stuff ($_SERVER)', Environment::getGlobally('argv'));

		new App([
			'server' => $info = [
				'HTTP_HOST'        => 'something/TRYKIRBY.COM',
				'HTTP_K_SOMETHING' => 'custom value (app)',
				'argv'             => 'lower case stuff (app)'
			]
		]);
		$info['HTTP_HOST'] = 'trykirby.com';

		$this->assertSame($info, Environment::getGlobally());
		$this->assertSame($info, Environment::getGlobally(false));
		$this->assertSame($info, Environment::getGlobally(null));
		$this->assertSame('trykirby.com', Environment::getGlobally('HTTP_HOST'));
		$this->assertSame('trykirby.com', Environment::getGlobally('http_host'));
		$this->assertSame('custom value (app)', Environment::getGlobally('HTTP_K_SOMETHING'));
		$this->assertSame('custom value (app)', Environment::getGlobally('http_k_something'));
		$this->assertSame('fallback', Environment::getGlobally('http_does_not_exist', 'fallback'));
		$this->assertSame('lower case stuff (app)', Environment::getGlobally('argv'));
	}

	public function testHost(): void
	{
		// via server name
		$env = new Environment(null, [
			'SERVER_NAME' => 'getkirby.com'
		]);

		$this->assertSame('getkirby.com', $env->host());

		// via server addr
		$env = new Environment(null, [
			'SERVER_ADDR' => '127.0.0.1'
		]);

		$this->assertSame('127.0.0.1', $env->host());
	}

	public function testHostAllowedSingle(): void
	{
		$env = new Environment(['allowed' => 'https://getkirby.com']);

		$this->assertSame('getkirby.com', $env->host());
	}

	public function testHostAllowedMultiple(): void
	{
		$options = [
			'allowed' => [
				'https://getkirby.com',
				'http://test.getkirby.com'
			]
		];

		// via server name
		$env = new Environment($options, [
			'SERVER_NAME' => 'test.getkirby.com'
		]);

		$this->assertSame('test.getkirby.com', $env->host());

		// via insecure host is fine in this case
		$env = new Environment($options, [
			'HTTP_HOST' => 'test.getkirby.com'
		]);

		$this->assertSame('test.getkirby.com', $env->host());

		// via insecure forwarded host is also fine
		$env = new Environment($options, [
			'HTTP_FORWARDED' => 'host="test.getkirby.com"'
		]);

		$this->assertSame('test.getkirby.com', $env->host());

		// also with the X-Forwarded-Host header
		$env = new Environment($options, [
			'HTTP_X_FORWARDED_HOST' => 'test.getkirby.com'
		]);

		$this->assertSame('test.getkirby.com', $env->host());
	}

	public function testHostForbidden(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The environment is not allowed');

		new Environment(
			[
				'allowed' => [
					'https://getkirby.com',
					'https://test.getkirby.com'
				]
			],
			[
				'SERVER_NAME' => 'google.com'
			]
		);
	}

	public function testHostIgnoreInsecure(): void
	{
		// not possible via insecure header
		$env = new Environment(null, [
			'HTTP_HOST' => 'getkirby.com'
		]);

		$this->assertNull($env->host());

		// not possible via insecure forwarded header
		$env = new Environment(null, [
			'HTTP_FORWARDED' => 'host=getkirby.com'
		]);

		$this->assertNull($env->host());

		$env = new Environment(null, [
			'HTTP_X_FORWARDED_HOST' => 'getkirby.com'
		]);

		$this->assertNull($env->host());
	}

	public function testHostInsecure(): void
	{
		// insecure host header
		$env = new Environment(['allowed' => '*'], [
			'HTTP_HOST' => 'getkirby.com'
		]);

		$this->assertSame('getkirby.com', $env->host());

		// insecure Forwarded header
		$env = new Environment(['allowed' => '*'], [
			'HTTP_FORWARDED' => 'host=getkirby.com'
		]);

		$this->assertSame('getkirby.com', $env->host());

		// insecure forwarded host header
		$env = new Environment(['allowed' => '*'], [
			'HTTP_X_FORWARDED_HOST' => 'getkirby.com'
		]);

		$this->assertSame('getkirby.com', $env->host());
	}

	public static function httpsProvider(): array
	{
		return [
			['off', false],
			[null, false],
			['', false],
			[0, false],
			['0', false],
			[false, false],
			['false', false],
			[-1, false],
			['-1', false],
			['on', true],
			[true, true],
			['true', true],
			['1', true],
			[1, true],
			['https', true],
		];
	}

	#[DataProvider('httpsProvider')]
	public function testHttps($value, $expected): void
	{
		// via server config
		$env = new Environment(null, [
			'HTTPS' => $value
		]);

		$this->assertSame($expected, $env->https());
	}

	public function testHttpsAllowedSingle(): void
	{
		$env = new Environment(['allowed' => 'http://getkirby.com']);
		$this->assertFalse($env->https());

		$env = new Environment(['allowed' => 'https://getkirby.com']);
		$this->assertTrue($env->https());
	}

	public function testHttpsAllowedMultiple(): void
	{
		$options = [
			'allowed' => [
				'http://a.getkirby.com',
				'https://b.getkirby.com',
			]
		];

		// via server name: https off
		$env = new Environment($options, [
			'SERVER_NAME' => 'a.getkirby.com'
		]);

		$this->assertFalse($env->https());

		// via server name: https on
		$env = new Environment($options, [
			'HTTPS'       => 'on',
			'SERVER_NAME' => 'b.getkirby.com'
		]);

		$this->assertTrue($env->https());

		// via Forwarded: no proto
		$env = new Environment($options, [
			'HTTP_FORWARDED' => 'host="a.getkirby.com"'
		]);

		$this->assertFalse($env->https());

		// via Forwarded: https off
		$env = new Environment($options, [
			'HTTP_FORWARDED' => 'host="a.getkirby.com";proto=http'
		]);

		$this->assertFalse($env->https());

		// via Forwarded: https on
		$env = new Environment($options, [
			'HTTP_FORWARDED' => 'host="b.getkirby.com";proto=https'
		]);

		$this->assertTrue($env->https());

		// via forwarded ssl: https off
		$env = new Environment($options, [
			'HTTP_X_FORWARDED_HOST' => 'a.getkirby.com',
			'HTTP_X_FORWARDED_SSL'  => false
		]);

		$this->assertFalse($env->https());

		// via forwarded ssl: https on
		$env = new Environment($options, [
			'HTTP_X_FORWARDED_HOST' => 'b.getkirby.com',
			'HTTP_X_FORWARDED_SSL'  => true
		]);

		$this->assertTrue($env->https());

		// via forwarded proto: https off
		$env = new Environment($options, [
			'HTTP_X_FORWARDED_HOST'  => 'a.getkirby.com',
			'HTTP_X_FORWARDED_PROTO' => 'http'
		]);

		$this->assertFalse($env->https());

		// via forwarded proto: https on
		$env = new Environment($options, [
			'HTTP_X_FORWARDED_HOST'  => 'b.getkirby.com',
			'HTTP_X_FORWARDED_PROTO' => 'https'
		]);

		$this->assertTrue($env->https());
	}

	public function testHttpsForbidden(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The environment is not allowed');

		new Environment(
			[
				'allowed' => [
					'https://getkirby.com',
					'https://test.getkirby.com'
				]
			],
			[
				'HTTPS'       => 'off',
				'SERVER_NAME' => 'getkirby.com'
			]
		);
	}

	public static function httpsFromProtocolProvider(): array
	{
		return [
			['http', false],
			[null, false],
			['https', true],
			['HTTPS', true],
			['https, http', true],
			['HTTPS, http', true],
		];
	}

	#[DataProvider('httpsFromProtocolProvider')]
	public function testHttpsFromProtocol($value, $expected): void
	{
		$env = new Environment(['allowed' => '*'], [
			'HTTP_FORWARDED' => 'host=getkirby.com;proto="' . $value . '"',
		]);

		$this->assertSame($expected, $env->https());

		$env = new Environment(['allowed' => '*'], [
			'HTTP_X_FORWARDED_HOST'  => 'getkirby.com',
			'HTTP_X_FORWARDED_PROTO' => $value
		]);

		$this->assertSame($expected, $env->https());
	}

	public function testHttpsIgnoreInsecure(): void
	{
		$env = new Environment(null, [
			'HTTP_FORWARDED' => 'proto=https'
		]);

		$this->assertFalse($env->https());

		$env = new Environment(null, [
			'HTTP_X_FORWARDED_SSL' => 'on'
		]);

		$this->assertFalse($env->https());
	}

	public function testHttpsInsecure(): void
	{
		// insecure forwarded https header
		$env = new Environment(['allowed' => '*'], [
			'HTTP_FORWARDED' => 'host=getkirby.com;proto=https'
		]);

		$this->assertTrue($env->https());

		// insecure forwarded https header
		$env = new Environment(['allowed' => '*'], [
			'HTTP_X_FORWARDED_SSL'  => 'on',
			'HTTP_X_FORWARDED_HOST' => 'getkirby.com'
		]);

		$this->assertTrue($env->https());
	}

	public function testIgnoreFromInsecureForwardedHost(): void
	{
		$env = new Environment([], [
			'HTTP_FORWARDED' => 'host=example.com'
		]);

		$this->assertNull($env->host());

		$env = new Environment([], [
			'HTTP_X_FORWARDED_HOST' => 'example.com'
		]);

		$this->assertNull($env->host());
	}

	public function testInfo(): void
	{
		// no info
		$env = new Environment();

		$this->assertSame($_SERVER, $env->info());


		// empty info
		$env = new Environment(null, []);

		$this->assertSame([], $env->info());


		// custom info
		$env = new Environment(null, $info = [
			'HTTP_X_FORWARDED_SSL'  => 'on',
			'HTTP_X_FORWARDED_HOST' => 'getkirby.com'
		]);

		$this->assertSame($info, $env->info());
	}

	public function testInvalidAllowList(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid allow list setup for base URLs');

		new Environment([
			'allowed' => [new stdClass()]
		], [
			'HTTP_HOST' => 'example.com'
		]);
	}

	public function testIp(): void
	{
		// no ip
		$env = new Environment();

		$this->assertNull($env->address());
		$this->assertNull($env->ip());

		// via server address
		$env = new Environment(null, [
			'SERVER_ADDR' => '127.0.0.1'
		]);

		$this->assertSame('127.0.0.1', $env->address());
		$this->assertSame('127.0.0.1', $env->ip());
	}

	public function testIsBehindProxy(): void
	{
		// no value given
		$env = new Environment();
		$this->assertFalse($env->isBehindProxy());

		// given host but with secure checking 1
		$env = new Environment(null, [
			'HTTP_FORWARDED' => 'host=getkirby.com'
		]);

		$this->assertFalse($env->isBehindProxy());

		// given host but with secure checking 2
		$env = new Environment(null, [
			'HTTP_X_FORWARDED_HOST' => 'getkirby.com'
		]);

		$this->assertFalse($env->isBehindProxy());

		// given host with allowlist 1
		$env = new Environment([
			'allowed' => ['http://getkirby.com', 'http://trykirby.com']
		], [
			'HTTP_FORWARDED' => 'host=getkirby.com'
		]);

		$this->assertTrue($env->isBehindProxy());

		// given host with allowlist 2
		$env = new Environment([
			'allowed' => ['http://getkirby.com', 'http://trykirby.com']
		], [
			'HTTP_X_FORWARDED_HOST' => 'getkirby.com'
		]);

		$this->assertTrue($env->isBehindProxy());

		// given host with fixed host 1
		$env = new Environment([
			'allowed' => 'http://getkirby.com'
		], [
			'HTTP_FORWARDED' => 'host=getkirby.com'
		]);

		$this->assertNull($env->isBehindProxy());

		// given host with fixed host 2
		$env = new Environment([
			'allowed' => 'http://getkirby.com'
		], [
			'HTTP_X_FORWARDED_HOST' => 'getkirby.com'
		]);

		$this->assertNull($env->isBehindProxy());

		// given host with wildcard 1
		$env = new Environment([
			'allowed' => '*'
		], [
			'HTTP_FORWARDED' => 'host=getkirby.com'
		]);

		$this->assertTrue($env->isBehindProxy());

		// given host with wildcard 2
		$env = new Environment([
			'allowed' => '*'
		], [
			'HTTP_X_FORWARDED_HOST' => 'getkirby.com'
		]);

		$this->assertTrue($env->isBehindProxy());

		// empty host 1
		$env = new Environment([
			'allowed' => '*'
		], [
			'HTTP_FORWARDED' => 'host='
		]);

		$this->assertFalse($env->isBehindProxy());

		// empty host 2
		$env = new Environment([
			'allowed' => '*'
		], [
			'HTTP_X_FORWARDED_HOST' => ''
		]);

		$this->assertFalse($env->isBehindProxy());
	}

	public static function isLocalWithIpProvider(): array
	{
		return [
			['127.0.0.1', '127.0.0.1', '127.0.0.1', true],
			['::1', '::1', '::1', true],
			['127.0.0.1', '::1', null, true],
			['::1', '127.0.0.1', false, true],
			['1.2.3.4', '127.0.0.1', '::1', false],
			['127.0.0.1', null, '1.2.3.4', false],
			['127.0.0.1', null, '', true],
			[null, null, null, false],
			['', null, false, false],
		];
	}

	#[DataProvider('isLocalWithIpProvider')]
	public function testIsLocalWithIp($address, $forwardedFor, $clientIp, bool $expected): void
	{
		$env = new Environment(null, [
			'REMOTE_ADDR' => $address,
			'HTTP_X_FORWARDED_FOR' => $forwardedFor,
			'HTTP_CLIENT_IP' => $clientIp,
		]);

		$this->assertSame($expected, $env->isLocal());

		$env = new Environment(null, [
			'REMOTE_ADDR' => $address,
			'HTTP_FORWARDED' => 'for="' . $forwardedFor . '"',
			'HTTP_CLIENT_IP' => $clientIp,
		]);

		$this->assertSame($expected, $env->isLocal());
	}

	public static function isLocalWithServerNameProvider(): array
	{
		return [
			['localhost', true],
			['mydomain.local', true],
			['mydomain.test', true],
			['mydomain.ddev.site', true],
			['mydomain.com', false],
			['mydomain.dev', false],
		];
	}

	#[DataProvider('isLocalWithServerNameProvider')]
	public function testIsLocalWithServerName($name, $expected): void
	{
		$env = new Environment(null, [
			'SERVER_NAME' => $name
		]);

		$this->assertSame($expected, $env->isLocal());
	}

	public function testOptions(): void
	{
		$env = new Environment(null, [
			'SERVER_NAME' => 'example.com'
		]);

		$this->assertSame('test option', $env->options(static::FIXTURES)['test']);
	}

	public function testOptionsFromServerAddress(): void
	{
		$env = new Environment(null, [
			'SERVER_ADDR' => '127.0.0.1'
		]);

		$this->assertSame('test address option', $env->options(static::FIXTURES)['test']);
	}

	public function testOptionsFromInvalidHost(): void
	{
		$env = new Environment([
			'cli' => false,
			'allowed' => [
				'http://example.de'
			]
		], [
			'SERVER_NAME' => 'example.com'
		]);

		$this->assertSame([], $env->options(static::FIXTURES));
	}

	public function testOptionsFromCLI(): void
	{
		$env = new Environment([
			'cli' => true
		]);

		$this->assertSame('test cli option', $env->options(static::FIXTURES)['test']);
	}

	public function testPath(): void
	{
		// the path in cli requests is always empty
		$env = new Environment();
		$this->assertSame('', $env->path());

		// the path in HTTPS requests is taken from the script name
		$env = new Environment(['cli' => false], [
			'SCRIPT_NAME' => '/subfolder/index.php'
		]);

		$this->assertSame('subfolder', $env->path());

		// When there's a single allowed URL, the path is extracted from the URL
		$env = new Environment([
			'allowed' => [
				'https://getkirby.com/subfolder'
			]
		]);

		$this->assertSame('subfolder', $env->path());
	}

	public function testPort(): void
	{
		// no port given
		$env = new Environment();
		$this->assertNull($env->port());

		// via server addr
		$env = new Environment(null, [
			'SERVER_PORT' => 8888
		]);

		$this->assertSame(8888, $env->port());

		// via detected host
		$env = new Environment(null, [
			'SERVER_NAME' => 'getkirby.com:8888'
		]);

		$this->assertSame(8888, $env->port());

		// via https
		$env = new Environment(null, [
			'HTTPS' => true
		]);

		$this->assertSame(443, $env->port());

		// via Forwarded with port
		$env = new Environment(['allowed' => '*'], [
			'HTTP_FORWARDED' => 'host=getkirby.com:8888'
		]);

		$this->assertSame(8888, $env->port());

		// via Forwarded with proto
		$env = new Environment(['allowed' => '*'], [
			'HTTP_FORWARDED' => 'host=getkirby.com;proto=https'
		]);

		$this->assertSame(443, $env->port());

		// via forwarded port
		$env = new Environment(['allowed' => '*'], [
			'HTTP_X_FORWARDED_HOST' => 'getkirby.com',
			'HTTP_X_FORWARDED_PORT' => 8888
		]);

		$this->assertSame(8888, $env->port());

		// via forwarded host
		$env = new Environment(['allowed' => '*'], [
			'HTTP_X_FORWARDED_HOST' => 'getkirby.com:8888'
		]);

		$this->assertSame(8888, $env->port());

		// via forwarded proto
		$env = new Environment(['allowed' => '*'], [
			'HTTP_X_FORWARDED_HOST'  => 'getkirby.com',
			'HTTP_X_FORWARDED_PROTO' => 'https'
		]);

		$this->assertSame(443, $env->port());
	}

	public function testPortAllowedSingle(): void
	{
		$env = new Environment(['allowed' => 'http://getkirby.com']);
		$this->assertNull($env->port());

		$env = new Environment(['allowed' => 'http://getkirby.com:9999']);
		$this->assertSame(9999, $env->port());
	}

	public function testPortAllowedMultiple(): void
	{
		$options = [
			'allowed' => [
				'http://getkirby.com',
				'http://getkirby.com:9999',
			]
		];

		// via server name: no port
		$env = new Environment($options, [
			'SERVER_NAME' => 'getkirby.com'
		]);

		$this->assertNull($env->port());

		// via server name: port
		$env = new Environment($options, [
			'SERVER_PORT' => 9999,
			'SERVER_NAME' => 'getkirby.com'
		]);

		$this->assertSame(9999, $env->port());

		// via proxy: no port 1
		$env = new Environment($options, [
			'HTTP_FORWARDED' => 'host=getkirby.com',
		]);

		$this->assertNull($env->port());

		// via proxy: no port 2
		$env = new Environment($options, [
			'HTTP_X_FORWARDED_HOST' => 'getkirby.com',
		]);

		$this->assertNull($env->port());

		// via proxy: port
		$env = new Environment($options, [
			'HTTP_X_FORWARDED_HOST' => 'getkirby.com',
			'HTTP_X_FORWARDED_PORT' => 9999
		]);

		$this->assertSame(9999, $env->port());

		// via proxy: port in host 1
		$env = new Environment($options, [
			'HTTP_FORWARDED' => 'host=getkirby.com:9999'
		]);

		$this->assertSame(9999, $env->port());

		// via proxy: port in host 2
		$env = new Environment($options, [
			'HTTP_X_FORWARDED_HOST' => 'getkirby.com:9999',
		]);

		$this->assertSame(9999, $env->port());
	}

	public function testPortForbidden(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The environment is not allowed');

		new Environment(
			[
				'allowed' => [
					'http://getkirby.com:8888',
					'http://getkirby.com:1234'
				]
			],
			[
				'SERVER_NAME' => 'getkirby.com'
			]
		);
	}

	public function testPortIgnoreInsecure(): void
	{
		$env = new Environment(null, [
			'HTTP_FORWARDED' => 'host=getkirby.com:8888'
		]);

		$this->assertNull($env->port());

		$env = new Environment(null, [
			'HTTP_X_FORWARDED_PORT' => 8888
		]);

		$this->assertNull($env->port());
	}

	public function testPortInsecure(): void
	{
		$env = new Environment(['allowed' => '*'], [
			'HTTP_FORWARDED' => 'host=getkirby.com:9999'
		]);

		$this->assertSame(9999, $env->port());

		$env = new Environment(['allowed' => '*'], [
			'HTTP_X_FORWARDED_PORT'  => 9999,
			'HTTP_X_FORWARDED_HOST' => 'getkirby.com'
		]);

		$this->assertSame(9999, $env->port());
	}

	public function testRequestUrl(): void
	{
		// basic
		$env = new Environment(['cli' => false], []);

		$this->assertSame('/', $env->requestUrl());

		// with server name
		$env = new Environment(['cli' => false], [
			'SERVER_NAME' => 'getkirby.com'
		]);

		$this->assertSame('http://getkirby.com', $env->requestUrl());

		// with request path
		$env = new Environment(['cli' => false], [
			'SERVER_NAME' => 'getkirby.com',
			'REQUEST_URI' => '/blog/article-a',
		]);

		$this->assertSame('http://getkirby.com/blog/article-a', $env->requestUrl());

		// with subfolder path
		$env = new Environment(['cli' => false], [
			'SERVER_NAME' => 'getkirby.com',
			'REQUEST_URI' => '/subfolder/blog/article-a',
			'SCRIPT_NAME' => '/subfolder/index.php',
		]);

		$this->assertSame('http://getkirby.com/subfolder/blog/article-a', $env->requestUrl());
	}

	public static function requestUriProvider(): array
	{
		return [
			[
				null,
				[
					'path'  => '',
					'query' => ''
				]
			],
			[
				'/',
				[
					'path'  => '',
					'query' => ''
				]
			],
			[
				'/foo/bar',
				[
					'path'  => 'foo/bar',
					'query' => ''
				]
			],
			[
				'/foo/bar?foo=bar',
				[
					'path'  => 'foo/bar',
					'query' => 'foo=bar'
				]
			],
			[
				'/foo/bar/page:2?foo=bar',
				[
					'path'  => 'foo/bar',
					'query' => 'foo=bar'
				]
			],
			[
				'index.php?foo=bar',
				[
					'path'  => 'index.php',
					'query' => 'foo=bar'
				]
			],
			[
				'https://getkirby.com/foo/bar?foo=bar',
				[
					'path'  => 'foo/bar',
					'query' => 'foo=bar'
				]
			]
		];
	}

	#[DataProvider('requestUriProvider')]
	public function testRequestUri($value, $expected): void
	{
		$env = new Environment(null, [
			'REQUEST_URI' => $value,
		]);

		$this->assertSame($expected['path'], $env->requestUri($value)->path()->toString());
		$this->assertSame($expected['query'], $env->requestUri($value)->query()->toString());
	}

	public static function sanitizeProvider(): array
	{
		return [
			// needs no sanitizing
			[
				'HTTP_HOST',
				'getkirby.com',
				'getkirby.com'
			],
			[
				'HTTP_HOST',
				'öxample.com',
				'öxample.com'
			],
			[
				'HTTP_HOST',
				'example-with-dashes.com',
				'example-with-dashes.com'
			],
			[
				'SERVER_PORT',
				9999,
				9999
			],

			// needs sanitizing
			[
				'HTTP_HOST',
				'.somehost.com',
				'somehost.com'
			],
			[
				'HTTP_HOST',
				'-somehost.com',
				'somehost.com'
			],
			[
				'HTTP_HOST',
				'<script>foo()</script>',
				'foo'
			],
			[
				'HTTP_X_FORWARDED_HOST',
				'<script>foo()</script>',
				'foo'
			],
			[
				'HTTP_X_FORWARDED_HOST',
				'../some-fake-host',
				'some-fake-host'
			],
			[
				'HTTP_X_FORWARDED_HOST',
				'../',
				null
			],
			[
				'SERVER_PORT',
				'abc9999',
				9999
			],
			[
				'SERVER_PORT',
				'abc',
				null
			],
			[
				'HTTP_X_FORWARDED_PORT',
				'abc9999',
				9999
			]
		];
	}

	#[DataProvider('sanitizeProvider')]
	public function testSanitize($key, $value, $expected): void
	{
		$this->assertSame($expected, Environment::sanitize($key, $value));
	}

	public function testSanitizeAll(): void
	{
		$input    = [];
		$expected = [];

		foreach (static::sanitizeProvider() as $row) {
			$input[$row[0]] = $row[1];
			$expected[$row[0]] = $row[2];
		}

		$this->assertSame($expected, Environment::sanitize($input));
	}

	public static function scriptPathProvider(): array
	{
		return [
			[
				null,
				''
			],
			[
				'',
				''
			],
			[
				' ',
				''
			],
			[
				'/index.php',
				''
			],
			[
				'/subfolder/index.php',
				'subfolder'
			],
			[
				'/subfolder/test.php',
				'subfolder'
			],
			[
				'\subfolder\subsubfolder\index.php',
				'subfolder/subsubfolder'
			],
		];
	}

	#[DataProvider('scriptPathProvider')]
	public function testScriptPath($value, $expected): void
	{
		$env = new Environment(['cli' => false], [
			'SCRIPT_NAME' => $value
		]);

		$this->assertSame($expected, $env->scriptPath());
	}

	public function testScriptPathOnCli(): void
	{
		$env = new Environment(['cli' => true]);

		$this->assertSame('', $env->scriptPath());
	}

	public function testToArray(): void
	{
		$env = new Environment([
			'root' => static::FIXTURES,
		], [
			'SERVER_NAME' => 'example.com'
		]);

		$this->assertSame([
			'baseUrl'       => 'http://example.com',
			'host'          => 'example.com',
			'https'         => false,
			'info'          => [
				'SERVER_NAME' => 'example.com'
			],
			'ip'            => null,
			'isBehindProxy' => false,
			'path'          => '',
			'port'          => null,
			'requestUrl'    => 'http://example.com',
			'scriptPath'    => '',
		], $env->toArray());
	}
}
