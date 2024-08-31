<?php

use Kirby\Cms\File;
use Kirby\Panel\Ui\Buttons\PreviewButton;
use Kirby\Panel\Ui\Buttons\SettingsButton;

return [
	'file.preview' => function (File $file) {
		return new PreviewButton(link: $file->previewUrl());
	},
	'file.settings' => function (File $file) {
		return new SettingsButton(model: $file);
	}
];
