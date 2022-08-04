<?php

use Kirby\Toolkit\I18n;

return function (array $props) {
	$props['sections'] = [
		'files' => [
			// TODO: Remove `headline` check in 3.9.0
			'label'    => $props['headline'] ?? $props['label'] ?? I18n::translate('files'),
			'type'     => 'files',
			'layout'   => $props['layout'] ?? 'cards',
			'template' => $props['template'] ?? null,
			'image'    => $props['image'] ?? null,
			'info'     => '{{ file.dimensions }}'
		]
	];

	// remove global options
	unset(
		// TODO: Remove in 3.9.0
		$props['headline'],
		$props['label'],
		$props['layout'],
		$props['template'],
		$props['image']
	);

	return $props;
};
