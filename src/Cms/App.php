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
use Kirby\Image\Darkroom\GdLib as Darkroom;
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
                    return new Darkroom([
                        'quality' => 80
                    ]);
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
            'root' => [
                'type' => 'array',
                'default' => function () {

                    $index = realpath(__DIR__ . '/../../../');

                    return [
                        '/'           => $index,
                        'kirby'       => realpath(__DIR__ . '/../../'),
                        'media'       => $index . '/media',
                        'content'     => $index . '/content',
                        'controllers' => $index . '/site/controllers',
                        'accounts'    => $index . '/site/accounts',
                        'snippets'    => $index . '/site/snippets',
                        'templates'   => $index . '/site/templates',
                        'blueprints'  => $index . '/site/blueprints',
                        'panel'       => $index . '/panel'
                    ];
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
                'default' => function () {
                    return trim($this->server()->get('path_info'), '/');
                }
            ],
            'url' => [
                'type'    => 'array',
                'default' => function () {
                    return [
                        '/'     => $index = Url::index(),
                        'media' => $index . '/media',
                        'panel' => $index . '/panel',
                        'api'   => $index . '/api'
                    ];
                }
            ],
        ]);

        Object::use('store', $this->store());
        Object::use('media', $this->media());
        Object::use('rules', $this->rules());
        Object::use('perms', $this->perms());
        Object::use('kirby', $this);

        Collection::use('pagination', function ($options) {
            return new Pagination([
                'total' => $this->count(),
                'limit' => $options['limit'] ?? 10,
                'page'  => $options['page'] ?? App::instance()->request()->query()->get('page'),
                'url'   => Url\Query::strip(Url::current())
            ]);
        });

        static::$instance = $this;

    }

    public static function instance()
    {
        return static::$instance;
    }

    public function url(string $url = '/')
    {
        return $this->prop('url')[$url];
    }

    public function user($id = null)
    {
        if ($id === null) {
            // TODO: return the logged in user
            throw new Exception('Not yet implemented');
        }

        return $this->users()->find($id);
    }

    public function root(string $root = '/')
    {
        return $this->prop('root')[$root];
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

        return $this->view($this->site()->errorPage());

    }

}
