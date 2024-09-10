<?php

use Kirby\Cms\Language;
use Kirby\Panel\Ui\Buttons\LanguageAddButton;
use Kirby\Panel\Ui\Buttons\LanguageDeleteButton;
use Kirby\Panel\Ui\Buttons\LanguageSettingsButton;
use Kirby\Panel\Ui\Buttons\PreviewButton;

return [
	'languages.add' => fn () =>
		new LanguageAddButton(),
	'language.preview' => fn (Language $language) =>
		new PreviewButton(link: $language->url()),
	'language.settings' => fn (Language $language) =>
		new LanguageSettingsButton($language),
	'language.delete' => function (Language $language) {
		if ($language->isDeletable() === true) {
			return new LanguageDeleteButton($language);
		}
	}
];
