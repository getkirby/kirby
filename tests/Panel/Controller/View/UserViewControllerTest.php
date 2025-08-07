<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Cms\User;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Button\ViewButtons;
use Kirby\Panel\Ui\View;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ModelViewController::class)]
#[CoversClass(UserViewController::class)]
class UserViewControllerTest extends TestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Panel.Controller.View.UserViewController';

	protected User $user;

	public function setUp(): void
	{
		parent::setUp();
		$this->app = $this->app->clone([
			'users' => [
				[
					'id'    => 'test',
					'name'  => 'Test User',
					'email' => 'test@getkirby.com',
					'role'  => 'admin'
				]
			]
		]);

		$this->app->impersonate('kirby');
		$this->user = $this->app->user('test');
	}

	public function testBreadcrumb(): void
	{
		$controller = new UserViewController($this->user);
		$breadcrumb = $controller->breadcrumb();
		$this->assertSame('Test User', $breadcrumb[0]['label']);
		$this->assertSame('/users/test', $breadcrumb[0]['link']);
	}

	public function testButtons(): void
	{
		$controller = new UserViewController($this->user);
		$buttons    = $controller->buttons();
		$this->assertInstanceOf(ViewButtons::class, $buttons);
		$this->assertCount(2, $buttons->render());
	}

	public function testComponent(): void
	{
		$controller = new UserViewController($this->user);
		$this->assertSame('k-user-view', $controller->component());
	}

	public function testFactory(): void
	{
		$controller = UserViewController::factory('test');
		$this->assertInstanceOf(UserViewController::class, $controller);
		$this->assertSame($this->user, $controller->model());
	}

	public function testLoad(): void
	{
		$controller = new UserViewController($this->user);
		$view       = $controller->load();
		$this->assertInstanceOf(View::class, $view);
		$this->assertSame('k-user-view', $view->component);
		$this->assertSame('users', $view->search);

		$props = $view->props();
		$this->assertNull($props['avatar']);
		$this->assertSame('admin', $props['blueprint']);
		$this->assertArrayHasKey('canChangeEmail', $props);
		$this->assertArrayHasKey('canChangeLanguage', $props);
		$this->assertArrayHasKey('canChangeName', $props);
		$this->assertArrayHasKey('canChangeRole', $props);
		$this->assertSame('test', $props['id']);
		$this->assertSame('test@getkirby.com', $props['email']);
		$this->assertSame('English', $props['language']);
		$this->assertSame('Test User', $props['name']);
		$this->assertSame('Admin', $props['role']);
		$this->assertSame('Test User', $props['username']);

		// inherited props
		$this->assertArrayHasKey('blueprint', $props);
		$this->assertArrayHasKey('lock', $props);
		$this->assertArrayHasKey('permissions', $props);
		$this->assertArrayNotHasKey('tab', $props);
		$this->assertArrayHasKey('tabs', $props);
		$this->assertArrayHasKey('versions', $props);
	}

	public function testNext(): void
	{
		$this->app = $this->app->clone([
			'users' => [
				[
					'id'    => 'a',
					'name'  => 'User A',
					'email' => 'a@getkirby.com'
				],
				[
					'id'    => 'b',
					'name'  => 'User B',
					'email' => 'b@getkirby.com'
				]
			]
		]);

		$controller = new UserViewController($this->app->user('a'));
		$next       = $controller->next();
		$this->assertSame('User B', $next['title']);
		$this->assertSame('/users/b', $next['link']);

		$controller = new UserViewController($this->app->user('b'));
		$next       = $controller->next();
		$this->assertNull($next);

		// with tab
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'tab' => 'test'
				]
			]
		]);

		$controller = new UserViewController($this->app->user('a'));
		$next       = $controller->next();
		$this->assertSame('User B', $next['title']);
		$this->assertSame('/users/b?tab=test', $next['link']);
	}

	public function testPrev(): void
	{
		$this->app = $this->app->clone([
			'users' => [
				[
					'id'    => 'a',
					'name'  => 'User A',
					'email' => 'a@getkirby.com'
				],
				[
					'id'    => 'b',
					'name'  => 'User B',
					'email' => 'b@getkirby.com'
				]
			]
		]);

		$this->app->impersonate('kirby');

		$controller = new UserViewController($this->app->user('a'));
		$prev       = $controller->prev();
		$this->assertNull($prev);

		$controller = new UserViewController($this->app->user('b'));
		$prev       = $controller->prev();
		$this->assertSame('User A', $prev['title']);
		$this->assertSame('/users/a', $prev['link']);

		// with tab
		$this->app = $this->app->clone([
			'request' => [
				'query' => [
					'tab' => 'test'
				]
			]
		]);

		$controller = new UserViewController($this->app->user('b'));
		$prev       = $controller->prev();
		$this->assertSame('User A', $prev['title']);
		$this->assertSame('/users/a?tab=test', $prev['link']);
	}

	public function testTitle(): void
	{
		$controller = new UserViewController($this->user);
		$this->assertSame('Test User', $controller->title());
	}
}
