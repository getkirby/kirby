<?php

use Kirby\Cms\File;
use Kirby\Panel\Ui\Button\OpenButton;
use Kirby\Panel\Ui\Button\SettingsButton;

return [
	'file.open'     => fn (File $file) => new OpenButton($file->previewUrl()),
	'file.settings' => fn (File $file) => new SettingsButton($file)
];
