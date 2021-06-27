<?php

namespace Kirby\Filesystem;

function blockMethod($method, $args)
{
    if (defined('KIRBY_TESTING') !== true || KIRBY_TESTING !== true) {
        throw new Exception('A mock file function was loaded outside of the test environment. This should never happen.');
    }

    if (in_array($method, FileTest::$block)) {
        return false;
    }
    return call_user_func_array('\\' . $method, $args);
}

function file_put_contents($file, $content)
{
    return blockMethod('file_put_contents', [$file, $content]);
}

function rename($old, $new)
{
    return blockMethod('rename', [$old, $new]);
}

function copy($old, $new)
{
    return blockMethod('copy', [$old, $new]);
}

function unlink($file)
{
    return blockMethod('unlink', [$file]);
}
