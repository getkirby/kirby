<?php

namespace Kirby\Toolkit;

function blockMethod($method, $args)
{
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
