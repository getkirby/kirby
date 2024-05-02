<?php

use Kirby\Toolkit\I18n;

return function (array $props) {
	// load the general templates setting for all sections
	$templates = $props['templates'] ?? null;

	$section = function ($label, $status, $props) use ($templates) {
		$defaults = [
			'label'  => $label,
			'type'   => 'pages',
			'layout' => 'list',
			'status' => $status
		];

		if ($props === true) {
			$props = [];
		}

		if (is_string($props) === true) {
			$props = [
				'label' => $props
			];
		}

		// inject the global templates definition
		if (empty($templates) === false) {
			$props['templates'] ??= $templates;
		}

		return array_replace_recursive($defaults, $props);
	};

	$sections = [];

	$drafts   = $props['drafts']   ?? [];
	$unlisted = $props['unlisted'] ?? false;
	$listed   = $props['listed']   ?? [];


	if ($drafts !== false) {
		$sections['drafts'] = $section(I18n::translate('pages.status.draft'), 'drafts', $drafts);
	}

	if ($unlisted !== false) {
		$sections['unlisted'] = $section(I18n::translate('pages.status.unlisted'), 'unlisted', $unlisted);
	}

	if ($listed !== false) {
		$sections['listed'] = $section(I18n::translate('pages.status.listed'), 'listed', $listed);
	}

	// cleaning up
	unset($props['drafts'], $props['unlisted'], $props['listed'], $props['templates']);

	return array_merge($props, ['sections' => $sections]);
};
