<?php

namespace Kirby\Panel\Form\Field;

use Kirby\Cms\App;
use Kirby\Cms\Roles;
use Kirby\Form\Field\RadioField;
use Kirby\Reflection\Attributes\Prop;

/**
 * Panel field override for selecting a user role
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class RoleField extends RadioField
{
	#[Prop(computed: true)]
	protected string|null $name = 'role';

	public function __construct(
		protected Roles|null $roles = null,
		mixed ...$args
	) {
		parent::__construct(...$args);
	}

	public function label(): string
	{
		if ($this->label === null || $this->label === []) {
			return $this->i18n('role');
		}

		return parent::label();
	}

	public function options(): array
	{
		// turn roles into radio field options
		return $this->roles()->values(fn ($role) => [
			'text'  => $role->title(),
			'info'  => $role->description() ?? $this->i18n('role.description.placeholder'),
			'value' => $role->name()
		]);
	}

	public function roles(): Roles
	{
		return $this->roles ?? App::instance()->roles();
	}

	public function type(): string
	{
		return 'radio';
	}
}
