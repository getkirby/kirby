<?php

use Kirby\Toolkit\I18n;

return [
	'mixins' => [
		'headline',
	],
	'props' => [
		'text' => function ($text = null) {
			return I18n::translate($text, $text);
		},
		'theme' => function (string $theme = null) {
			return $theme;
		}
	],
	'computed' => [
		'text' => function () {
			if ($this->text) {
				$text = $this->model()->toSafeString($this->text);
				$text = $this->kirby()->kirbytext($text);
				return $text;
			}
		},
	],
	'toArray' => function () {
		return [
			'label' => $this->headline,
			'text'  => $this->text,
			'theme' => $this->theme
		];
	}
];
