<?php

use Kirby\Panel\Ui\Drawers\FieldDrawer;

return [
	'model' => [
		'handler' => FieldDrawer::forModel(...)
	],
	'file' => [
		'handler' => FieldDrawer::forFile(...)
	],
];
