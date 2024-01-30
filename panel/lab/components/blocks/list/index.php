<?php

use Kirby\Cms\Fieldsets;

$fieldset = Fieldsets::factory()->get('list');
$defaults = $fieldset->form($fieldset->fields())->data(true);

return [
	'docs'     => 'k-block-type-list',
	'defaults' => $defaults,
	'fieldset' => $fieldset->toArray(),
];
