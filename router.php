<?php

$uri = urldecode(
	parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

// Emulate Apache's `mod_rewrite` functionality
if ($uri !== '/' && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $uri)) {
	return false;
}

$_SERVER['SCRIPT_NAME'] = '/index.php';

require $_SERVER['DOCUMENT_ROOT'] . '/' . $_SERVER['SCRIPT_NAME'];
