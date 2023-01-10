<?php

use Kirby\Toolkit\I18n;

return [
	'props' => [
		/**
		 * Image options to control the source and look of preview
		 */
		'image' => function ($image = null) {
			return $image ?? [];
		},
		/**
		 * Optional info text setup. Info text is shown on the right (lists, cardlets) or below (cards) the title.
		 */
		'info' => function ($info = null) {
			return I18n::translate($info, $info);
		},
		/**
		 * Setup for the main text in the list or cards. By default this will display the title.
		 */
		'text' => function ($text = '{{ model.title }}') {
			return I18n::translate($text, $text);
		}
	],
	'methods' => [
		'link' => function () {
			$modelLink  = $this->model->panel()->url(true);
			$parentLink = $this->parent->panel()->url(true);

			if ($modelLink !== $parentLink) {
				return $parentLink;
			}
		}
	]
];
