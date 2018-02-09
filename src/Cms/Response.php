<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Http\Response as BaseResponse;
use Kirby\Http\Response\Json;

class Response extends BaseResponse
{

    public static function errorPage($data = [], $contentType = 'html', $code = 404)
    {
        if ($code < 400 || $code > 600) {
            $code = 500;
        }

        if (is_string($data) === true) {
            $data = ['errorMessage' => $data];
        }

        $data = array_merge([
            'errorCode'    => $code,
            'errorMessage' => null,
            'errorType'    => null
        ], $data);

        return static::page(App::instance()->site()->errorPage(), $data, $contentType, $code);
    }

    public static function for($input)
    {
        // Empty input
        if (empty($input) === true) {
            return static::errorPage('Not found');
        }

        // Responses
        if (is_a($input, BaseResponse::class) === true) {
            return $input;
        }

        // Pages
        if (is_a($input, Page::class)) {
            return static::page($input);
        }

        // Exceptions
        if (is_a($input, Exception::class)) {
            return static::errorPage([
                'errorMessage' => $input->getMessage(),
                'errorCode'    => $input->getCode(),
                'errorType'    => get_class($input)
            ], 'html', $input->getCode());
        }

        // Pages by id
        if (is_string($input) === true) {
            if ($page = App::instance()->site()->find($input)) {
                return static::page($page);
            }
        }

        // Fallback
        return static::errorPage();
    }

    public static function homePage()
    {
        return static::page(App::instance()->site()->homePage());
    }

    public static function json(array $data = [])
    {
        return new Json($data);
    }

    public static function page(Page $page, array $data = [], $contentType = 'html', $code = 200)
    {
        // we'll need this a few times
        $app = App::instance();

        // create all globals for the
        // controller, template and snippets
        $globals = array_merge($data, [
            'kirby' => $page->kirby(),
            'site'  => $site = $page->site(),
            'pages' => $site->children(),
            'page'  => $site->visit($page)
        ]);

        // try to create the page template
        $template = $app->component('template', $page->template(), [], $contentType);

        // fall back to the default template if it doesn't exist
        if ($template->exists() === false) {
            $template = $app->component('template', 'default', [], $contentType);
        }

        // call the template controller if there's one.
        $globals = array_merge($app->controller($template->name(), $globals, $contentType), $globals);

        // make all globals available
        // for templates and snippets
        Template::globals($globals);

        // create the response object for the page
        return new static($template->render(), 'text/html', $code);
    }

}
