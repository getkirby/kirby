<?php

namespace Kirby\Cms;

use Kirby\App\App as BaseApp;
use Kirby\FileSystem\File;
use Kirby\Http\Response;
use Kirby\Toolkit\View;

class App extends BaseApp
{

    protected static $instance;

    public function controller(string $name, array $arguments = []): array
    {

        $controller = new File($this->root('controllers') . '/' . $name . '.php');

        if ($controller->exists() === false) {
            return [];
        }

        return (array)(require $controller->root())(...$arguments);

    }

    public function view(Page $page): Response
    {

        $viewData = [
            'site'  => $site  = $this->site(),
            'pages' => $pages = $site->children(),
            'page'  => $page,
        ];

        // TODO: put this in a template component
        $template = new File($this->root('templates') . '/' . ($page->template() ?? 'default') . '.php');

        // switch to the default template if the file cannot be found
        if ($template->exists() === false) {
            $template = new File($this->root('templates') . '/default.php');
        }

        // load controller data if a controller exists
        $controllerData = $this->controller($template->name(), array_values($viewData));

        View::globals(array_merge($controllerData, $viewData));

        // create the template
        $view = new View($template->realpath());

        // render the response
        return new Response($view->toString());

    }

    public function response(): Response {

        // fetch the page at the current path
        $response = $this->router()->call($this->path(), $this->request()->method());

        if (is_a($response, Response::class)) {
            return $response;
        }

        if (is_a($response, Page::class)) {
            return $this->view($response);
        }

        return $this->view($this->site()->find('error'));

    }

}
