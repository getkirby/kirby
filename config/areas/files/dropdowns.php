<?php

use Kirby\Cms\Find;
use Kirby\Panel\Ui\Buttons\LanguagesDropdown;

return [
	'file' => function (string $parent, string $filename) {
		return Find::file($parent, $filename)->panel()->dropdown();
	},
	'language' => function (string $parent, string $filename) {
		$file = Find::file($parent, $filename);
		return (new LanguagesDropdown($file))->options();
	}
];
