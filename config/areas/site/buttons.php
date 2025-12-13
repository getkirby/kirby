<?php

use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Panel\Ui\Button\LanguagesButton;
use Kirby\Panel\Ui\Button\OpenButton;
use Kirby\Panel\Ui\Button\PageStatusButton;
use Kirby\Panel\Ui\Button\PreviewButton;
use Kirby\Panel\Ui\Button\SettingsButton;
use Kirby\Panel\Ui\Button\VersionsButton;

return [
	'site.open' => function (Site $site, string $versionId = 'form') {
		$versionId = $versionId === 'compare' || $versionId === 'form' ? 'changes' : $versionId;
		$link      = $site->previewUrl($versionId);

		if ($link !== null) {
			return new OpenButton(
				link: $link,
			);
		}
	},
	'site.preview' => function (Site $site) {
		if ($site->previewUrl() !== null) {
			return new PreviewButton(
				link: $site->panel()->url(true) . '/preview/form',
			);
		}
	},
	'site.versions' => fn (Site $site, string $mode = 'form') => new VersionsButton(
		model: $site,
		mode: $mode
	),
	'page.open' => function (Page $page, string $versionId = 'form') {
		$versionId = $versionId === 'compare' || $versionId === 'form' ? 'changes' : $versionId;
		$link      = $page->previewUrl($versionId);

		if ($link !== null) {
			return new OpenButton(
				link: $link,
			);
		}
	},
	'page.preview' => function (Page $page) {
		if ($page->previewUrl() !== null) {
			return new PreviewButton(
				link: $page->panel()->url(true) . '/preview/form',
			);
		}
	},
	'page.versions' => fn (Page $page, string $mode = 'form') => new VersionsButton(
		model: $page,
		mode: $mode
	),
	'page.settings' => fn (Page $page) => new SettingsButton($page),
	'page.status'   => fn (Page $page) => new PageStatusButton($page),

	// `languages` button needs to be in site area,
	// as the  languages might be not loaded even in
	// multilang mode when the `languages` option is deactivated
	// (but content languages to switch between still can exist)
	'languages' => fn (ModelWithContent $model) => new LanguagesButton($model),

	// file buttons
	...require __DIR__ . '/../files/buttons.php'
];
