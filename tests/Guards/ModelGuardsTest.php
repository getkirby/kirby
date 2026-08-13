<?php

namespace Kirby\Guards;

use Kirby\Cms\ModelTestCase;
use Kirby\Cms\Page;
use Kirby\Cms\User;
use Kirby\Exception\AbilityException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\PermissionException;
use PHPUnit\Framework\Attributes\CoversClass;

class ModelGuardsTestValidators extends PageValidators
{
	public function validateTitle(string $title): void
	{
		$this->error(key: 'page.changeTitle.custom');
	}
}

class ModelGuardsTestGuards extends PageGuards
{
	public function __construct(
		Page $model,
		User $user
	) {
		parent::__construct(
			model: $model,
			user: $user
		);

		$this->validators = new ModelGuardsTestValidators(
			model: $model,
			user: $user
		);
	}
}

#[CoversClass(ModelGuards::class)]
class ModelGuardsTest extends ModelTestCase
{
	public const TMP = KIRBY_TMP_DIR . '/Guards.ModelGuards';

	protected function guards(Page|null $page = null): PageGuards
	{
		return new PageGuards(
			model: $page ?? new Page(['slug' => 'test']),
			user: $this->user()
		);
	}

	protected function user(): User
	{
		return $this->app->user() ?? new User(['id' => 'test']);
	}

	public function testAbilities(): void
	{
		$this->assertInstanceOf(PageAbilities::class, $this->guards()->abilities());
	}

	public function testEnsureAvailable(): void
	{
		$this->app->impersonate('kirby');

		$this->assertNull($this->guards()->ensureAvailable('update'));
	}

	public function testEnsureAvailableWithFailingAbility(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [['slug' => 'home']]
			]
		]);

		$this->app->impersonate('kirby');

		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.page.delete.homePage');

		$this->guards($this->app->page('home'))->ensureAvailable('delete');
	}

	public function testEnsureAvailableWithFailingPermission(): void
	{
		$this->app->impersonate('nobody');

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.page.update.permission');

		$this->guards()->ensureAvailable('update');
	}

	public function testEnsureAvailableWithUndefinedAction(): void
	{
		$this->app = $this->app->clone([
			'roles' => [['name' => 'editor']],
			'users' => [['email' => 'editor@getkirby.com', 'role' => 'editor']]
		]);

		$this->app->impersonate('editor@getkirby.com');

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.page.does-not-exist.permission');

		$this->guards()->ensureAvailable('does-not-exist');
	}

	public function testEnsureAvailableWithUndefinedActionAndAllowingDefault(): void
	{
		$this->app = $this->app->clone([
			'roles' => [['name' => 'editor']],
			'users' => [['email' => 'editor@getkirby.com', 'role' => 'editor']]
		]);

		$this->app->impersonate('editor@getkirby.com');

		$this->assertNull($this->guards()->ensureAvailable('does-not-exist', default: true));
	}

	public function testEnsureExecutable(): void
	{
		$this->app->impersonate('kirby');

		$this->assertNull($this->guards()->ensureExecutable('changeTitle', 'New title'));
	}

	public function testEnsureExecutableWithFailingAbility(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [['slug' => 'home']]
			]
		]);

		$this->app->impersonate('kirby');

		$this->expectException(AbilityException::class);
		$this->expectExceptionCode('error.page.delete.homePage');

		$this->guards($this->app->page('home'))->ensureExecutable('delete');
	}

	public function testEnsureExecutableWithFailingPermission(): void
	{
		$this->app->impersonate('nobody');

		$this->expectException(PermissionException::class);
		$this->expectExceptionCode('error.page.update.permission');

		$this->guards()->ensureExecutable('update');
	}

	public function testEnsureExecutableWithFailingValidator(): void
	{
		$this->app->impersonate('kirby');

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.changeTitle.empty');

		$this->guards()->ensureExecutable('changeTitle', '');
	}

	public function testEnsureExecutableWithReplacedValidators(): void
	{
		$this->app->impersonate('kirby');

		// the validators are fetched on demand, so a replaced
		// validators object is taken into account
		$guards = new ModelGuardsTestGuards(
			model: new Page(['slug' => 'test']),
			user: $this->user()
		);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionCode('error.page.changeTitle.custom');

		$guards->ensureExecutable('changeTitle', 'New title');
	}

	public function testEnsureExecutableWithUndefinedAction(): void
	{
		$this->app->impersonate('kirby');

		// actions that no layer knows about have no checks to run
		$this->assertNull($this->guards()->ensureExecutable('does-not-exist'));
		$this->assertTrue($this->guards()->isExecutable('does-not-exist'));
	}

	public function testEnsureExecutableWithUndefinedActionAndRestrictedRole(): void
	{
		$this->app = $this->app->clone([
			'roles' => [['name' => 'editor', 'permissions' => false]],
			'users' => [['email' => 'editor@getkirby.com', 'role' => 'editor']]
		]);

		$this->app->impersonate('editor@getkirby.com');

		// undefined actions are denied, just like every
		// other action the role is not allowed to do
		$this->assertFalse($this->guards()->isExecutable('does-not-exist'));
		$this->assertFalse($this->guards()->isExecutable('update'));
	}

	public function testIsAvailable(): void
	{
		$this->app->impersonate('kirby');

		$this->assertTrue($this->guards()->isAvailable('update'));
	}

	public function testIsAvailableWithActionMethod(): void
	{
		$this->app->impersonate('kirby');

		// the dedicated action method is skipped on purpose,
		// as its arguments are not available here
		$this->assertTrue($this->guards()->isAvailable('changeTitle'));
	}

	public function testIsAvailableWithFailingAbility(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [['slug' => 'home']]
			]
		]);

		$this->app->impersonate('kirby');

		$this->assertFalse($this->guards($this->app->page('home'))->isAvailable('delete'));
	}

	public function testIsAvailableWithFailingPermission(): void
	{
		$this->app->impersonate('nobody');

		$this->assertFalse($this->guards()->isAvailable('update'));
	}

	public function testIsAvailableWithUndefinedAction(): void
	{
		$this->app = $this->app->clone([
			'roles' => [['name' => 'editor']],
			'users' => [['email' => 'editor@getkirby.com', 'role' => 'editor']]
		]);

		$this->app->impersonate('editor@getkirby.com');

		$guards = $this->guards();

		$this->assertFalse($guards->isAvailable('does-not-exist'));
		$this->assertTrue($guards->isAvailable('does-not-exist', default: true));
	}

	public function testIsExecutable(): void
	{
		$this->app->impersonate('kirby');

		$this->assertTrue($this->guards()->isExecutable('changeTitle', 'New title'));
	}

	public function testIsExecutableWithFailingAbility(): void
	{
		$this->app = $this->app->clone([
			'site' => [
				'children' => [['slug' => 'home']]
			]
		]);

		$this->app->impersonate('kirby');

		$this->assertFalse($this->guards($this->app->page('home'))->isExecutable('delete'));
	}

	public function testIsExecutableWithFailingPermission(): void
	{
		$this->app->impersonate('nobody');

		$this->assertFalse($this->guards()->isExecutable('update'));
	}

	public function testIsExecutableWithFailingValidator(): void
	{
		$this->app->impersonate('kirby');

		$this->assertFalse($this->guards()->isExecutable('changeTitle', ''));
	}

	public function testPermissions(): void
	{
		$this->assertInstanceOf(PagePermissions::class, $this->guards()->permissions());
	}

	public function testUser(): void
	{
		$this->app->impersonate('kirby');

		$this->assertSame($this->app->user(), $this->guards()->user());
	}

	public function testValidators(): void
	{
		$this->assertInstanceOf(PageValidators::class, $this->guards()->validators());
	}
}
