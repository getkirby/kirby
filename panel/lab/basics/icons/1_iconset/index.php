<?php

use Kirby\Filesystem\F;

$assets = kirby()->panel()->assets();
$svg    = new SimpleXMLElement(F::read($assets->iconsRoot()));
$icons  = [];

foreach ($svg->defs->children() as $symbol) {
	$slug = str_replace('icon-', '', $symbol->attributes()->id);
	$icons[] = $slug;
}

return [
	'icons'  => $icons,
	'source' => 'panel/public/img/icons.svg'
];
