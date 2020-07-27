<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Toolkit\Collection;
use Kirby\Toolkit\Pagination;
use Kirby\Toolkit\Str;

class Inertia extends \Kirby\Inertia\Inertia
{

    public static $kirby;
    public static $views;

    public static function avatar(User $user): ?string
    {
        if ($avatar = $user->avatar()) {
            return $avatar->url();
        }

        return null;
    }

    public static function collect(Collection $collection, Closure $map): array
    {
        return array_values($collection->toArray($map));
    }

    public static function collection(Collection $collection, Closure $map, array $pagination = null)
    {
        if (empty($pagination) === true) {
            return static::collect($collection, $map);
        }

        $collection = $collection->paginate($pagination);

        return [
            'data'       => static::collect($collection, $map),
            'pagination' => static::pagination($collection->pagination())
        ];
    }

    public static function content($model): array
    {
        return Form::for($model)->values();
    }

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
            'props' => Inertia::model($file, [
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
                'next' => function () use ($file, $siblings) {
                    $next = $siblings->nth($siblings->indexOf($file) + 1);
                    return Inertia::prevnext($next, 'filename');
                },
                'prev' => function () use ($file, $siblings) {
                    $prev = $siblings->nth($siblings->indexOf($file) - 1);
                    return Inertia::prevnext($prev, 'filename');
                },
            ]),
            'view' => [
                'breadcrumb' => function () use ($file, $parent, $type) {

                    switch ($type) {
                        case "site":
                            $breadcrumb = [];
                            break;
                        case "user":
                            $breadcrumb = [
                                [
                                    'label' => $parent->username(),
                                    'link'  => $parent->panelUrl(true)
                                ]
                            ];
                            break;
                        case "page":
                            $breadcrumb = static::collect($file->parents()->flip(), function ($parent) {
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

    public static function model($model, array $merge = [])
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

    public static function pagination(Pagination $pagination): array
    {
        return [
            'limit' => $pagination->limit(),
            'page'  => $pagination->page(),
            'total' => $pagination->total(),
        ];
    }

    public static function prevnext($model = null, $tooltip = 'title'): ?array
    {
        if ($model) {
            return [
                'link'    => $model->panelUrl(true),
                'tooltip' => (string)$model->$tooltip()
            ];
        }

        return null;
    }

    public static function response(string $component, array $props = [])
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

    public static function setup(App $kirby)
    {
        Pagination::$validate = false;

        static::$kirby   = $kirby;
        static::$request = $kirby->request();
        static::$version = $kirby->versionHash();

        static::$view = function ($inertia) use ($kirby) {
            return Panel::render($kirby, $inertia);
        };

        static::$views = [
            'site' => [
                'breadcrumbLabel' => $kirby->site()->title()->or(t('view.site'))->toString(),
                'icon'            => 'home',
                'id'              => 'site',
                'label'           => t('view.site'),
                'link'            => 'site',
                'menu'            => true,
                'search'          => 'pages'
            ],
            'users' => [
                'icon'   => 'users',
                'id'     => 'users',
                'label'  => t('view.users'),
                'link'   => 'users',
                'menu'   => true,
                'search' => 'users'
            ],
            'settings' => [
                'icon'  => 'settings',
                'id'    => 'settings',
                'label' => t('view.settings'),
                'menu'  => true,
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

        static::$shared = function () use ($kirby) {

            return [
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
                    if ($kirby->option('languages') === true && $language = $kirby->language()) {
                        return [
                            'code' => $language->code(),
                            'name' => $language->name(),
                        ];
                    }
                },
                '$languages' => function () use ($kirby): array {
                    if ($kirby->option('languages') === true) {
                        return static::collect($kirby->languages(), function ($language) {
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
        };

    }

    public static function view(string $id, array $props = []): array
    {
        $view = static::$views[$id] ?? static::$views['site'];

        $defaults = [
            'breadcrumb'      => $props['breadcrumb'] ?? [],
            'breadcrumbLabel' => $view['breadcrumbLabel'] ?? $view['label'],
            'icon'            => $view['icon'],
            'id'              => $view['id'],
            'label'           => $view['label'],
            'link'            => $view['link'],
            'menu'            => $view['menu'],
            'path'            => static::$kirby->request()->path()->slice(1)->toString(),
            'search'          => static::$kirby->option('panel.search.type', 'pages'),
            'title'           => $view['label'],
        ];

        return array_replace_recursive($defaults, $props);
    }

}
