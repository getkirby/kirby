<?php

use Kirby\Cms\App;
use Kirby\Cms\Collection;
use Kirby\Cms\File;
use Kirby\Cms\Filename;
use Kirby\Cms\FileVersion;
use Kirby\Cms\Template;
use Kirby\Data\Data;
use Kirby\Http\Server;
use Kirby\Http\Uri;
use Kirby\Http\Url;
use Kirby\Image\Darkroom;
use Kirby\Text\Markdown;
use Kirby\Text\SmartyPants;
use Kirby\Toolkit\A;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\Tpl as Snippet;

return [

    /**
     * Used by the `css()` helper
     *
     * @param \Kirby\Cms\App $kirby Kirby instance
     * @param string $url Relative or absolute URL
     * @param string|array $options An array of attributes for the link tag or a media attribute string
     */
    'css' => function (App $kirby, string $url, $options = null): string {
        return $url;
    },


    /**
     * Object and variable dumper
     * to help with debugging.
     *
     * @param \Kirby\Cms\App $kirby Kirby instance
     * @param mixed $variable
     * @param bool $echo
     * @return string
     */
    'dump' => function (App $kirby, $variable, bool $echo = true) {
        if (Server::cli() === true) {
            $output = print_r($variable, true) . PHP_EOL;
        } else {
            $output = '<pre>' . print_r($variable, true) . '</pre>';
        }

        if ($echo === true) {
            echo $output;
        }

        return $output;
    },

    /**
     * Modify URLs for file objects
     *
     * @param \Kirby\Cms\App $kirby Kirby instance
     * @param \Kirby\Cms\File $file The original file object
     * @return string
     */
    'file::url' => function (App $kirby, File $file): string {
        return $file->mediaUrl();
    },

    /**
     * Adapt file characteristics
     *
     * @param \Kirby\Cms\App $kirby Kirby instance
     * @param \Kirby\Cms\File|\Kirby\Cms\FileModifications $file The file object
     * @param array $options All thumb options (width, height, crop, blur, grayscale)
     * @return \Kirby\Cms\File|\Kirby\Cms\FileVersion
     */
    'file::version' => function (App $kirby, $file, array $options = []) {
        if ($file->isResizable() === false) {
            return $file;
        }

        // create url and root
        $mediaRoot = dirname($file->mediaRoot());
        $template  = $mediaRoot . '/{{ name }}{{ attributes }}.{{ extension }}';
        $thumbRoot = (new Filename($file->root(), $template, $options))->toString();
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
     * @param \Kirby\Cms\App $kirby Kirby instance
     * @param string $url Relative or absolute URL
     * @param string|array $options An array of attributes for the link tag or a media attribute string
     */
    'js' => function (App $kirby, string $url, $options = null): string {
        return $url;
    },

    /**
     * Add your own Markdown parser
     *
     * @param \Kirby\Cms\App $kirby Kirby instance
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
     * Add your own search engine
     *
     * @param \Kirby\Cms\App $kirby Kirby instance
     * @param \Kirby\Cms\Collection $collection Collection of searchable models
     * @param string $query
     * @param mixed $params
     * @return \Kirby\Cms\Collection|bool
     */
    'search' => function (App $kirby, Collection $collection, string $query = null, $params = []) {
        if (empty(trim($query)) === true) {
            return $collection->limit(0);
        }

        if (is_string($params) === true) {
            $params = ['fields' => Str::split($params, '|')];
        }

        $defaults = [
            'fields'    => [],
            'minlength' => 2,
            'score'     => [],
            'words'     => false,
        ];

        $options     = array_merge($defaults, $params);
        $collection  = clone $collection;
        $searchWords = preg_replace('/(\s)/u', ',', $query);
        $searchWords = Str::split($searchWords, ',', $options['minlength']);
        $lowerQuery  = Str::lower($query);
        $exactQuery  = $options['words'] ? '(\b' . preg_quote($query) . '\b)' : preg_quote($query);

        if (empty($options['stopwords']) === false) {
            $searchWords = array_diff($searchWords, $options['stopwords']);
        }

        $searchWords = array_map(function ($value) use ($options) {
            return $options['words'] ? '\b' . preg_quote($value) . '\b' : preg_quote($value);
        }, $searchWords);

        $preg    = '!(' . implode('|', $searchWords) . ')!i';
        $results = $collection->filter(function ($item) use ($query, $preg, $options, $lowerQuery, $exactQuery) {
            $data = $item->content()->toArray();
            $keys = array_keys($data);
            $keys[] = 'id';

            if (is_a($item, 'Kirby\Cms\User') === true) {
                $keys[] = 'name';
                $keys[] = 'email';
                $keys[] = 'role';
            } elseif (is_a($item, 'Kirby\Cms\Page') === true) {
                // apply the default score for pages
                $options['score'] = array_merge([
                    'id'    => 64,
                    'title' => 64,
                ], $options['score']);
            }

            if (empty($options['fields']) === false) {
                $fields = array_map('strtolower', $options['fields']);
                $keys   = array_intersect($keys, $fields);
            }

            $item->searchHits  = 0;
            $item->searchScore = 0;

            foreach ($keys as $key) {
                $score = $options['score'][$key] ?? 1;
                $value = $data[$key] ?? (string)$item->$key();

                $lowerValue = Str::lower($value);

                // check for exact matches
                if ($lowerQuery == $lowerValue) {
                    $item->searchScore += 16 * $score;
                    $item->searchHits  += 1;

                // check for exact beginning matches
                } elseif ($options['words'] === false && Str::startsWith($lowerValue, $lowerQuery) === true) {
                    $item->searchScore += 8 * $score;
                    $item->searchHits  += 1;

                // check for exact query matches
                } elseif ($matches = preg_match_all('!' . $exactQuery . '!i', $value, $r)) {
                    $item->searchScore += 2 * $score;
                    $item->searchHits  += $matches;
                }

                // check for any match
                if ($matches = preg_match_all($preg, $value, $r)) {
                    $item->searchHits  += $matches;
                    $item->searchScore += $matches * $score;
                }
            }

            return $item->searchHits > 0 ? true : false;
        });

        return $results->sortBy('searchScore', 'desc');
    },

    /**
     * Add your own SmartyPants parser
     *
     * @param \Kirby\Cms\App $kirby Kirby instance
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
     * @param \Kirby\Cms\App $kirby Kirby instance
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
     * @param \Kirby\Cms\App $kirby Kirby instance
     * @param string $name Template name
     * @param string $type Extension type
     * @param string $defaultType Default extension type
     * @return \Kirby\Cms\Template
     */
    'template' => function (App $kirby, string $name, string $type = 'html', string $defaultType = 'html') {
        return new Template($name, $type, $defaultType);
    },

    /**
     * Add your own thumb generator
     *
     * @param \Kirby\Cms\App $kirby Kirby instance
     * @param string $src The root of the original file
     * @param string $template The template for the root to the desired destination
     * @param array $options All thumb options that should be applied: `width`, `height`, `crop`, `blur`, `grayscale`
     * @return string
     */
    'thumb' => function (App $kirby, string $src, string $template, array $options): string {
        $darkroom = Darkroom::factory(option('thumbs.driver', 'gd'), option('thumbs', []));
        $options  = $darkroom->preprocess($src, $options);
        $root     = (new Filename($src, $template, $options))->toString();

        F::copy($src, $root, true);
        $darkroom->process($root, $options);

        return $root;
    },

    /**
     * Modify all URLs
     *
     * @param \Kirby\Cms\App $kirby Kirby instance
     * @param string $path URL path
     * @param array|string|null $options Array of options for the Uri class
     * @param Closure $originalHandler Deprecated: Callback function to the original URL handler with `$path` and `$options` as parameters
     *                                 Use `$kirby->nativeComponent('url')` inside your URL component instead.
     * @return string
     */
    'url' => function (App $kirby, string $path = null, $options = null, Closure $originalHandler = null): string {
        $language = null;

        // get language from simple string option
        if (is_string($options) === true) {
            $language = $options;
            $options  = null;
        }

        // get language from array
        if (is_array($options) === true && isset($options['language']) === true) {
            $language = $options['language'];
            unset($options['language']);
        }

        // get a language url for the linked page, if the page can be found
        if ($kirby->multilang() === true) {
            $parts = Str::split($path, '#');

            if ($page = page($parts[0] ?? null)) {
                $path = $page->url($language);

                if (isset($parts[1]) === true) {
                    $path .= '#' . $parts[1];
                }
            }
        }

        // keep relative urls
        if (substr($path, 0, 2) === './' || substr($path, 0, 3) === '../') {
            return $path;
        }

        $url = Url::makeAbsolute($path, $kirby->url());

        if ($options === null) {
            return $url;
        }

        return (new Uri($url, $options))->toString();
    },

];
