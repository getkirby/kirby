<?php

use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Panel\Ui\Buttons\LanguagesDropdown;
use Kirby\Panel\Ui\Buttons\PageStatusButton;
use Kirby\Panel\Ui\Buttons\PreviewDropdownButton;
use Kirby\Panel\Ui\Buttons\SettingsButton;

return [
	'site.preview' => function (Site $site) {
		return new PreviewDropdownButton(
			open: $site->url(),
			preview: $site->panel()->url(true) . '/preview/compare',
			copy: $site->url(),
		);
	},
	'page.preview' => function (Page $page) {
		if ($page->permissions()->can('preview') === true) {
			return new PreviewDropdownButton(
				open: $page->previewUrl(),
				preview: $page->panel()->url(true) . '/preview/compare',
				copy: $page->previewUrl(),
			);
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
