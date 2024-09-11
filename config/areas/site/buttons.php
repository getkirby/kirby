<?php

use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Panel\Ui\Buttons\LanguagesButton;
use Kirby\Panel\Ui\Buttons\PageStatusButton;
use Kirby\Panel\Ui\Buttons\PreviewButton;
use Kirby\Panel\Ui\Buttons\SettingsButton;

return [
	'site.preview' => function (Site $site) {
		return new PreviewButton(link: $site->url());
	},
	'page.preview' => function (Page $page) {
		if ($page->permissions()->can('preview') === true) {
			$button = new PreviewButton(link: $page->previewUrl());

			if ($page->version('changes')->exists() === true) {
				$button->link = null;
				$button->options = [
					[
						'icon'   => 'preview',
						'text'   => I18n::translate('form.preview'),
						'link'   => $page->url() . '?_version=changes',
						'target' => '_blank'
					],
					'-',
					[
						'icon'   => 'open',
						'text'   => 'Open current public version',
						'link'   => $page->url(),
						'target' => '_blank'
					],
				];
			}

			return $button;
		}
	},
	'page.settings' => function (Page $page) {
		return new SettingsButton(model: $page);
	},
	'page.status' => function (Page $page) {
		return new PageStatusButton($page);
	},
	// `languages` button needs to be in site area, as languages area itself
	// is only loaded when in multilang setup
	'languages' => function () {
		return new LanguagesButton();
	},

	// file buttons
	...require __DIR__ . '/../files/buttons.php'
];
