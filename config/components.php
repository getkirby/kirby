<?php

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Filename;
use Kirby\Cms\FileVersion;
use Kirby\Cms\FileModifications;
use Kirby\Cms\Model;
use Kirby\Cms\Response;
use Kirby\Cms\Template;
use Kirby\Data\Data;
use Kirby\Exception\NotFoundException;
use Kirby\Image\Darkroom;
use Kirby\Text\Markdown;
use Kirby\Text\SmartyPants;
use Kirby\Toolkit\A;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Tpl as Snippet;

return [

    /**
     * Used by the `css()` helper
     *
     * @param Kirby\Cms\App $kirby Kirby instance
     * @param string $url Relative or absolute URL
     * @param string|array $options An array of attributes for the link tag or a media attribute string
     */
    'css' => function (App $kirby, string $url, $options = null): string {
        return $url;
    },

    /**
     * Modify URLs for file objects
     *
     * @param Kirby\Cms\App $kirby Kirby instance
     * @param Kirby\Cms\File $file The original file object
     * @return string
     */
    'file::url' => function (App $kirby, File $file): string {
        return $file->mediaUrl();
    },

    /**
     * Adapt file characteristics
     *
     * @param Kirby\Cms\App $kirby Kirby instance
     * @param Kirby\Cms\File|Kirby\Cms\FileModifications $file The file object
     * @param array $options All thumb options (width, height, crop, blur, grayscale)
     * @return Kirby\Cms\File|Kirby\Cms\FileVersion
     */
    'file::version' => function (App $kirby, $file, array $options = []) {
        if ($file->isResizable() === false) {
            return $file;
        }

        // create url and root
        $mediaRoot = dirname($file->mediaRoot());
        $dst       = $mediaRoot . '/{{ name }}{{ attributes }}.{{ extension }}';
        $thumbRoot = (new Filename($file->root(), $dst, $options))->toString();
        $thumbName = basename($thumbRoot);
        $job       = $mediaRoot . '/.jobs/' . $thumbName . '.json';

        if (file_exists($thumbRoot) === false) {
            try {
                Data::write($job, array_merge($options, [
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
            'url'           => dirname($file->mediaUrl()) . '/' . $thumbName,
        ]);
    },

    /**
     * Used by the `js()` helper
     *
     * @param Kirby\Cms\App $kirby Kirby instance
     * @param string $url Relative or absolute URL
     * @param string|array $options An array of attributes for the link tag or a media attribute string
     */
    'js' => function (App $kirby, string $url, $options = null): string {
        return $url;
    },

    /**
     * Add your own Markdown parser
     *
     * @param Kirby\Cms\App $kirby Kirby instance
     * @param string $text Text to parse
     * @param array $options Markdown options
     * @param bool $inline Whether to wrap the text in `<p>` tags
     * @return string
     */
    'markdown' => function (App $kirby, string $text = null, array $options = [], bool $inline = false): string {
        static $markdown;
        static $config;

        // if the config options have changed or the component is called for the first time,
        // (re-)initialize the parser object
        if ($config !== $options) {
            $markdown = new Markdown($options);
            $config   = $options;
        }

        return $markdown->parse($text, $inline);
    },

    /**
     * Add your own SmartyPants parser
     *
     * @param Kirby\Cms\App $kirby Kirby instance
     * @param string $text Text to parse
     * @param array $options SmartyPants options
     * @return string
     */
    'smartypants' => function (App $kirby, string $text = null, array $options = []): string {
        static $smartypants;
        static $config;

        // if the config options have changed or the component is called for the first time,
        // (re-)initialize the parser object
        if ($config !== $options) {
            $smartypants = new Smartypants($options);
            $config      = $options;
        }

        return $smartypants->parse($text);
    },

    /**
     * Add your own snippet loader
     *
     * @param Kirby\Cms\App $kirby Kirby instance
     * @param string|array $name Snippet name
     * @param array $data Data array for the snippet
     * @return string|null
     */
    'snippet' => function (App $kirby, $name, array $data = []): ?string {
        $snippets = A::wrap($name);

        foreach ($snippets as $name) {
            $name = (string)$name;
            $file = $kirby->root('snippets') . '/' . $name . '.php';

            if (file_exists($file) === false) {
                $file = $kirby->extensions('snippets')[$name] ?? null;
            }

            if ($file) {
                break;
            }
        }

        return Snippet::load($file, $data);
    },

    /**
     * Add your own template engine
     *
     * @param Kirby\Cms\App $kirby Kirby instance
     * @param string $name Template name
     * @param string $type Extension type
     * @param string $defaultType Default extension type
     * @return Kirby\Cms\Template
     */
    'template' => function (App $kirby, string $name, string $type = 'html', string $defaultType = 'html') {
        return new Template($name, $type, $defaultType);
    },

    /**
     * Add your own thumb generator
     *
     * @param Kirby\Cms\App $kirby Kirby instance
     * @param string $src The root of the original file
     * @param string $dst The root to the desired destination
     * @param array $options All thumb options that should be applied: `width`, `height`, `crop`, `blur`, `grayscale`
     * @return string
     */
    'thumb' => function (App $kirby, string $src, string $dst, array $options): string {
        $darkroom = Darkroom::factory(option('thumbs.driver', 'gd'), option('thumbs', []));
        $options  = $darkroom->preprocess($src, $options);
        $root     = (new Filename($src, $dst, $options))->toString();

        F::copy($src, $root, true);
        $darkroom->process($root, $options);

        return $root;
    },

    /**
     * Modify all URLs
     *
     * @param Kirby\Cms\App $kirby Kirby instance
     * @param string $path URL path
     * @param array|null $options Array of options for the Uri class
     * @param Closure $originalHandler Callback function to the original URL handler with `$path` and `$options` as parameters
     * @return string
     */
    'url' => function (App $kirby, string $path = null, $options = [], Closure $originalHandler): string {
        return $originalHandler($path, $options);
    },

];
