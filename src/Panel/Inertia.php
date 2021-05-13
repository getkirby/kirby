<?php

namespace Kirby\Panel;

use Closure;
use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Cms\Form;
use Kirby\Cms\Pagination;
use Kirby\Cms\User;
use Kirby\Http\Response;
use Kirby\Http\Url;
use Kirby\Toolkit\Collection;
use Kirby\Toolkit\Str;

/**
 * Custom Inertia implementation, providing
 * all relevant methods and info on Panel
 * views and what props/data to pass to them
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Inertia
{
    /**
     * Kirby app instance
     *
     * @var \Kirby\Cms\App
     */
    public static $kirby;

    /**
     * Current request
     *
     * @var \Kirby\Http\Request
     */
    public static $request;

    /**
     * Callback function to lazily return
     * information shared across the Panel
     * app
     *
     * @var \Closure
     */
    public static $shared;

    /**
     * Version hash of Kirby installation
     *
     * @var string
     */
    public static $version;

    /**
     * General views definitions
     *
     * @var array
     */
    public static $views;


    /**
     * Returns the avatar URL for a user
     * if the user has one
     *
     * @param \Kirby\Cms\User $user
     * @return string|null
     */
    public static function avatar(User $user): ?string
    {
        if ($avatar = $user->avatar()) {
            return $avatar->url();
        }

        return null;
    }

    /**
     * Retrieve a data array for the Collection -
     * either directly as array of values or paginated
     *
     * @param \Kirby\Toolkit\Collection $collection
     * @param Closure $map
     * @param array|null $pagination
     * @return array
     */
    public static function collection(Collection $collection, Closure $map, ?array $pagination = null): array
    {
        if (empty($pagination) === true) {
            return static::toValues($collection, $map);
        }

        $collection = $collection->paginate($pagination);

        return [
            'data'       => static::toValues($collection, $map),
            'pagination' => static::pagination($collection->pagination())
        ];
    }

    /**
     * Get the content values for a specified model
     *
     * @param \Kirby\Cms\ModelWithContent $model
     * @return array
     */
    public static function content($model): array
    {
        return Form::for($model)->values();
    }

    /**
     * Renders the error view with provided message
     *
     * @param string|array $message
     * @return \Kirby\Http\Response
     */
    public static function error($message)
    {
        return static::render('ErrorView', [
            '$props' => [
                'error'  => $message,
                'layout' => static::$kirby->user() ? 'inside' : 'outside',
            ],
            '$view' => static::view('error'),
        ]);
    }

    /**
     * Returns array of view information for
     * the file. If file does not exist, returns
     * erro string
     *
     * @param mixed $file
     * @return array|string
     */
    public static function file($file)
    {
        if (!$file) {
            return t('error.file.undefined');
        }

        $parent   = $file->parent();
        $type     = $parent::CLASS_ALIAS;
        $siblings = $file->templateSiblings()->sortBy('sort', 'asc', 'filename', 'asc');

        return [
            'component' => 'FileView',
            'props' => static::model($file, [
                'file' => [
                    'content'    => Inertia::content($file),
                    'dimensions' => $file->dimensions()->toArray(),
                    'extension'  => $file->extension(),
                    'filename'   => $file->filename(),
                    'id'         => $file->id(),
                    'mime'       => $file->mime(),
                    'niceSize'   => $file->niceSize(),
                    'parent'     => $file->parent()->panelPath(),
                    'panelImage' => $file->panelImage(),
                    'previewUrl' => $file->previewUrl(),
                    'url'        => $file->url(),
                    'template'   => $file->template(),
                    'type'       => $file->type(),
                ],
                'next' => function () use ($file, $siblings): ?array {
                    $next = $siblings->nth($siblings->indexOf($file) + 1);
                    return Inertia::prevnext($next, 'filename');
                },
                'prev' => function () use ($file, $siblings): ?array {
                    $prev = $siblings->nth($siblings->indexOf($file) - 1);
                    return Inertia::prevnext($prev, 'filename');
                },
            ]),
            'view' => [
                'breadcrumb' => function () use ($file, $parent, $type): array {
                    switch ($type) {
                        case 'site':
                            $breadcrumb = [];
                            break;
                        case 'user':
                            $breadcrumb = [
                                [
                                    'label' => $parent->username(),
                                    'link'  => $parent->panelUrl(true)
                                ]
                            ];
                            break;
                        case 'page':
                            $breadcrumb = static::toValues($file->parents()->flip(), function ($parent) {
                                return [
                                    'label' => $parent->title()->toString(),
                                    'link'  => $parent->panelUrl(true),
                                ];
                            });
                    }

                    // add the file
                    $breadcrumb[] = [
                        'label' => $file->filename(),
                        'link'  => $file->panelUrl(true),
                    ];

                    return $breadcrumb;
                },
                'id'    => $type === 'user' ? 'users' : 'site',
                'title' => $file->filename(),
            ]
        ];
    }

    /**
     * Creates json response for Inertia
     *
     * @param array $response
     * @return \Kirby\Http\Response
     */
    public static function json(array $response = [])
    {
        return Response::json($response, null, null, [
            'Vary'      => 'Accept',
            'X-Inertia' => 'true'
        ]);
    }

    /**
     * Resolves lazy props
     *
     * @param array $props
     * @return array
     */
    public static function lazy(array $props): array
    {
        array_walk_recursive($props, function (&$prop) {
            if (is_a($prop, Closure::class)) {
                $prop = $prop();
            }
        });

        return $props;
    }

    /**
     * Merges blueprint and tab information
     * for the model into the array
     *
     * @param \Kirby\Cms\ModelWithContent $model
     * @param array $merge
     * @return array
     */
    public static function model($model, array $merge = []): array
    {
        $blueprint = $model->blueprint();
        $tabs      = $blueprint->tabs();

        if (!$tab = $blueprint->tab(param('tab'))) {
            $tab = $tabs[0] ?? [];
        }

        return array_merge([
            'blueprint'   => $blueprint->name(),
            'permissions' => $model->permissions()->toArray(),
            'tab'         => $tab,
            'tabs'        => $tabs,
        ], $merge);
    }

    /**
     * Creates pagination array for
     * API response
     *
     * @param \Kirby\Toolkit\Pagination $pagination
     * @return array
     */
    public static function pagination(Pagination $pagination): array
    {
        return [
            'limit' => $pagination->limit(),
            'page'  => $pagination->page(),
            'total' => $pagination->total(),
        ];
    }

    /**
     * Returns prevnext information
     * for model
     *
     * @param \Kirby\Cms\ModelWithContent|null $model
     * @param string $tooltip
     * @return array|null
     */
    public static function prevnext($model = null, $tooltip = 'title'): ?array
    {
        if ($model) {
            return [
                'link'    => $model->panel()->url(true),
                'tooltip' => (string)$model->$tooltip()
            ];
        }

        return null;
    }

    /**
     * Creates props array for the component
     *
     * @param string $component
     * @param array $props
     * @return array
     */
    public static function props(string $component, array $props = []): array
    {
        $kirby = static::$kirby;

        // merge with shared props
        $shared = [
            '$config' => [
                'debug'     => $kirby->option('debug'),
                'kirbytext' => $kirby->option('panel.kirbytext', true),
                'search'    => [
                    'limit' => $kirby->option('panel.search.limit', 10),
                    'type'  => $kirby->option('panel.search.type', 'pages')
                ],
                'translation' => $kirby->option('panel.language', 'en'),
            ],
            '$language' => function () use ($kirby) {
                if (
                    $kirby->option('languages') === true &&
                    $language = $kirby->language()
                ) {
                    return [
                        'code' => $language->code(),
                        'name' => $language->name(),
                    ];
                }
            },
            '$languages' => function () use ($kirby): array {
                if ($kirby->option('languages') === true) {
                    return static::toValues($kirby->languages(), function ($language) {
                        return [
                            'code'    => $language->code(),
                            'default' => $language->isDefault(),
                            'name'    => $language->name(),
                        ];
                    });
                }

                return [];
            },
            '$permissions' => function () use ($kirby) {
                if ($user = $kirby->user()) {
                    return $user->role()->permissions()->toArray();
                }
            },
            '$props' => [],
            '$system' => [
                'ascii'     => Str::$ascii,
                'csrf'      => $kirby->option('api.csrf') ?? csrf(),
                'isLocal'   => $kirby->system()->isLocal(),
                'license'   => $kirby->system()->license(),
                'multilang' => $kirby->option('languages', false) !== false,
                'slugs'     => Str::$language,
            ],
            '$translation' => function () use ($kirby) {
                if ($user = $kirby->user()) {
                    $translation = $kirby->translation($user->language());
                } else {
                    $translation = $kirby->translation($kirby->option('panel.language', 'en'));
                }

                if (!$translation) {
                    $translation = $kirby->translation('en');
                }

                return [
                    'code'      => $translation->code(),
                    'data'      => $translation->dataWithFallback(),
                    'direction' => $translation->direction(),
                    'name'      => $translation->name(),
                ];
            },
            '$urls' => [
                'api'  => $kirby->url('api'),
                'site' => $kirby->url('index')
            ],
            '$user' => function () use ($kirby) {
                if ($user = $kirby->user()) {
                    return [
                        'email'       => $user->email(),
                        'id'          => $user->id(),
                        'language'    => $user->language(),
                        'permissions' => $user->role()->permissions()->toArray(),
                        'role'        => $user->role()->id(),
                        'username'    => $user->username(),
                    ];
                }

                return null;
            },
            '$views' => static::$views
        ];

        $props = array_merge($shared, $props);

        // is it a partial request?
        $only = Str::split(static::$request->header('X-Inertia-Partial-Data'));

        // only include new props in array, if partial request
        if (
            empty($only) === false &&
            static::$request->header('X-Inertia-Partial-Component') === $component
        ) {
            foreach ($props as $key => $value) {
                if (in_array($key, $only) === false) {
                    unset($props[$key]);
                }
            }
        }

        // resolve lazy props
        return static::lazy($props);
    }

    /**
     * Render the request
     *
     * @param string $component
     * @param array $props
     * @return \Kirby\Http\Response
     */
    public static function render(string $component, array $props = [])
    {
        // prepare props
        $props    = static::props($component, $props);

        // prepare response
        $response = static::response($component, $props);

        // is JSON required?
        if (
            static::$request->method() === 'GET' &&
            (
                static::$request->header('X-Inertia') ||
                static::$request->get('json')
            )
        ) {
            return static::json($response);
        }

        return Panel::render(static::$kirby, $response);
    }

    /**
     * Creates $inertia response array
     *
     * @param string $component
     * @param array $props
     * @return array
     */
    public static function response(string $component, array $props = []): array
    {
        // inject the inertia config as props
        $props['$component'] = $component;
        $props['$url']       = Url::current();
        $props['$version']   = static::$version;

        return [
            'component' => $props['$component'],
            'props'     => $props,
            'url'       => $props['$url'],
            'version'   => $props['$version']
        ];
    }

    /**
     * Returns all values of a Collection
     * by applying a Closure to each item
     *
     * @param \Kirby\Toolkit\Collection $collection
     * @param Closure $map
     * @return array
     */
    public static function toValues(Collection $collection, Closure $map): array
    {
        return array_values($collection->toArray($map));
    }

    /**
     * Creates view array
     *
     * @param string $id
     * @param array $props
     * @return array
     */
    public static function view(string $id, array $props = []): array
    {
        // get view-specific defaults
        $view = static::$views[$id] ?? static::$views['site'];

        // create default array
        $defaults = [
            'breadcrumb'      => $props['breadcrumb'] ?? [],
            'breadcrumbLabel' => $view['breadcrumbLabel'] ?? $view['label'],
            'icon'            => $view['icon'],
            'id'              => $view['id'],
            'label'           => $view['label'],
            'link'            => $view['link'],
            'menu'            => $view['menu'] ?? true,
            'path'            => static::$kirby->request()->path()->slice(1)->toString(),
            'search'          => $view['search'] ?? static::$kirby->option('panel.search.type', 'pages'),
            'title'           => $view['label'],
        ];

        // merge props with defaults
        $props = array_replace_recursive($defaults, $props);

        // resolve lazy props
        return static::lazy($props);
    }
}

Inertia::$views = [
    'site' => [
        'breadcrumbLabel' => function () {
            return Inertia::$kirby->site()->title()->or(t('view.site'))->toString();
        },
        'icon'            => 'home',
        'id'              => 'site',
        'label'           => t('view.site'),
        'link'            => 'site',
        'search'          => 'pages'
    ],
    'users' => [
        'icon'   => 'users',
        'id'     => 'users',
        'label'  => t('view.users'),
        'link'   => 'users',
        'search' => 'users'
    ],
    'settings' => [
        'icon'  => 'settings',
        'id'    => 'settings',
        'label' => t('view.settings'),
        'link'  => 'settings'
    ],
    'account' => [
        'icon'   => 'account',
        'id'     => 'account',
        'label'  => t('view.account'),
        'link'   => 'account',
        'menu'   => false,
        'search' => 'users'
    ],
    'error' => [
        'icon'  => 'alert',
        'id'    => 'error',
        'label' => 'Error',
        'menu'  => false,
        'link'  => 'error'
    ],
    'installation' => [
        'icon'  => 'settings',
        'id'    => 'installation',
        'label' => t('view.installation'),
        'menu'  => false,
        'link'  => 'installation'
    ],
    'login' => [
        'icon'  => 'user',
        'id'    => 'login',
        'label' => t('login'),
        'menu'  => false,
        'link'  => 'login'
    ]
];
