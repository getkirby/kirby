<?php

namespace Kirby\Api;

use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use Kirby\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ChangesRoutesTest extends TestCase
{
	public const string TMP = KIRBY_TMP_DIR . '/Api.ChangesRoutes';

	protected function setUp(): void
	{
		$this->app = new App([
			'options' => [
				'api.allowImpersonation' => true
			],
			'roots' => [
				'index' => static::TMP,
			],
			'site' => [
				'children' => [
					[
						'slug' => 'a'
					]
				]
			]
		]);

		$this->app->impersonate('kirby');
		Dir::make(static::TMP);
	}

	protected function tearDown(): void
	{
		Dir::remove(static::TMP);
		App::destroy();
	}

	public function testUnlock(): void
	{
		$response = $this->app->api()->call(
			path: 'pages/a/changes/unlock',
			method: 'POST'
		);

		$this->assertSame(['status' => 'ok'], $response);
	}

	public function testUnlockWithInvalidModelType(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid model type: foo');

		$this->app->api()->call(
			path: 'foo/bar/changes/unlock',
			method: 'POST'
		);
	}

	public static function missingModelProvider(): array
	{
		return [
			'file' => ['pages/a/files/missing.jpg'],
			'page' => ['pages/missing'],
			'user' => ['users/missing'],
		];
	}

	#[DataProvider('missingModelProvider')]
	public function testUnlockWithMissingModel(string $path): void
	{
		$response = $this->app->api()->call(
			path: $path . '/changes/unlock',
			method: 'POST'
		);

		$this->assertSame(['status' => 'ok'], $response);
	}
}
