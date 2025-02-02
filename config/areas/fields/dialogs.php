<?php

use Kirby\Panel\Ui\Dialogs\FieldDialog;

return [
	'model' => [
		'handler' => FieldDialog::forModel(...)
	],
	'file' => [
		'handler' => FieldDialog::forFile(...)
	],
];
