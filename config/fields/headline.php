<?php

return [
	'save' => false,
	'props' => [
		/**
		 * Unset inherited props
		 */
		'after'       => null,
		'autofocus'   => null,
		'before'      => null,
		'default'     => null,
		'disabled'    => null,
		'icon'        => null,
		'placeholder' => null,
		'required'    => null,
		'translate'   => null,

		/**
		 * If `false`, the prepended number will be hidden
		 */
		'numbered' => function (bool $numbered = true) {
			return $numbered;
		}
	]
];
