<?php

use Kirby\Cms\Fieldsets;

$fieldset = Fieldsets::factory()->get('text');
$defaults = $fieldset->form($fieldset->fields())->fill(defaults: true)->toFormValues();

return [
	'docs'     => 'k-block-type-text',
	'defaults' => $defaults,
	'fieldset' => $fieldset->toArray(),
];
