<?php

namespace Kirby\Filesystem;

use Exception;

function blockMethod($method, $args)
{
	if (defined('KIRBY_TESTING') !== true || KIRBY_TESTING !== true) {
		throw new Exception('A mock file function was loaded outside of the test environment. This should never happen.');
	}

	if (in_array($method, FileTest::$block)) {
		return false;
	}
	if (isset(FileTest::$mocks[$method]) === true) {
		return (FileTest::$mocks[$method])(...$args);
	}

	return call_user_func_array('\\' . $method, $args);
}

function file_put_contents($file, $content, ...$args)
{
	return blockMethod('file_put_contents', [$file, $content, ...$args]);
}

function rename($old, $new)
{
	return blockMethod('rename', [$old, $new]);
}

function copy($old, $new)
{
	return blockMethod('copy', [$old, $new]);
}

function stat($file)
{
	return blockMethod('stat', [$file]);
}

function unlink($file)
{
	return blockMethod('unlink', [$file]);
}
