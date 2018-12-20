<?php

use Kirby\Cms\App;
use Kirby\Cms\Filename;
use Kirby\Cms\FileVersion;
use Kirby\Cms\Model;
use Kirby\Cms\Response;
use Kirby\Cms\Template;
use Kirby\Data\Data;
use Kirby\Exception\NotFoundException;
use Kirby\Image\Darkroom;
use Kirby\Text\SmartyPants;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Tpl as Snippet;

return [
    'file::version' => function (App $kirby, Model $file, array $options = []) {
        if ($file->isResizable() === false) {
            return $file;
        }

        // pre-calculate all thumb attributes
        $darkroom   = Darkroom::factory(option('thumbs.driver', 'gd'), option('thumbs', []));
        $attributes = $darkroom->preprocess($file->root(), $options);

        // create url and root
        $parent    = $file->parent();
        $mediaRoot = $parent->mediaRoot() . '/' . $file->mediaHash();
        $dst       = $mediaRoot . '/{{ name }}{{ attributes }}.{{ extension }}';
        $thumbRoot = (new Filename($file->root(), $dst, $attributes))->toString();
        $thumbName = basename($thumbRoot);
        $job       = $mediaRoot . '/.jobs/' . $thumbName . '.json';

        if (file_exists($thumbRoot) === false) {
            try {
                Data::write($job, array_merge($attributes, [
                    'filename' => $file->filename()
                ]));
            } catch (Throwable $e) {
                return $file;
            }
        }

        return new FileVersion([
            'modifications' => $options,
            'original'      => $file,
            'root'          => $thumbRoot,
            'url'           => $parent->mediaUrl() . '/' . $file->mediaHash() . '/' . $thumbName,
        ]);
    },
    'file::url' => function (App $kirby, Model $file) {
        return $file->mediaUrl();
    },
    'markdown' => function (App $kirby, string $text = null, array $options = []): string {
        static $markdown;

        if (isset($markdown) === false) {
            $parser   = ($options['extra'] ?? false) === true ? 'ParsedownExtra' : 'Parsedown';
            $markdown = new $parser;
            $markdown->setBreaksEnabled($options['breaks'] ?? true);
        }

        // we need the @ here, because parsedown has some notice issues :(
        return @$markdown->text($text);
    },
    'smartypants' => function (App $kirby, string $text = null, array $options = []): string {
        static $smartypants;

        $smartypants = $smartypants ?? new Smartypants($options);

        return $smartypants->parse($text);
    },
    'snippet' => function (App $kirby, string $name, array $data = []) {
        $file = $kirby->root('snippets') . '/' . $name . '.php';

        if (file_exists($file) === false) {
            $file = $kirby->extensions('snippets')[$name] ?? null;
        }

        return Snippet::load($file, $data);
    },
    'template' => function (App $kirby, string $name, string $type = 'html', string $defaultType = 'html') {
        return new Template($name, $type, $defaultType);
    },
    'thumb' => function (App $kirby, string $src, string $dst, array $options) {
        $darkroom = Darkroom::factory(option('thumbs.driver', 'gd'), option('thumbs', []));
        $options  = $darkroom->preprocess($src, $options);
        $root     = (new Filename($src, $dst, $options))->toString();

        F::copy($src, $root);
        $darkroom->process($root, $options);

        return $root;
    },
];
