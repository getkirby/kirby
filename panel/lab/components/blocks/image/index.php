<?php

use Kirby\Cms\Fieldsets;

$fieldset = Fieldsets::factory()->get('image');
$defaults = $fieldset->form($fieldset->fields())->fill(defaults: true)->toFormValues();

return [
	'docs'     => 'k-block-type-image',
	'defaults' => $defaults,
	'fieldset' => $fieldset->toArray(),
];
