<?php

use Kirby\Cms\User;
use Kirby\Panel\Ui\Buttons\SettingsButton;
use Kirby\Panel\Ui\Buttons\ViewButton;
use Kirby\Toolkit\I18n;

return [
	'users.create' => function (User $user, string|null $role = null) {
		return new ViewButton(
			dialog: 'users/create?role=' . $role,
			disabled: $user->kirby()->roles()->canBeCreated()->count() < 1,
			icon: 'add',
			text: I18n::translate('user.create'),
		);
	},
	'user.settings' => function (User $user) {
		return new SettingsButton(model: $user);
	}
];
