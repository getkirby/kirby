<?php

use Kirby\Cms\Fieldsets;

$fieldset = Fieldsets::factory()->get('markdown');
$defaults = $fieldset->form($fieldset->fields())->fill(defaults: true)->toFormValues();

return [
	'docs'     => 'k-block-type-markdown',
	'defaults' => $defaults,
	'fieldset' => $fieldset->toArray(),
];
