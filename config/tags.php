<?php

/**
 * Default KirbyTags definition
 */
return [

    /* Date */
    'date' => [
        'attr' => [],
        'html' => function () {
            return strtolower($this->date) === 'year' ? date('Y') : date($this->date);
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
        'html' => function () {
            return Html::email($this->value, $this->text, [
                'class'  => $this->class,
                'rel'    => $this->rel,
                'target' => $this->target,
                'title'  => $this->title,
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
        'html' => function () {
            $file = Kirby::instance()->file($this->value, $this->page());

            if ($file === null) {
                return $this->text;
            }

            // use filename if the text is empty and make sure to
            // ignore markdown italic underscores in filenames
            if (empty($this->text) === true) {
                $this->text = str_replace('_', '\_', $file->filename());
            }

            return Html::a($file->url(), $this->text, [
                'class'    => $this->class,
                'download' => true,
                'rel'      => $this->rel,
                'target'   => $this->target,
                'title'    => $this->title,
            ]);
        }
    ],

    /* Gist */
    'gist' => [
        'attr' => [
            'file'
        ],
        'html' => function () {
            return Html::gist($this->value, $this->file);
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
        'html' => function () {
            $this->src  = Url::to($this->value);
            $this->file = Kirby::instance()->file($this->value, $this->page());

            if ($this->file) {
                $this->src     = $this->file->url();
                $this->alt     = $this->alt     ?? $this->file->alt()->or(' ')->value();
                $this->title   = $this->title   ?? $this->file->title()->value();
                $this->caption = $this->caption ?? $this->file->caption()->value();
            }

            $link = function ($img) {
                if (empty($this->link) === true) {
                    return $img;
                }

                return Html::a($this->link === 'self' ? $this->src : $this->link, [$img], [
                    'rel'    => $this->rel,
                    'class'  => $this->linkclass,
                    'target' => $this->target
                ]);
            };

            $image = Html::img($this->src, array(
                'width'  => $this->width,
                'height' => $this->height,
                'class'  => $this->imgclass,
                'title'  => $this->title,
                'alt'    => $this->alt ?? ' '
            ));

            return Html::figure([ $link($image) ], $this->caption, [
                'class' => $this->class
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
        'html' => function () {
            return Html::a($this->value, $this->text, [
                'rel'    => $this->rel,
                'class'  => $this->class,
                'role'   => $this->role,
                'title'  => $this->title,
                'target' => $this->target,
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
        'html' => function() {

            $text = $this->text;
            $tel  = str_replace(['/', ' ', '-'], '', $this->value);

            if (empty($text) === true) {
                $text = $this->value;
            }

            return Html::a('tel:' . $tel, $text, [
                'class' => $this->class,
                'rel'   => $this->rel,
                'title' => $this->title
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
        'html' => function () {

            // get and sanitize the username
            $username = str_replace('@', '', $this->value);

            // build the profile url
            $url = 'https://twitter.com/' . $username;

            // sanitize the link text
            $text = $this->text ?? '@' . $username;

            // build the final link
            return Html::a($url, $text, [
                'class'  => $this->class,
                'rel'    => $this->rel,
                'target' => $this->target,
                'title'  => $this->title,
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
        'html' => function () {

            $video = Html::video(
                $this->value,
                $this->option('kirbytext.video.options', [])
            );

            return Html::figure([$video], $this->caption, [
                'class'  => $this->class  ?? $this->option('kirbytext.video.class', 'video'),
                'height' => $this->height ?? $this->option('kirbytext.video.height'),
                'width'  => $this->width  ?? $this->option('kirbytext.video.width'),
            ]);

        }
    ],

];
