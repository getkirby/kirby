<?php

use Kirby\Cms\Fieldsets;

$fieldset = Fieldsets::factory()->get('code');
$defaults = $fieldset->form($fieldset->fields())->fill(defaults: true)->toFormValues();

return [
	'docs'     => 'k-block-type-code',
	'defaults' => $defaults,
	'fieldset' => $fieldset->toArray(),
];
