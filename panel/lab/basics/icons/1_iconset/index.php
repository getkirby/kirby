<?php

use Kirby\Panel\Assets;

$assets = new Assets();
$file   = $assets->icons();
$svg    = new SimpleXMLElement($file);
$icons = [];

foreach ($svg->defs->children() as $symbol) {
	$slug = str_replace('icon-', '', $symbol->attributes()->id);
	$icons[] = $slug;
}

return [
	'icons'  => $icons,
	'source' => 'panel/public/img/icons.svg'
];
