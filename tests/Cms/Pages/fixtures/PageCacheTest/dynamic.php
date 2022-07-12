This is a test: <?= uniqid() ?>

<?php

if (Str::contains($page->slug(), '-auth')) {
	$kirby->request()->auth();
}

if (Str::contains($page->slug(), '-cookie')) {
	Cookie::get('foo');
}

if (Str::contains($page->slug(), '-session')) {
	$kirby->session();
}
