<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Router;

/**
 *
 */
class LanguageRouter
{

    protected $language;
    protected $router;

    public function __construct(Language $language)
    {
        $this->language = $language;
    }

    public function routes(): array
    {
        $language = $this->language;
        $kirby    = $language->kirby();
        $routes   = $kirby->routes();

        // only keep the scoped language routes
        $routes = array_filter($routes, function ($route) {
            return ($route['languages'] ?? false);
        });

        // add the page-scope if necessary
        foreach ($routes as $index => $route) {
            if ($pageId = ($route['page'] ?? null)) {
                if ($page = $kirby->page($pageId)) {
                    $routes[$index]['pattern'] = $page->slug($language) . '/' . $route['pattern'];
                    $routes[$index]['page']    = $page;
                } else {
                    throw new NotFoundException('The page "' . $pageId . '" does not exist');
                }
            }
        }

        return $routes;
    }

    public function call(string $path = null)
    {
        $language = $this->language;
        $kirby    = $language->kirby();
        $router   = new Router($this->routes());

        try {
            return $router->call($path, $kirby->request()->method(), function ($route) use ($language) {
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
