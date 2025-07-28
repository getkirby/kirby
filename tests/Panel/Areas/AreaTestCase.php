<?php

namespace Kirby\Panel\Areas;

use Kirby\Cms\App;
use Kirby\Cms\Blueprint;
use Kirby\Cms\User;
use Kirby\Filesystem\Dir;
use Kirby\Http\Response;
use Kirby\TestCase;
use Kirby\Toolkit\Str;

abstract class AreaTestCase extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Areas.AreaTestCase';

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => static::TMP,
			],
			'request' => [
				'query' => [
					'_json' => true,
				]
			],
			'options' => [
				'api' => [
					'allowImpersonation' => true
				]
			]
		]);

		Dir::make(static::TMP);
	}

	public function tearDown(): void
	{
		// clear session file first
		$this->app->session()->destroy();

		Dir::remove(static::TMP);

		// clear blueprint cache
		Blueprint::$loaded = [];

		// clean up server software fakes
		unset($_SERVER['SERVER_SOFTWARE']);

		App::destroy();
	}

	protected function app(array $params): App
	{
		return $this->app = $this->app->clone($params);
	}

	protected function assertErrorView(string $path, string $message): void
	{
		$view = $this->view($path);
		$this->assertSame('k-error-view', $view['component']);
		$this->assertSame($message, $view['props']['error']);
	}

	protected function assertFormDialog(array $dialog): void
	{
		$this->assertSame('k-form-dialog', $dialog['component']);
	}

	protected function assertLanguageDropdown(string $path): void
	{
		$options = $this->dropdown($path)['options'];

		$this->assertSame('English', $options[0]['text']);
		$this->assertSame('-', $options[1]);
		$this->assertSame('Deutsch', $options[2]['text']);
	}

	protected function assertRedirect(
		string $source,
		string $dest = '/',
		int $code = 302
	): void {
		$response = $this->response($source);

		if ($refresh = $response->header('Refresh')) {
			preg_match('/url=(.+)/', $refresh, $matches);
			$location = $matches[1] ?? null;
		}

		$location ??= $response->header('Location');

		$this->assertInstanceOf(Response::class, $response);
		$this->assertSame($code, $response->code());
		$this->assertSame($dest, ltrim(Str::after($location, '/panel'), '/'));
	}

	protected function assertRemoveDialog(array $dialog): void
	{
		$this->assertSame('k-remove-dialog', $dialog['component']);
	}

	protected function assertTextDialog(array $dialog): void
	{
		$this->assertSame('k-text-dialog', $dialog['component']);
	}

	protected function dialog(string $path): array
	{
		return $this->response('dialogs/' . $path, true)['dialog'];
	}

	protected function dropdown(string $path): array
	{
		return $this->response('dropdowns/' . $path, true)['dropdown'];
	}

	protected function enableMultilang(): void
	{
		$this->app([
			'options' => [
				'languages' => true
			],
		]);
	}

	/**
	 * Fake a ready setup and install
	 * the first admin user
	 */
	protected function install(): void
	{
		$this->installable();
		$this->app([
			'users' => [
				[
					'id'       => 'test',
					'email'    => 'test@getkirby.com',
					'role'     => 'admin',
					'password' => User::hashPassword('12345678')
				]
			]
		]);
	}

	/**
	 * Fake a ready setup
	 */
	protected function installable(): void
	{
		// fake a valid server
		$_SERVER['SERVER_SOFTWARE'] = 'php';

		// installation has to be allowed
		$this->app([
			'options' => [
				'panel' => [
					'install' => true
				]
			]
		]);
	}

	protected function installEditor(): void
	{
		$this->app([
			'blueprints' => [
				'users/editor' => [
					'name' => 'editor',
					'title' => 'Editor',
				]
			],
			'users' => [
				[
					'id'    => 'test',
					'email' => 'test@getkirby.com',
					'role'  => 'admin',
				],
				[
					'id'    => 'editor',
					'email' => 'editor@getkirby.com',
					'role'  => 'editor',
				]
			]
		]);
	}

	protected function installLanguages(): void
	{
		$this->app([
			'languages' => [
				'en' => [
					'code'    => 'en',
					'default' => true,
					'name'    => 'English'
				],
				'de' => [
					'code'    => 'de',
					'default' => false,
					'name'    => 'Deutsch'
				]
			]
		]);
	}

	protected function login(string $user = 'test@getkirby.com'): void
	{
		$this->app->impersonate($user);
	}

	protected function response(
		string|null $path = null,
		bool $toJson = false
	): Response|array|null {
		$panel    = $this->app->panel();
		$response = $panel->router()->call($path);

		if ($toJson === true) {
			return json_decode($response->body(), true);
		}

		return $response;
	}

	protected function search(string $path): array
	{
		return $this->response('search/' . $path, true)['search'];
	}

	protected function submit(array $data): void
	{
		$this->app([
			'request' => [
				'method' => 'POST',
				'body'   => $data
			]
		]);

		// re-authenticate after cloning the app
		$this->login();
	}

	protected function view(string|null $path = null): array
	{
		return $this->response($path, true)['view'];
	}
}
