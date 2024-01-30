<?php

namespace Kirby\Blueprint;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Exception\InvalidArgumentException;
use Kirby\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
	protected $app;

	public function app(array $props = [])
	{
		return $this->app = new App(array_replace_recursive([
			'roots' => [
				'index' => '/dev/null'
			],
			'users' => [
				[
					'email' => 'test@getkirby.com',
					'role'  => 'admin'
				]
			]
		], $props));
	}

	public function assertValidationError(string $message): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage($message);
	}

	public function file()
	{
		return new File([
			'filename' => 'test.jpg',
			'parent'   => $this->model()
		]);
	}

	public function model()
	{
		return new Page(['slug' => 'test']);
	}

	public function setUp(): void
	{
		$this->app();
	}

	public function site()
	{
		return $this->app->site();
	}

	public function user()
	{
		return $this->app->user('test@getkirby.com');
	}
}
