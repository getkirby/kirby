<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Cms\App;
use Kirby\Cms\Role;
use Kirby\Cms\User;
use Kirby\Panel\Collector\UsersCollector;
use Kirby\Panel\Controller\ViewController;
use Kirby\Panel\Ui\Button\ViewButtons;
use Kirby\Panel\Ui\Item\UserItem;
use Kirby\Panel\Ui\View;

/**
 * Controls the users view
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class UsersViewController extends ViewController
{
	protected array $roles;

	public function __construct(
		public string|null $role = null
	) {
		parent::__construct();
	}

	public function buttons(): ViewButtons
	{
		return ViewButtons::view('users')
			->defaults('create')
			->bind(['role' => $this->role]);
	}

	public static function factory(): static
	{
		return new static(role: App::instance()->request()->get('role'));
	}

	public function load(): View
	{
		return new View(
			component: 'k-users-view',
			buttons:   $this->buttons(),
			role:      $this->role(),
			roles:     array_values($this->roles()),
			users:     $this->users(),
		);
	}

	public function role(): array|null
	{
		if ($role = $this->role) {
			return $this->roles()[$role] ?? null;
		}

		return null;
	}

	public function roles(): array
	{
		return $this->roles ??= $this->kirby->roles()->toArray(fn (Role $role) => [
			'id'    => $role->id(),
			'title' => $role->title(),
		]);
	}

	public function users(): array
	{
		$collector = new UsersCollector(
			limit: 20,
			page: $this->kirby->request()->get('page', 1),
			role: $this->role,
			sortBy: 'username asc',
		);

		$users = $collector->models(paginated: true);

		return [
			'pagination' => $users->pagination()->toArray(),
			'data'       => $users->values(
				fn (User $user) => (new UserItem($user))->props()
			),
		];
	}
}
