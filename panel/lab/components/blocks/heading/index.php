<?php

use Kirby\Cms\Fieldsets;

$fieldset = Fieldsets::factory()->get('heading');
$defaults = $fieldset->form($fieldset->fields())->data(true);

return [
	'docs'     => 'k-block-type-heading',
	'defaults' => $defaults,
	'fieldset' => $fieldset->toArray(),
];
