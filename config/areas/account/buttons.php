<?php

use Kirby\Cms\App;
use Kirby\Cms\User;
use Kirby\Panel\Ui\Buttons\ViewButton;

return [
	'user.theme' => function (App $kirby, User $user) {
		if ($kirby->user()->is($user) === true) {
			return new ViewButton(component: 'k-theme-view-button');
		}
	}
];
