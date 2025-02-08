<?php

use Kirby\Cms\File;
use Kirby\Panel\Ui\Buttons\PreviewButton;
use Kirby\Panel\Ui\Buttons\SettingsButton;

return [
	'file.preview' =>
		fn (File $file) => new PreviewButton(link: $file->previewUrl()),
	'file.settings' =>
		fn (File $file) => new SettingsButton(model: $file)
];
