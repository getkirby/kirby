<?php

return [
	'docs' => 'k-image-frame',
	'id'   => kirby()->page('sections/files')?->images()->first()?->uuid()?->toString()
];
