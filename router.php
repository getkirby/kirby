<?php

// prepend a fake host to ensure that PHP can parse the path even if it contains weird stuff;
// afterwards just take the plain path back out from the parsed result
$uri = parse_url('https://getkirby.com/' . ltrim($_SERVER['REQUEST_URI'], '/'), PHP_URL_PATH) ?? '/';
$uri = urldecode($uri);

// Emulate Apache's `mod_rewrite` functionality
if ($uri !== '/' && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($uri, '/'))) {
	return false;
}

$_SERVER['SCRIPT_NAME'] = '/index.php';

require $_SERVER['DOCUMENT_ROOT'] . '/' . $_SERVER['SCRIPT_NAME'];
