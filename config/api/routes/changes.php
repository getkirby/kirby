<?php

use Kirby\Api\Controller\Changes;
use Kirby\Cms\App;
use Kirby\Cms\Find;

return [
	[
		'pattern' => '(:all)/changes/discard',
		'method'  => 'POST',
		'action'  => function (string $path) {
			return Changes::discard(
				model: Find::parent($path),
			);
		}
	],
	[
		'pattern' => '(:all)/changes/publish',
		'method'  => 'POST',
		'action'  => function (string $path) {
			return Changes::publish(
				model: Find::parent($path),
				input: App::instance()->request()->get()
			);
		}
	],
	[
		'pattern' => '(:all)/changes/save',
		'method'  => 'POST',
		'action'  => function (string $path) {
			return Changes::save(
				model: Find::parent($path),
				input: App::instance()->request()->get()
			);
		}
	],
];
