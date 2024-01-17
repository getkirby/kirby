<?php

use Kirby\Cms\Fieldsets;

$fieldset = Fieldsets::factory()->get('video');
$defaults = $fieldset->form($fieldset->fields())->data(true);

return [
	'docs'     => 'k-block-type-video',
	'defaults' => $defaults,
	'fieldset' => $fieldset->toArray(),
];
