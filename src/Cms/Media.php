<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;
use Throwable;

class Media
{
    public static function link(Model $model, $filename)
    {
        $filename = urldecode($filename);

        // TODO: this should be refactored when users get normal files
        if (is_a($model, 'Kirby\Cms\User') === true) {
            if ($filename === 'profile.jpg') {
                return $model->avatar()->publish()->url();
            }
        } else {
            if ($file = $model->file($filename)) {
                return $file->publish()->url();
            }
        }

    }
}
