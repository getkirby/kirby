<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Auth\Challenge;
use Kirby\Auth\Challenges;
use Kirby\Auth\Pending;
use Kirby\Cms\User;
use Kirby\Panel\Redirect;
use Kirby\Panel\TestCase;
use Kirby\Panel\Ui\Component;
use Kirby\Panel\Ui\View;
use PHPUnit\Framework\Attributes\CoversClass;

class TestChallenge extends Challenge
{
	public static bool $available = true;

	public static function icon(): string
	{
		return 'test-icon';
	}

	public static function isAvailable(User $user, string $mode): bool
	{
		return static::$available;
	}

	public function create(): Pending|null
	{
		return null;
	}

	public function form(Pending $pending): Component
	{
		return new Component(
			component: 'k-login-test-challenge-form',
			submit:    $this->submit(),
			user:      $this->user->email(),
		);
	}

	public function verify(mixed $input, Pending $data): bool
	{
		return true;
	}
}

class TestChallenge2 extends Challenge
{
	public static function isAvailable(User $user, string $mode): bool
	{
		return true;
	}

	public function create(): Pending|null
	{
		return null;
	}

	public function form(Pending $pending): Component
	{
		return new Component(
			component: 'k-login-test-challenge2-form',
			submit:    $this->submit(),
			user:      $this->user->email(),
		);
	}

	public function verify(mixed $input, Pending $data): bool
	{
		return true;
	}
}

#[CoversClass(LoginViewController::class)]
class LoginViewControllerTest extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();

		TestChallenge::$available   = true;
		Challenges::$challenges['test']  = TestChallenge::class;
		Challenges::$challenges['test2'] = TestChallenge2::class;
	}

	public function tearDown(): void
	{
		parent::tearDown();

		unset(Challenges::$challenges['test'], Challenges::$challenges['test2']);

	}

	public function testConstructUnknownChallenge(): void
	{
		$this->expectException(Redirect::class);
		new LoginViewController('challenge', 'unknown');
	}

	public function testConstructUnknownMethod(): void
	{
		$this->expectException(Redirect::class);
		new LoginViewController('method', 'unknown');
	}

	public function testConstructUnknownType(): void
	{
		$this->expectException(Redirect::class);
		new LoginViewController('unknown-type');
	}

	public function testConstructWithoutPendingState(): void
	{
		$this->expectException(Redirect::class);
		new LoginViewController('challenge', 'email');
	}

	public function testConstructUnavailableChallengeRedirects(): void
	{
		// test2 is registered but not in auth.challenges, so the switch fails
		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'challenges' => ['test']
				]
			],
			'users' => [
				[
					'email' => 'test@example.com',
					'role'  => 'admin'
				]
			]
		]);

		$this->app->session()->set('kirby.challenge.email', 'test@example.com');
		$this->app->session()->set('kirby.challenge.type', 'test');
		$this->app->session()->set('kirby.challenge.mode', 'login');

		$this->expectException(Redirect::class);
		new LoginViewController('challenge', 'test2');
	}

	public function testConstructSwitchesChallenge(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'challenges' => ['test', 'test2']
				]
			],
			'users' => [
				[
					'email' => 'test@example.com',
					'role'  => 'admin'
				]
			]
		]);

		$session = $this->app->session();
		$session->set('kirby.challenge.email', 'test@example.com');
		$session->set('kirby.challenge.type', 'test');
		$session->set('kirby.challenge.mode', 'login');

		$controller = new LoginViewController('challenge', 'test2');
		$props      = $controller->load()->props();

		$this->assertSame('test2', $session->get('kirby.challenge.type'));
		$this->assertSame('k-login-test-challenge2-form', $props['form']['component']);
	}

	public function testChallenges(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'challenges' => ['test', 'test2']
				]
			],
			'users' => [
				[
					'email' => 'test@example.com',
					'role'  => 'admin'
				]
			]
		]);

		$this->app->session()->set('kirby.challenge.email', 'test@example.com');
		$this->app->session()->set('kirby.challenge.type', 'test');
		$this->app->session()->set('kirby.challenge.mode', 'login');

		$challenges = (new LoginViewController('challenge', 'test'))->load()->props()['challenges'];

		$this->assertCount(2, $challenges);

		$this->assertSame('test', $challenges[0]['type']);
		$this->assertSame('test-icon', $challenges[0]['icon']);
		$this->assertArrayHasKey('label', $challenges[0]);
		$this->assertTrue($challenges[0]['active']);

		$this->assertSame('test2', $challenges[1]['type']);
		$this->assertArrayHasKey('label', $challenges[1]);
		$this->assertFalse($challenges[1]['active']);
	}

	public function testChallengesEmptyWhenNotPending(): void
	{
		$challenges = (new LoginViewController())->load()->props()['challenges'];
		$this->assertSame([], $challenges);
	}

	public function testLoad(): void
	{
		$controller = new LoginViewController();
		$view       = $controller->load();
		$this->assertInstanceOf(View::class, $view);
		$this->assertSame('k-login-view', $view->component);

		$props = $view->props();
		$this->assertSame('inactive', $props['state']);
		$this->assertSame('k-login-password-method-form', $props['form']['component']);
		$this->assertCount(1, $props['methods']);
		$this->assertSame('password', $props['methods'][0]['type']);
		$this->assertTrue($props['methods'][0]['active']);
		$this->assertSame([], $props['challenges']);
	}

	public function testLoadWithMethod(): void
	{
		$this->app = $this->app->clone([
			'options' => ['auth.methods' => ['password', 'code']]
		]);

		$controller = new LoginViewController('method', 'code');
		$props      = $controller->load()->props();

		$this->assertSame('k-login-code-method-form', $props['form']['component']);
		$this->assertCount(2, $props['methods']);
		$this->assertTrue($props['methods'][1]['active']);
	}

	public function testLoadWithChallenge(): void
	{
		$this->app = $this->app->clone([
			'options' => [
				'auth' => [
					'challenges' => ['test', 'test2']
				]
			],
			'users' => [
				[
					'email' => 'test@example.com',
					'role'  => 'admin'
				]
			]
		]);

		$this->app->session()->set('kirby.challenge.email', 'test@example.com');
		$this->app->session()->set('kirby.challenge.type', 'test');
		$this->app->session()->set('kirby.challenge.mode', 'login');

		$controller = new LoginViewController('challenge', 'test');
		$props      = $controller->load()->props();

		$this->assertSame('pending', $props['state']);
		$this->assertSame('k-login-test-challenge-form', $props['form']['component']);
		// when current is a Challenge, the first enabled method is shown as active
		$this->assertTrue($props['methods'][0]['active']);
		$this->assertCount(2, $props['challenges']);
		$this->assertTrue($props['challenges'][0]['active']);
		$this->assertFalse($props['challenges'][1]['active']);
	}

	public function testMethodRouteRedirectsWhenPending(): void
	{
		$this->app->session()->set('kirby.challenge.email', 'test@example.com');
		$this->app->session()->set('kirby.challenge.type', 'email');
		$this->app->session()->set('kirby.challenge.mode', 'login');

		// visiting a method route while a challenge is pending redirects
		// to the active challenge route to keep URL and state in sync
		$this->expectException(Redirect::class);
		new LoginViewController('method', 'password');
	}

	public function testMethods(): void
	{
		$controller = new LoginViewController();
		$method     = $controller->load()->props()['methods'][0];

		$this->assertSame('password', $method['type']);
		$this->assertSame('key', $method['icon']);
		$this->assertArrayHasKey('label', $method);
		$this->assertArrayHasKey('active', $method);
	}
}
