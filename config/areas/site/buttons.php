<?php

use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Panel\Ui\Buttons\LanguagesDropdown;
use Kirby\Panel\Ui\Buttons\PageStatusButton;
use Kirby\Panel\Ui\Buttons\PreviewButton;
use Kirby\Panel\Ui\Buttons\SettingsButton;

return [
	'site.preview' => function (Site $site) {
		return new PreviewButton(link: $site->url());
	},
	'page.preview' => function (Page $page) {
		if ($page->permissions()->can('preview') === true) {
			return new PreviewButton(link: $page->previewUrl());
		}
	},
	'page.settings' => function (Page $page) {
		return new SettingsButton(model: $page);
	},
	'page.status' => function (Page $page) {
		return new PageStatusButton($page);
	},
	// `languages` button needs to be in site area,
	// as the  languages might be not loaded even in
	// multilang mode when the `languages` option is deactivated
	// (but content languages to switch between still can exist)
	'languages' => function (ModelWithContent $model) {
		return new LanguagesDropdown($model);
	},

	// file buttons
	...require __DIR__ . '/../files/buttons.php'
];
