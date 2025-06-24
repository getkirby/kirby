<?php

use Kirby\Cms\File;
use Kirby\Panel\Ui\Buttons\OpenButton;
use Kirby\Panel\Ui\Buttons\SettingsButton;

return [
	'file.open' => function (File $file) {
		return new OpenButton(link: $file->previewUrl());
	},
	'file.settings' => function (File $file) {
		return new SettingsButton(model: $file);
	}
];
