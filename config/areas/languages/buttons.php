<?php

use Kirby\Cms\Language;
use Kirby\Panel\Ui\Buttons\PreviewButton;
use Kirby\Panel\Ui\Buttons\ViewButton;
use Kirby\Toolkit\I18n;

return [
	'languages.add' => function () {
		return new ViewButton(
			dialog: 'languages/create',
			icon: 'add',
			text: I18n::translate('language.create'),
		);
	},
	'language.preview' => function (Language $language) {
		return new PreviewButton(link: $language->url());
	},
	'language.settings' => function (Language $language) {
		return new ViewButton(
			dialog: 'languages/' . $language->id() . '/update',
			icon: 'cog',
			title: I18n::translate('settings'),
		);
	},
	'language.remove' => function (Language $language) {
		if ($language->isDeletable() === true) {
			return new ViewButton(
				dialog: 'languages/' . $language->id() . '/delete',
				icon: 'trash',
				title: I18n::translate('delete'),
			);
		}
	}
];
