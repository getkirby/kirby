<?php

namespace Kirby\Cms;

use Closure;
use Exception;
use Kirby\FileSystem\File as Template;
use Kirby\FileSystem\File as Controller;
use Kirby\Http\Request;
use Kirby\Http\Response;
use Kirby\Http\Router;
use Kirby\Http\Server;
use Kirby\Image\Darkroom;
use Kirby\Image\Darkroom\GdLib;
use Kirby\Text\Markdown;
use Kirby\Text\Smartypants;
use Kirby\Text\Tags as Kirbytext;
use Kirby\Toolkit\Url;
use Kirby\Toolkit\View;


class App extends Object
{

    protected static $instance;

    public function __construct(array $props = [])
    {

        parent::__construct($props, [
            'darkroom' => [
                'type'    => Darkroom::class,
                'default' => function () {
                    return new GdLib([
                        'quality' => 80
                    ]);
                }
            ],
            'fieldMethods' => [
                'type'    => 'array',
                'default' => function () {
                    return require $this->root('kirby') . '/extensions/methods.php';
                }
            ],
            'kirbytext' => [
                'type'    => Kirbytext::class,
                'default' => function () {
                    return new Kirbytext([
                        'breaks' => true
                    ]);
                }
            ],
            'markdown' => [
                'type'    => Markdown::class,
                'default' => function () {
                    return new Markdown([
                        'breaks' => true
                    ]);
                }
            ],
            'media' => [
                'type'    => Media::class,
                'default' => function () {
                    return new Media([
                        'darkroom' => $this->darkroom(),
                        'root'     => $this->root('media'),
                        'url'      => $this->url('media')
                    ]);
                },
            ],
            'perms' => [
                'type'    => Perms::class,
                'default' => function () {
                    return require $this->root('kirby') . '/config/perms.php';
                }
            ],
            'request' => [
                'type'    => Request::class,
                'default' => function () {
                    return new Request;
                }
            ],
            'roots' => [
                'type'    => Roots::class,
                'default' => function () {
                    return new Roots();
                }
            ],
            'router' => [
                'type'    => Router::class,
                'default' => function () {
                    return new Router($this->routes());
                }
            ],
            'routes' => [
                'type'    => 'array',
                'default' => function () {
                    return require $this->root('kirby') . '/config/routes.php';
                }
            ],
            'rules' => [
                'type'    => Rules::class,
                'default' => function () {
                    return require $this->root('kirby') . '/config/rules.php';
                }
            ],
            'schema' => [
                'type'    => 'array',
                'default' => function () {
                    return require $this->root('kirby') . '/config/schema.php';
                }
            ],
            'server' => [
                'type'    => Server::class,
                'default' => function () {
                    return new Server;
                }
            ],
            'site' => [
                'type' => Site::class,
                'default' => function () {

                    $site = new Site([
                        'url'  => $this->url(),
                        'root' => $this->root('content')
                    ]);

                    Page::use('site', $site);
                    File::use('site', $site);

                    return $site;

                }
            ],
            'smartypants' => [
                'type'    => Smartypants::class,
                'default' => function () {
                    return new Smartypants;
                }
            ],
            'store' => [
                'type'    => Store::class,
                'default' => function () {
                    return require $this->root('kirby') . '/config/store.php';
                }
            ],
            'system' => [
                'type'    => System::class,
                'default' => function () {
                    return new System($this);
                }
            ],
            'path' => [
                'type'    => 'string',
                'default' => function (): string {

                    // TODO: move this to a nicer place
                    $uri    = parse_url($this->server()->get('request_uri'), PHP_URL_PATH);
                    $script = dirname($this->server()->get('script_name'));
                    $path   = preg_replace('!^' . preg_quote($script) . '!', '', $uri);

                    return trim($path, '/');
                }
            ],
            'urls' => [
                'type'    => Urls::class,
                'default' => function () {
                    return new Urls([
                        'index' => Url::index(),
                    ]);
                }
            ],
        ]);

        $kirby = $this;

        Object::use('kirby', $kirby);

        Object::use('store', function () use ($kirby) {
            return $kirby->store();
        });

        Object::use('media', function () use ($kirby) {
            return $kirby->media();
        });

        Object::use('rules', function () use ($kirby) {
            return $kirby->rules();
        });

        Object::use('perms', function () use ($kirby) {
            return $kirby->perms();
        });

        Collection::use('pagination', function ($options) {
            return new Pagination([
                'total' => $this->count(),
                'limit' => $options['limit'] ?? 20,
                'page'  => $options['page'] ?? App::instance()->request()->query()->get('page'),
                'url'   => Url\Query::strip(Url::current())
            ]);
        });

        // register all field methods
        Field::methods($this->fieldMethods());

        static::$instance = $kirby;

    }

    public static function instance()
    {
        return static::$instance;
    }

    public function url(string $url = 'index')
    {
        return $this->urls()->get($url);
    }

    public function user($id = null)
    {
        if ($id === null) {
            // TODO: return the logged in user
            throw new Exception('Not yet implemented');
        }

        return $this->users()->find($id);
    }

    public function root(string $root = 'index')
    {
        return $this->roots()->get($root);
    }

    public function users(): Users
    {
        return $this->store()->commit('users');
    }

    public function controller(string $name, array $arguments = []): array
    {

        $controller = new Controller($this->root('controllers') . '/' . $name . '.php');

        if ($controller->exists() === false) {
            return [];
        }

        return (array)(require $controller->root())(...$arguments);

    }

    public function view(Page $page): Response
    {

        $site = $this->site();
        $site->set('page', $page);

        $viewData = [
            'site'  => $site,
            'pages' => $pages = $site->children(),
            'page'  => $page
        ];

        // TODO: put this in a template component
        $template = new Template($this->root('templates') . '/' . ($page->template() ?? 'default') . '.php');

        // switch to the default template if the file cannot be found
        if ($template->exists() === false) {
            $template = new Template($this->root('templates') . '/default.php');
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

        try {
            return $this->view($this->site()->errorPage());
        } catch (Exception $e) {
            throw new Exception('The error page is missing or cannot be loaded correctly. Please make sure to add it to your conent folder.');
        }

    }

}
