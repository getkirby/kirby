<?php

use Kirby\Toolkit\I18n;

return [
	'props' => [
		/**
		 * The headline for the section. This can be a simple string or a template with additional info from the parent page.
		 * @deprecated 3.8.0 Use `label` instead
		 */
		'headline' => function ($headline = null) {
			return I18n::translate($headline, $headline);
		},
		/**
		 * The label for the section. This can be a simple string or
		 * a template with additional info from the parent page.
		 * Replaces the `headline` prop.
		 */
		'label' => function ($label = null) {
			return I18n::translate($label, $label);
		}
	],
	'computed' => [
		'headline' => function () {
			if ($this->label) {
				return $this->model()->toString($this->label);
			}

			if ($this->headline) {
				return $this->model()->toString($this->headline);
			}

			return ucfirst($this->name);
		}
	]
];
