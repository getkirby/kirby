<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Toolkit\F;
use Throwable;

class Media
{
    public static function link(Model $model, $filename)
    {
        // TODO: this should be refactored when users get normal files
        if (is_a($model, User::class) === true) {
            if ($filename === 'profile.jpg') {
                return $model->avatar()->publish()->url();
            }
        } else {
            if ($file = $model->file($filename)) {
                return $file->publish()->url();
            }
        }

        try {
            $kirby     = $model->kirby();
            $url       = $model->mediaUrl() . '/' . $filename;
            $mediaRoot = $model->mediaRoot();
            $thumb     = $mediaRoot . '/' . $filename;
            $job       = $mediaRoot . '/.jobs/' . $filename . '.json';
            $options   = Data::read($job);

            if (is_a($model, User::class) === true) {
                $file = $model->avatar();
            } else {
                $file = $model->file($options['filename']);
            }

            if (!$file || empty($options) === true) {
                return false;
            }

            $kirby->thumb($file->root(), $thumb, $options);
            F::remove($job);
            return $url;
        } catch (Throwable $e) {
            return false;
        }
    }
}
