<?php

use Kirby\Toolkit\I18n;

return function ($props) {
	$section = function ($defaults, $props) {
		if ($props === true) {
			$props = [];
		}

		if (is_string($props) === true) {
			$props = [
				'label' => $props
			];
		}

		return array_replace_recursive($defaults, $props);
	};

	if (empty($props['sidebar']) === false) {
		$sidebar = $props['sidebar'];
	} else {
		$sidebar = [];

		$pages = $props['pages'] ?? [];
		$files = $props['files'] ?? [];

		if ($pages !== false) {
			$sidebar['pages'] = $section([
				'label'  => I18n::translate('pages'),
				'type'   => 'pages',
				'status' => 'all',
				'layout' => 'list',
			], $pages);
		}

		if ($files !== false) {
			$sidebar['files'] = $section([
				'label'  => I18n::translate('files'),
				'type'   => 'files',
				'layout' => 'list'
			], $files);
		}
	}

	if (empty($sidebar) === true) {
		$props['fields'] = $props['fields'] ?? [];

		unset(
			$props['files'],
			$props['pages']
		);
	} else {
		$props['columns'] = [
			[
				'width'  => '2/3',
				'fields' => $props['fields'] ?? []
			],
			[
				'width' => '1/3',
				'sections' => $sidebar
			],
		];

		unset(
			$props['fields'],
			$props['files'],
			$props['pages'],
			$props['sidebar']
		);
	}

	return $props;
};
