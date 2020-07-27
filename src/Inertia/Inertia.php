<?php

namespace Kirby\Inertia;

use Closure;
use Kirby\Http\Response;
use Kirby\Http\Url;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\Tpl;

class Inertia
{
    public static $request;
    public static $shared;
    public static $version;
    public static $view;

    public static function json(array $response = [])
    {
        return Response::json($response, null, null, [
            'Vary'      => 'Accept',
            'X-Inertia' => 'true'
        ]);
    }

    public static function props(string $component, array $props = [])
    {
        // Merge with shared props
        $props = array_merge(call_user_func(static::$shared), $props);

        // Partial request
        $only = Str::split(static::$request->header('X-Inertia-Partial-Data'));

        if (empty($only) === false && static::$request->header('X-Inertia-Partial-Component') === $component) {
            foreach ($props as $key => $value) {
                if (in_array($key, $only) === false) {
                    unset($props[$key]);
                }
            }
        }

        // Call Lazy Props
        array_walk_recursive($props, function (&$prop) {
            if (is_a($prop, Closure::class)) {
                $prop = $prop();
            }
        });

        return $props;
    }

    public static function render(string $component, array $props = [])
    {
        $props    = static::props($component, $props);
        $response = static::response($component, $props);
        $json     = static::$request->header('X-Inertia') || static::$request->get('json');

        if (static::$request->method() === 'GET' && $json) {
            return static::json($response);
        }

        return call_user_func(static::$view, $response);
    }

    public static function response(string $component, array $props = [])
    {
        return [
            'component' => $component,
            'props'     => $props,
            'url'       => Url::current(),
            'version'   => static::$version
        ];
    }

}
