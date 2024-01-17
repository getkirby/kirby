<?php

use Kirby\Cms\Fieldsets;

$fieldset = Fieldsets::factory()->get('gallery');
$defaults = $fieldset->form($fieldset->fields())->data(true);

return [
	'docs'     => 'k-block-type-gallery',
	'defaults' => $defaults,
	'fieldset' => $fieldset->toArray(),
];
