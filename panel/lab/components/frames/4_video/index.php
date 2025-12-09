<?php

return [
	'docs' => 'k-video-frame',
	'id'   => kirby()->page('sections/files')?->videos()->first()?->uuid()?->toString()
];
