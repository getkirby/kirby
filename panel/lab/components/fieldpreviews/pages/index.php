<?php

return [
	'docs' => 'k-pages-field-preview',
	'ids'  => kirby()->page('sections/pages')?->children()->limit(3)->values(
		fn ($page) => $page->uuid()?->toString() ?? $page->id()
	) ?? []
];
