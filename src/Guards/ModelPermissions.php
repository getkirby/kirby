<?php

namespace Kirby\Guards;

use Kirby\Cms\Model;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Role;
use Kirby\Cms\User;
use Kirby\Exception\PermissionException;

/**
 * Role and blueprint based permissions for a model object.
 * This is the single source of truth for all permission
 * rules. `\Kirby\Cms\ModelPermissions` is a boolean facade
 * on top of it.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class ModelPermissions
{
	use HasActions;

	/**
	 * Cache for the two user checks that `::setting()` runs for
	 * every action. The guards are bound to one user, so both are
	 * constant for the lifetime of the instance.
	 */
	protected bool|null $isKirby = null;
	protected bool|null $isNobody = null;

	public function __construct(
		protected Model $model,
		protected User $user
	) {
	}

	/**
	 * Corresponds with the permissions category in
	 * the Kirby\Cms\Permissions class. E.g. `pages`, `files`, etc.
	 */
	abstract public function category(): string;

	/**
	 * Runs the permission check for the given action.
	 * Actions with a dedicated `ensureTo<Action>()` method are
	 * handed over to it, all others are resolved against
	 * the permission rules.
	 *
	 * `Kirby\Cms\Permissions` merges every role against its
	 * defaults, so a registered action always resolves to a
	 * bool. The default is therefore only ever used for an
	 * action that has no rule at all, e.g. one that a plugin
	 * added without registering a permission for it. Such an
	 * action is denied, unless the caller opts into allowing
	 * it. The `nobody` role and the `kirby` user still
	 * overrule the default.
	 *
	 * @param bool $default Used if the action has no permission rule
	 * @throws PermissionException
	 */
	public function ensure(string $action, bool $default = false): void
	{
		if ($this->has($action) === true) {
			$this->{$this->method($action)}();

			return;
		}

		$this->ensureSetting($action, $default);
	}

	/**
	 * Throws unless the action is explicitly allowed
	 * by the model blueprint or the role of the user
	 *
	 * @param bool $default Used if no rule is defined for the action
	 * @throws PermissionException
	 */
	protected function ensureSetting(string $action, bool $default = false): void
	{
		if ($this->setting($action, $default) !== true) {
			$this->error($action);
		}
	}

	/**
	 * @throws PermissionException
	 */
	public function error(string $key, array $data = []): never
	{
		throw new PermissionException(
			key: $key,
			data: $data
		);
	}

	/**
	 * Non-throwing counterpart of `::ensure()`
	 *
	 * @param bool $default Used if no rule is defined for the action
	 */
	public function may(string $action, bool $default = false): bool
	{
		try {
			$this->ensure($action, $default);

			return true;
		} catch (PermissionException) {
			return false;
		}
	}

	/**
	 * Resolves the permission rule for the current user.
	 * Returns null if no rule is defined for the action.
	 */
	public function setting(string $action, bool|null $default = null): bool|null
	{
		// users with the `nobody` role can't execute anything
		// that needs a permission check. This must be checked
		// against the role and not against `User::isNobody()`,
		// which only matches the virtual, logged out user.
		$this->isNobody ??= $this->user->role()->isNobody();

		if ($this->isNobody === true) {
			return false;
		}

		// the almighty `kirby` user can execute anything
		$this->isKirby ??= $this->user->isKirby();

		if ($this->isKirby === true) {
			return true;
		}

		return
			$this->settingForUser($this->user, $action) ??
			$this->settingForRole($this->user->role(), $action) ?? $default;
	}

	/**
	 * Tries to find the permission rule by role and action.
	 * Returns null if no specific rule is set in the role blueprint.
	 */
	public function settingForRole(Role $role, string $action): bool|null
	{
		return $role->permissions()->for(
			category: $this->category(),
			action: $action,
			default: null
		);
	}

	/**
	 * Tries to find the permission rule by user, model and action.
	 * Returns null if no specific rule is set in the model blueprint.
	 */
	public function settingForUser(User $user, string $action): bool|null
	{
		// only models with a blueprint can define
		// permission rules for individual users
		if ($this->model instanceof ModelWithContent === true) {
			return $this->model->blueprint()->optionForUser($user, $action);
		}

		return null;
	}
}
