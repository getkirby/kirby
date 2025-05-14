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
		if ($site->previewUrl() !== null) {
			return new PreviewButton(
				link: $site->panel()->url(true) . '/preview/changes',
				target: null
			);
		}
	},
	'page.preview' => function (Page $page) {
		if ($page->previewUrl() !== null) {
			return new PreviewButton(
				link: $page->panel()->url(true) . '/preview/changes',
				target: null
			);
		}
	},
	'page.settings' => fn (Page $page) => new SettingsButton(model: $page),
	'page.status'   => fn (Page $page) => new PageStatusButton($page),

	// `languages` button needs to be in site area,
	// as the  languages might be not loaded even in
	// multilang mode when the `languages` option is deactivated
	// (but content languages to switch between still can exist)
	'languages' => fn (ModelWithContent $model) =>
		new LanguagesDropdown($model),

	// file buttons
	...require __DIR__ . '/../files/buttons.php'
];
