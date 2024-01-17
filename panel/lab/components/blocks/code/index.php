<?php

use Kirby\Cms\Fieldsets;

$fieldset = Fieldsets::factory()->get('code');
$defaults = $fieldset->form($fieldset->fields())->data(true);

return [
	'docs'     => 'k-block-type-code',
	'defaults' => $defaults,
	'fieldset' => $fieldset->toArray(),
];
