<?php

return [
	'docs' => 'k-users-field-preview',
	'ids'  => kirby()->users()->limit(3)->values(
		fn ($user) => $user->uuid()?->toString() ?? $user->id()
	) ?? []
];
