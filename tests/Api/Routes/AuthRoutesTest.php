<?php

namespace Kirby\Api;

use Kirby\Cms\App;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;

class AuthRoutesTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Cms.AuthRoutes';

	protected function setUp(): void
	{
		$this->app = new App([
			'options' => [
				'api.allowImpersonation' => true,
				'api.csrf'               => 'test-csrf'
			],
			'roots' => [
				'index' => static::TMP
			],
			'users' => [
				[
					'email'    => 'test@getkirby.com',
					'role'     => 'admin',
					'password' => User::hashPassword('secret123')
				]
			]
		]);
	}

	protected function tearDown(): void
	{
		$this->app->session()->destroy();
		Dir::remove(static::TMP);
	}

	public function testGet(): void
	{
		$this->app->impersonate('kirby');

		$response = $this->app->api()->call('auth');

		$this->assertSame('kirby@getkirby.com', $response['data']['email']);
	}

	public function testLogin(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => ['csrf' => 'test-csrf']
			]
		]);

		$response = $this->app->api()->call('auth/login', 'POST', [
			'body' => [
				'email'    => 'test@getkirby.com',
				'password' => 'secret123',
				'long'     => true
			]
		]);

		$this->assertSame('ok', $response['status']);
		$this->assertSame('test@getkirby.com', $response['user']['email']);
		$this->assertSame('test@getkirby.com', $this->app->user()->email());
		$this->assertFalse($this->app->session()->timeout());
	}

	public function testLoginWithoutCSRF(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid CSRF token');

		$response = $this->app->api()->call('auth/login', 'POST');
	}

	public function testLoginWithoutLong(): void
	{
		$this->app = $this->app->clone([
			'request' => [
				'query' => ['csrf' => 'test-csrf']
			]
		]);

		$response = $this->app->api()->call('auth/login', 'POST', [
			'body' => [
				'email'    => 'test@getkirby.com',
				'password' => 'secret123'
			]
		]);

		$this->assertSame('ok', $response['status']);
		$this->assertSame('test@getkirby.com', $response['user']['email']);
		$this->assertSame(1800, $this->app->session()->timeout());
	}
}
