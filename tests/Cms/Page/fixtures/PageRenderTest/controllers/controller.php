<?php

return function ($page) {
	return [
		'test' => 'TEST: ' . $page->title()
	];
};
