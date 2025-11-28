<?php

return [
	'docs' => 'k-files-field-preview',
	'ids'  => kirby()->page('sections/files')?->files()->limit(3)->values(
		fn ($file) => $file->uuid()?->toString() ?? $file->id()
	) ?? []
];
