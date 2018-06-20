<?php

use Kirby\Cms\App;
use Kirby\Cms\Html;
use Kirby\Cms\Url;

/**
 * Default KirbyTags definition
 */
return [

    /* Date */
    'date' => [
        'attr' => [],
        'html' => function ($tag) {
            return strtolower($tag->date) === 'year' ? date('Y') : date($tag->date);
        }
    ],

    /* Email */
    'email' => [
        'attr' => [
            'class',
            'rel',
            'target',
            'text',
            'title'
        ],
        'html' => function ($tag) {
            return Html::email($tag->value, $tag->text, [
                'class'  => $tag->class,
                'rel'    => $tag->rel,
                'target' => $tag->target,
                'title'  => $tag->title,
            ]);
        }
    ],

    /* File */
    'file' => [
        'attr' => [
            'class',
            'rel',
            'target',
            'text',
            'title'
        ],
        'html' => function ($tag) {
            $file = App::instance()->file($tag->value, $tag->parent());

            if ($file === null) {
                return $tag->text;
            }

            // use filename if the text is empty and make sure to
            // ignore markdown italic underscores in filenames
            if (empty($tag->text) === true) {
                $tag->text = str_replace('_', '\_', $file->filename());
            }

            return Html::a($file->url(), $tag->text, [
                'class'    => $tag->class,
                'download' => true,
                'rel'      => $tag->rel,
                'target'   => $tag->target,
                'title'    => $tag->title,
            ]);
        }
    ],

    /* Gist */
    'gist' => [
        'attr' => [
            'file'
        ],
        'html' => function ($tag) {
            return Html::gist($tag->value, $tag->file);
        }
    ],

    /* Image */
    'image' => [
        'attr' => [
            'alt',
            'caption',
            'class',
            'height',
            'imgclass',
            'link',
            'linkclass',
            'rel',
            'target',
            'text',
            'title',
            'width'
        ],
        'html' => function ($tag) {
            $tag->src  = Url::to($tag->value);
            $tag->file = Kirby::instance()->file($tag->value, $tag->parent());

            if ($tag->file) {
                $tag->src     = $tag->file->url();
                $tag->alt     = $tag->alt     ?? $tag->file->alt()->or(' ')->value();
                $tag->title   = $tag->title   ?? $tag->file->title()->value();
                $tag->caption = $tag->caption ?? $tag->file->caption()->value();
            }

            $link = function ($img) {
                if (empty($tag->link) === true) {
                    return $img;
                }

                return Html::a($tag->link === 'self' ? $tag->src : $tag->link, [$img], [
                    'rel'    => $tag->rel,
                    'class'  => $tag->linkclass,
                    'target' => $tag->target
                ]);
            };

            $image = Html::img($tag->src, [
                'width'  => $tag->width,
                'height' => $tag->height,
                'class'  => $tag->imgclass,
                'title'  => $tag->title,
                'alt'    => $tag->alt ?? ' '
            ]);

            return Html::figure([ $link($image) ], $tag->caption, [
                'class' => $tag->class
            ]);
        }
    ],

    /* Link */
    'link' => [
        'attr' => [
            'class',
            'rel',
            'role',
            'target',
            'title',
            'text',
        ],
        'html' => function ($tag) {
            return Html::a($tag->value, $tag->text, [
                'rel'    => $tag->rel,
                'class'  => $tag->class,
                'role'   => $tag->role,
                'title'  => $tag->title,
                'target' => $tag->target,
            ]);
        }
    ],

    /* Tel */
    'tel' => [
        'attr' => [
            'class',
            'rel',
            'text',
            'title'
        ],
        'html' => function($tag) {

            $text = $tag->text;
            $tel  = str_replace(['/', ' ', '-'], '', $tag->value);

            if (empty($text) === true) {
                $text = $tag->value;
            }

            return Html::a('tel:' . $tel, $text, [
                'class' => $tag->class,
                'rel'   => $tag->rel,
                'title' => $tag->title
            ]);

        }
    ],

    /* Twitter */
    'twitter' => [
        'attr' => [
            'class',
            'rel',
            'target',
            'text',
            'title'
        ],
        'html' => function ($tag) {

            // get and sanitize the username
            $username = str_replace('@', '', $tag->value);

            // build the profile url
            $url = 'https://twitter.com/' . $username;

            // sanitize the link text
            $text = $tag->text ?? '@' . $username;

            // build the final link
            return Html::a($url, $text, [
                'class'  => $tag->class,
                'rel'    => $tag->rel,
                'target' => $tag->target,
                'title'  => $tag->title,
            ]);
        }
    ],

    /* Video */
    'video' => [
        'attr' => [
            'class',
            'caption',
            'height',
            'width'
        ],
        'html' => function ($tag) {

            $video = Html::video(
                $tag->value,
                $tag->option('kirbytext.video.options', [])
            );

            return Html::figure([$video], $tag->caption, [
                'class'  => $tag->class  ?? $tag->option('kirbytext.video.class', 'video'),
                'height' => $tag->height ?? $tag->option('kirbytext.video.height'),
                'width'  => $tag->width  ?? $tag->option('kirbytext.video.width'),
            ]);

        }
    ],

];
