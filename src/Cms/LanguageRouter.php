<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Router;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

/**
 * The language router is used internally
 * to handle language-specific (scoped) routes
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class LanguageRouter
{
    /**
     * The parent language
     *
     * @var Language
     */
    protected $language;

    /**
     * The router instance
     *
     * @var Router
     */
    protected $router;

    /**
     * Creates a new language router instance
     * for the given language
     *
     * @param \Kirby\Cms\Language $language
     */
    public function __construct(Language $language)
    {
        $this->language = $language;
    }

    /**
     * Fetches all scoped routes for the
     * current language from the Kirby instance
     *
     * @return array
     * @throws \Kirby\Exception\NotFoundException
     */
    public function routes(): array
    {
        $language = $this->language;
        $kirby    = $language->kirby();
        $routes   = $kirby->routes();

        // only keep the scoped language routes
        $routes = array_values(array_filter($routes, function ($route) use ($language) {

            // no language scope
            if (empty($route['language']) === true) {
                return false;
            }

            // wildcard
            if ($route['language'] === '*') {
                return true;
            }

            // get all applicable languages
            $languages = Str::split(strtolower($route['language']), '|');

            // validate the language
            return in_array($language->code(), $languages) === true;
        }));

        // add the page-scope if necessary
        foreach ($routes as $index => $route) {
            if ($pageId = ($route['page'] ?? null)) {
                if ($page = $kirby->page($pageId)) {

                    // convert string patterns to arrays
                    $patterns = A::wrap($route['pattern']);

                    // prefix all patterns with the page slug
                    $patterns = array_map(function ($pattern) use ($page, $language) {
                        return $page->uri($language) . '/' . $pattern;
                    }, $patterns);

                    // reinject the pattern and the full page object
                    $routes[$index]['pattern'] = $patterns;
                    $routes[$index]['page']    = $page;
                } else {
                    throw new NotFoundException('The page "' . $pageId . '" does not exist');
                }
            }
        }

        return $routes;
    }

    /**
     * Wrapper around the Router::call method
     * that injects the Language instance and
     * if needed also the Page as arguments.
     *
     * @param string|null $path
     * @return mixed
     */
    public function call(string $path = null)
    {
        $language = $this->language;
        $kirby    = $language->kirby();
        $router   = new Router($this->routes());

        try {
            return $router->call($path, $kirby->request()->method(), function ($route) use ($kirby, $language) {
                $kirby->setCurrentTranslation($language);
                $kirby->setCurrentLanguage($language);

                if ($page = $route->page()) {
                    return $route->action()->call($route, $language, $page, ...$route->arguments());
                } else {
                    return $route->action()->call($route, $language, ...$route->arguments());
                }
            });
        } catch (Exception $e) {
            return $kirby->resolve($path, $language->code());
        }
    }
}
