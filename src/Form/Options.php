<?php

namespace Kirby\Form;

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\StructureObject;
use Kirby\Cms\User;
use Kirby\Toolkit\A;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Obj;

/**
 * Foundation for the Options query
 * classes, that are used to generate
 * options arrays for select fiels,
 * radio boxes, checkboxes and more.
 */
class Options
{
    protected static function aliases(): array
    {
        return [
            'Kirby\Cms\File'            => 'file',
            'Kirby\Toolkit\Obj'         => 'arrayItem',
            'Kirby\Cms\Page'            => 'page',
            'Kirby\Cms\StructureObject' => 'structureItem',
            'Kirby\Cms\User'            => 'user',
        ];
    }

    public static function api($api, $model = null): array
    {
        $model = $model ?? App::instance()->site();
        $fetch = null;
        $text  = null;
        $value = null;

        if (is_array($api) === true) {
            $fetch = $api['fetch'] ?? null;
            $text  = $api['text']  ?? null;
            $value = $api['value'] ?? null;
            $url   = $api['url']   ?? null;
        } else {
            $url = $api;
        }

        $optionsApi = new OptionsApi([
            'data'  => static::data($model),
            'fetch' => $fetch,
            'url'   => $url,
            'text'  => $text,
            'value' => $value
        ]);

        return $optionsApi->options();
    }

    protected static function data($model): array
    {
        $kirby = $model->kirby();

        // default data setup
        $data = [
            'kirby' => $kirby,
            'site'  => $kirby->site(),
            'users' => $kirby->users(),
        ];

        // add the model by the proper alias
        foreach (static::aliases() as $className => $alias) {
            if (is_a($model, $className) === true) {
                $data[$alias] = $model;
            }
        }

        return $data;
    }

    public static function factory($options, array $props = [], $model = null): array
    {
        switch ($options) {
            case 'api':
                $options = static::api($props['api']);
                break;
            case 'query':
                $options = static::query($props['query'], $model);
                break;
            case 'children':
            case 'grandChildren':
            case 'siblings':
            case 'index':
            case 'files':
            case 'images':
            case 'documents':
            case 'videos':
            case 'audio':
            case 'code':
            case 'archives':
                $options = static::query('page.' . $options, $model);
                break;
            case 'pages':
                $options = static::query('site.index', $model);
                break;
        }

        if (is_array($options) === false) {
            return [];
        }

        $result = [];

        foreach ($options as $key => $option) {
            if (is_array($option) === false || isset($option['value']) === false) {
                $option = [
                    'value' => is_int($key) ? $option : $key,
                    'text'  => $option
                ];
            }

            // translate the option text
            $option['text'] = I18n::translate($option['text'], $option['text']);

            // add the option to the list
            $result[] = $option;
        }

        return $result;
    }

    public static function query($query, $model = null): array
    {
        $model = $model ?? App::instance()->site();

        // default text setup
        $text = [
            'arrayItem'     => '{{ arrayItem.value }}',
            'file'          => '{{ file.filename }}',
            'page'          => '{{ page.title }}',
            'structureItem' => '{{ structureItem.title }}',
            'user'          => '{{ user.username }}',
        ];

        // default value setup
        $value = [
            'arrayItem'     => '{{ arrayItem.value }}',
            'file'          => '{{ file.id }}',
            'page'          => '{{ page.id }}',
            'structureItem' => '{{ structureItem.id }}',
            'user'          => '{{ user.email }}',
        ];

        // resolve array query setup
        if (is_array($query) === true) {
            $text  = $query['text']  ?? $text;
            $value = $query['value'] ?? $value;
            $query = $query['fetch'] ?? null;
        }

        $optionsQuery = new OptionsQuery([
            'aliases' => static::aliases(),
            'data'    => static::data($model),
            'query'   => $query,
            'text'    => $text,
            'value'   => $value
        ]);

        return $optionsQuery->options();
    }
}
