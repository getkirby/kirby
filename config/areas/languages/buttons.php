<?php

use Kirby\Cms\Language;
use Kirby\Panel\Ui\Buttons\LanguageCreateButton;
use Kirby\Panel\Ui\Buttons\LanguageDeleteButton;
use Kirby\Panel\Ui\Buttons\LanguageSettingsButton;
use Kirby\Panel\Ui\Buttons\OpenButton;

return [
	'languages.create' => fn () =>
		new LanguageCreateButton(),
	'language.open' => fn (Language $language) =>
		new OpenButton(link: $language->url()),
	'language.settings' => fn (Language $language) =>
		new LanguageSettingsButton($language),
	'language.delete' => function (Language $language) {
		if ($language->isDeletable() === true) {
			return new LanguageDeleteButton($language);
		}
	}
];
