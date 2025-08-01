<?php

use Kirby\Cms\Language;
use Kirby\Panel\Ui\Button\LanguageCreateButton;
use Kirby\Panel\Ui\Button\LanguageDeleteButton;
use Kirby\Panel\Ui\Button\LanguageSettingsButton;
use Kirby\Panel\Ui\Button\OpenButton;

return [
	'languages.create' => fn () =>
		new LanguageCreateButton(),
	'language.open' => fn (Language $language) =>
		new OpenButton($language->url()),
	'language.settings' => fn (Language $language) =>
		new LanguageSettingsButton($language),
	'language.delete' => function (Language $language) {
		if ($language->isDeletable() === true) {
			return new LanguageDeleteButton($language);
		}
	}
];
