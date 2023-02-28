<?php

use Kirby\Cms\App;

return function (int $a, int $b, int $c, App $kirby) {
	$result = $a + $b + $c;

	return compact('result', 'kirby');
};
