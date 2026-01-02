<?php

namespace Kirby\Panel\Form\Field;

use Kirby\Cms\App;
use Kirby\Cms\Roles;
use Kirby\Form\Field\RadioField;

class RoleField extends RadioField
{
	public function __construct(
		protected Roles|null $roles = null,
		...$props
	) {
		parent::__construct(...$props);
	}

	public function label(): string
	{
		if ($this->label === null || $this->label === []) {
			return $this->i18n('role');
		}

		return parent::label();
	}

	public function name(): string
	{
		return $this->name ?? 'role';
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
