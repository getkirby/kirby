<?php

use Kirby\Cms\Fieldsets;

$fieldset = Fieldsets::factory()->get('quote');
$defaults = $fieldset->form($fieldset->fields())->data(true);

return [
	'docs'     => 'k-block-type-quote',
	'defaults' => $defaults,
	'fieldset' => $fieldset->toArray(),
];
