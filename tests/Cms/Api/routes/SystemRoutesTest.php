<?php

namespace Kirby\Cms;

use Kirby\TestCase;

class SystemRoutesTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Cms.SystemRoutes';

	protected $app;

	protected function assertUserWithAuthView(array $user): void
	{
		$this->assertArrayHasKey('email', $user);
		$this->assertArrayHasKey('id', $user);
		$this->assertArrayHasKey('language', $user);
		$this->assertArrayHasKey('name', $user);
		$this->assertArrayHasKey('permissions', $user);
		$this->assertArrayHasKey('role', $user);

		$this->assertArrayNotHasKey('content', $user);
		$this->assertArrayNotHasKey('next', $user);
		$this->assertArrayNotHasKey('options', $user);
		$this->assertArrayNotHasKey('prev', $user);
		$this->assertArrayNotHasKey('username', $user);
		$this->assertArrayNotHasKey('uuid', $user);
	}

	protected function assertValidInstallation(array $data): void
	{
		$this->assertTrue($data['isInstalled']);
		$this->assertTrue($data['isOk']);
	}

	protected function createInvalidInstallation(): void
	{
		$this->createValidInstallation();
		chmod($this->app->root('content'), 0o000);
	}

	protected function createUser(string $role = 'admin'): User
	{
		return $this->app->users()->create([
			'email'    => 'test@getkirby.com',
			'role'     => $role,
			'password' => '12345678'
		]);
	}

	protected function createValidInstallation(): void
	{
		$this->app->system();
	}

	public function setUp(): void
	{
		static::setUpTmp();

		$this->app = new App([
			'options' => [
				'api' => [
					'allowImpersonation' => true,
				],
				'panel' => [
					'install' => true
				]
			],
			'roots' => [
				'index' => static::TMP
			],
			'blueprints' => [
				'users/editor' => [
					'name' => 'editor',
					'permissions' => [
						'access' => [
							'system' => false
						]
					]
				]
			]
		]);
	}

	public function tearDown(): void
	{
		chmod($this->app->root('content'), 0o755);

		static::tearDownTmp();
		App::destroy();
	}

	public function testGetLoginView(): void
	{
		$this->createValidInstallation();
		$this->createUser();

		$data = $this->app->api()->call('system', 'GET')['data'];

		$this->assertValidInstallation($data);

		$this->assertTrue($data['isInstallable']);
		$this->assertArrayHasKey('authStatus', $data);
		$this->assertArrayHasKey('loginMethods', $data);
		$this->assertArrayHasKey('title', $data);
		$this->assertArrayHasKey('translation', $data);

		$this->assertArrayNotHasKey('ascii', $data);
		$this->assertArrayNotHasKey('requirements', $data);
		$this->assertArrayNotHasKey('user', $data);
	}

	public function testGetPanelView(): void
	{
		$this->createValidInstallation();
		$user = $this->createUser();

		$this->app->impersonate($user);

		$data = $this->app->api()->call('system', 'GET')['data'];

		$this->assertValidInstallation($data);

		$this->assertArrayHasKey('ascii', $data);
		$this->assertSame('en', $data['defaultLanguage']);
		$this->assertFalse($data['isLocal']);
		$this->assertTrue($data['kirbytext']);
		$this->assertSame([], $data['languages']);
		$this->assertArrayHasKey('license', $data);
		$this->assertArrayHasKey('locales', $data);
		$this->assertFalse($data['multilang']);
		$this->assertArrayHasKey('requirements', $data);
		$this->assertArrayHasKey('site', $data);
		$this->assertArrayHasKey('slugs', $data);
		$this->assertArrayHasKey('title', $data);
		$this->assertArrayHasKey('translation', $data);
		$this->assertUserWithAuthView($data['user']);
		$this->assertSame($this->app->version(), $data['version']);

		$this->assertArrayNotHasKey('authStatus', $data);
		$this->assertArrayNotHasKey('isInstallable', $data);
		$this->assertArrayNotHasKey('loginMethods', $data);
	}

	public function testGetPanelViewWithoutSystemAccess(): void
	{
		$this->createValidInstallation();
		$user = $this->createUser('editor');

		$this->app->impersonate($user);

		$data = $this->app->api()->call('system', 'GET')['data'];

		$this->assertValidInstallation($data);
		$this->assertUserWithAuthView($data['user']);
		$this->assertNull($data['version']);
	}

	public function testGetTroubleshootingView(): void
	{
		$this->createInvalidInstallation();

		$data = $this->app->api()->call('system', 'GET')['data'];

		$this->assertTrue($data['isInstallable']);
		$this->assertFalse($data['isInstalled']);
		$this->assertFalse($data['isOk']);
		$this->assertArrayHasKey('requirements', $data);
		$this->assertArrayHasKey('title', $data);
		$this->assertArrayHasKey('translation', $data);

		$this->assertArrayNotHasKey('ascii', $data);
		$this->assertArrayNotHasKey('authStatus', $data);
		$this->assertArrayNotHasKey('loginMethods', $data);
		$this->assertArrayNotHasKey('user', $data);
	}
}
