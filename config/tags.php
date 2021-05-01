<?php

use Kirby\Cms\Html;
use Kirby\Cms\Url;
use Kirby\Text\KirbyTag;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;

/**
 * Default KirbyTags definition
 */
return [

    /**
     * Date
     */
    'date' => [
        'attr' => [],
        'html' => function ($tag) {
            return strtolower($tag->date) === 'year' ? date('Y') : date($tag->date);
        }
    ],

    /**
     * Email
     */
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

    /**
     * File
     */
    'file' => [
        'attr' => [
            'class',
            'download',
            'rel',
            'target',
            'text',
            'title'
        ],
        'html' => function ($tag) {
            if (!$file = $tag->file($tag->value)) {
                return $tag->text;
            }

            // use filename if the text is empty and make sure to
            // ignore markdown italic underscores in filenames
            if (empty($tag->text) === true) {
                $tag->text = str_replace('_', '\_', $file->filename());
            }

            return Html::a($file->url(), $tag->text, [
                'class'    => $tag->class,
                'download' => $tag->download !== 'false',
                'rel'      => $tag->rel,
                'target'   => $tag->target,
                'title'    => $tag->title,
            ]);
        }
    ],

    /**
     * Gist
     */
    'gist' => [
        'attr' => [
            'file'
        ],
        'html' => function ($tag) {
            return Html::gist($tag->value, $tag->file);
        }
    ],

    /**
     * Image
     */
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
            'title',
            'width'
        ],
        'html' => function ($tag) {
            if ($tag->file = $tag->file($tag->value)) {
                $tag->src     = $tag->file->url();
                $tag->alt     = $tag->alt     ?? $tag->file->alt()->or(' ')->value();
                $tag->title   = $tag->title   ?? $tag->file->title()->value();
                $tag->caption = $tag->caption ?? $tag->file->caption()->value();
            } else {
                $tag->src = Url::to($tag->value);
            }

            $link = function ($img) use ($tag) {
                if (empty($tag->link) === true) {
                    return $img;
                }

                if ($link = $tag->file($tag->link)) {
                    $link = $link->url();
                } else {
                    $link = $tag->link === 'self' ? $tag->src : $tag->link;
                }

                return Html::a($link, [$img], [
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

            if ($tag->kirby()->option('kirbytext.image.figure', true) === false) {
                return $link($image);
            }

            // render KirbyText in caption
            if ($tag->caption) {
                $tag->caption = [$tag->kirby()->kirbytext($tag->caption, [], true)];
            }

            return Html::figure([ $link($image) ], $tag->caption, [
                'class' => $tag->class
            ]);
        }
    ],

    /**
     * Link
     */
    'link' => [
        'attr' => [
            'class',
            'lang',
            'rel',
            'role',
            'target',
            'title',
            'text',
        ],
        'html' => function ($tag) {
            if (empty($tag->lang) === false) {
                $tag->value = Url::to($tag->value, $tag->lang);
            }

            return Html::a($tag->value, $tag->text, [
                'rel'    => $tag->rel,
                'class'  => $tag->class,
                'role'   => $tag->role,
                'title'  => $tag->title,
                'target' => $tag->target,
            ]);
        }
    ],

    /**
     * Tel
     */
    'tel' => [
        'attr' => [
            'class',
            'rel',
            'text',
            'title'
        ],
        'html' => function ($tag) {
            return Html::tel($tag->value, $tag->text, [
                'class' => $tag->class,
                'rel'   => $tag->rel,
                'title' => $tag->title
            ]);
        }
    ],

    /**
     * Twitter
     */
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

    /**
     * Video
     */
    'video' => [
        'attr' => [
            'autoplay',
            'caption',
            'controls',
            'class',
            'height',
            'loop',
            'muted',
            'poster',
            'preload',
            'style',
            'width',
        ],
        'html' => function ($tag) {
            // all available video tag attributes
            $availableAttrs = KirbyTag::$types[$tag->type]['attr'];

            // global video tag options
            $attrs   = $tag->kirby()->option('kirbytext.video', []);
            $options = $attrs['options'] ?? [];

            // removes options from attributes
            if (isset($attrs['options']) === true) {
                unset($attrs['options']);
            }

            // injects default values from global options
            // applies only defined attributes to safely update tag props
            foreach ($attrs as $key => $value) {
                if (
                    in_array($key, $availableAttrs) === true &&
                    (isset($tag->{$key}) === false || $tag->{$key} === null)
                ) {
                    $tag->{$key} = $value;
                }
            }

            // checks and gets if poster is local file
            if (
                empty($tag->poster) === false &&
                Str::startsWith($tag->poster, 'http://') !== true &&
                Str::startsWith($tag->poster, 'https://') !== true
            ) {
                if ($poster = $tag->file($tag->poster)) {
                    $tag->poster = $poster->url();
                }
            }

            // converts tag attributes to supported formats (listed below) to output correct html
            // booleans: autoplay, controls, loop, muted
            // strings : height, poster, preload, width
            // for ex  : `autoplay` will not work if `false` is a `string` instead of a `boolean`
            $attrs = [
                'autoplay' => $autoplay = Str::toType($tag->autoplay, 'bool'),
                'controls' => Str::toType($tag->controls ?? true, 'bool'),
                'height'   => $tag->height,
                'loop'     => Str::toType($tag->loop, 'bool'),
                'muted'    => Str::toType($tag->muted ?? $autoplay, 'bool'),
                'poster'   => $tag->poster,
                'preload'  => $tag->preload,
                'width'    => $tag->width
            ];

            // handles local and remote video file
            if (
                Str::startsWith($tag->value, 'http://') !== true &&
                Str::startsWith($tag->value, 'https://') !== true
            ) {
                // handles local video file
                if ($tag->file = $tag->file($tag->value)) {
                    $source = Html::tag('source', null, [
                        'src'  => $tag->file->url(),
                        'type' => $tag->file->mime()
                    ]);
                    $video = Html::tag('video', [$source], $attrs);
                }
            } else {
                // firstly handles supported video providers as youtube, vimeo, etc
                try {
                    $video = Html::video(
                        $tag->value,
                        $options,
                        // providers only support width and height attributes
                        [
                            'height' => $tag->height,
                            'width'  => $tag->width
                        ]
                    );
                } catch (Exception $e) {
                    // if not one of the supported video providers
                    // it checks if there is a valid remote video file
                    $extension = F::extension($tag->value);
                    $type      = F::extensionToType($extension);
                    $mime      = F::extensionToMime($extension);

                    if ($type === 'video') {
                        $source = Html::tag('source', null, [
                            'src'  => $tag->value,
                            'type' => $mime
                        ]);
                        $video = Html::tag('video', [$source], $attrs);
                    }
                }
            }

            return Html::figure([$video ?? ''], $tag->caption, [
                'class' => $tag->class ?? 'video',
                'style' => $tag->style
            ]);
        }
    ],

];
