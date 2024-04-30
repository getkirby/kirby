<?php

use Kirby\Cms\File;
use Kirby\Toolkit\I18n;

return [
	function (File $file) {
		if ($file->type() === 'image') {
			return [
				'props' => [
					'focusable' => $file->panel()->isFocusable(),
					'details'   => [
						[
							'title' => I18n::translate('dimensions'),
							'text'  => $file->dimensions() . ' ' . I18n::translate('pixel')
						],
						[
							'title' => I18n::translate('orientation'),
							'text'  => I18n::translate('orientation.' . $file->dimensions()->orientation())
						]
					],
				]
			];
		}
	}
];
