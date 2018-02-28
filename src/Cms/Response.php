<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Http\Response as BaseResponse;
use Kirby\Http\Response\Json;
use Kirby\Util\F;

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

    public static function for($input, $data = [], $contentType = 'html', $code = 200)
    {
        // Empty input
        if (empty($input) === true) {
            return static::errorPage('Not found', $contentType, $code);
        }

        // Responses
        if (is_a($input, BaseResponse::class) === true) {
            return $input;
        }

        // Pages
        if (is_a($input, Page::class)) {
            return static::page($input, $data, $contentType, $code);
        }

        // Exceptions
        if (is_a($input, Exception::class)) {
            return static::errorPage(array_merge($data, [
                'errorMessage' => $input->getMessage(),
                'errorCode'    => $input->getCode(),
                'errorType'    => get_class($input)
            ]), $contentType, $input->getCode());
        }

        // Pages by id
        if (is_string($input) === true) {
            if ($page = App::instance()->site()->find($input)) {
                return static::page($page, $data, $contentType, $code);
            }
        }

        // Fallback
        return static::errorPage($data, $contentType, $code);
    }

    public static function homePage(array $data = [], $contentType = 'html', $code = 200)
    {
        return static::page(App::instance()->site()->homePage(), $data, $contentType, $code);
    }

    public static function json(array $data = [])
    {
        return new Json($data);
    }

    public static function page(Page $page, array $data = [], $contentType = 'html', $code = 200)
    {
        // we'll need this a few times
        $kirby = $page->kirby();

        // create all globals for the
        // controller, template and snippets
        $globals = array_merge($data, [
            'kirby' => $kirby,
            'site'  => $site = $page->site(),
            'pages' => $site->children(),
            'page'  => $site->visit($page)
        ]);

        // try to create the page template
        $template = $kirby->component('template', $page->template(), [], $contentType);

        // fall back to the default template if it doesn't exist
        if ($template->exists() === false) {
            $template = $kirby->component('template', 'default', [], $contentType);
        }

        // react if even the default template does not exist
        if ($template->exists() === false) {
            if ($page->isErrorPage() === true) {
                throw new Exception('The error and the default template do not exist');
            }

            return static::errorPage('The default template does not exist');
        }

        // call the template controller if there's one.
        $globals = array_merge($kirby->controller($template->name(), $globals, $contentType), $globals);

        // make all globals available
        // for templates and snippets
        Template::globals($globals);

        // convert the content representation type to a usable mime type
        $mime = F::extensionToMime($contentType) ?? 'text/html';

        // create the response object for the page
        return new static($template->render(), $mime, $code);
    }

}
