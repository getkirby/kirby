<?php

use Kirby\Cms\App;
use Kirby\Cms\Translation;

return [
	'source' => 'panel/src/libraries/dayjs-locale.ts',
	'codes'  => [
		...App::instance()->translations()->values(
			fn (Translation $translation) => [
				'text'  => $translation->name() . ' [' . $translation->code() . ']',
				'value' => $translation->code()
			]
		),
		[
			'text'  => 'Unknown [xx]',
			'value' => 'xx'
		]
	]
];
