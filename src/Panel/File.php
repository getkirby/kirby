<?php

namespace Kirby\Panel;

use Throwable;

/**
 * Provides information about the file model for the Panel
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class File extends Model
{
    /**
     * @var \Kirby\Cms\File
     */
    protected $model;

    /**
     * Breadcrumb array
     *
     * @return array
     */
    public function breadcrumb(): array
    {
        $breadcrumb = [];
        $parent     = $this->model->parent();

        switch ($parent::CLASS_ALIAS) {
            case 'user':
                // The breadcrumb is not necessary
                // on the account view
                if ($parent->isLoggedIn() === false) {
                    $breadcrumb[] = [
                        'label' => $parent->username(),
                        'link'  => $parent->panel()->url(true)
                    ];
                }
                break;
            case 'page':
                $breadcrumb = $this->model->parents()->flip()->values(fn ($parent) => [
                    'label' => $parent->title()->toString(),
                    'link'  => $parent->panel()->url(true),
                ]);
        }

        // add the file
        $breadcrumb[] = [
            'label' => $this->model->filename(),
            'link'  => $this->url(true),
        ];

        return $breadcrumb;
    }

    /**
     * Provides a kirbytag or markdown
     * tag for the file, which will be
     * used in the panel, when the file
     * gets dragged onto a textarea
     *
     * @internal
     * @param string|null $type (`auto`|`kirbytext`|`markdown`)
     * @param bool $absolute
     * @return string
     */
    public function dragText(string $type = null, bool $absolute = false): string
    {
        $type = $this->dragTextType($type);
        $url  = $absolute ? $this->model->id() : $this->model->filename();

        if ($dragTextFromCallback = $this->dragTextFromCallback($type, $url)) {
            return $dragTextFromCallback;
        }

        if ($type === 'markdown') {
            if ($this->model->type() === 'image') {
                return '![' . $this->model->alt() . '](' . $url . ')';
            }

            return '[' . $this->model->filename() . '](' . $url . ')';
        }

        if ($this->model->type() === 'image') {
            return '(image: ' . $url . ')';
        }
        if ($this->model->type() === 'video') {
            return '(video: ' . $url . ')';
        }

        return '(file: ' . $url . ')';
    }

    /**
     * Provides options for the file dropdown
     *
     * @param array $options
     * @return array
     */
    public function dropdown(array $options = []): array
    {
        $defaults = [
            'view'   => get('view'),
            'update' => get('update'),
            'delete' => get('delete')
        ];

        $options     = array_merge($defaults, $options);
        $file        = $this->model;
        $permissions = $this->options(['preview']);
        $view        = $options['view'] ?? 'view';
        $url         = $this->url(true);
        $result      = [];

        if ($view === 'list') {
            $result[] = [
                'link'   => $file->previewUrl(),
                'target' => '_blank',
                'icon'   => 'open',
                'text'   => t('open')
            ];
            $result[] = '-';
        }

        $result[] = [
            'dialog'   => $url . '/changeName',
            'icon'     => 'title',
            'text'     => t('rename'),
            'disabled' => $this->isDisabledDropdownOption('changeName', $options, $permissions)
        ];

        $result[] = [
            'click'    => 'replace',
            'icon'     => 'upload',
            'text'     => t('replace'),
            'disabled' => $this->isDisabledDropdownOption('replace', $options, $permissions)
        ];

        if ($view === 'list') {
            $result[] = '-';
            $result[] = [
                'dialog'   => $url . '/changeSort',
                'icon'     => 'sort',
                'text'     => t('file.sort'),
                'disabled' => $this->isDisabledDropdownOption('update', $options, $permissions)
            ];
        }

        $result[] = '-';
        $result[] = [
            'dialog'   => $url . '/delete',
            'icon'     => 'trash',
            'text'     => t('delete'),
            'disabled' => $this->isDisabledDropdownOption('delete', $options, $permissions)
        ];

        return $result;
    }

    /**
     * Returns the setup for a dropdown option
     * which is used in the changes dropdown
     * for example.
     *
     * @return array
     */
    public function dropdownOption(): array
    {
        return [
            'icon' => 'image',
            'text' => $this->model->filename(),
        ] + parent::dropdownOption();
    }

    /**
     * Returns the Panel icon color
     *
     * @return string
     */
    protected function imageColor(): string
    {
        $types = [
            'image'    => 'orange-400',
            'video'    => 'yellow-400',
            'document' => 'red-400',
            'audio'    => 'aqua-400',
            'code'     => 'blue-400',
            'archive'  => 'white'
        ];

        $extensions = [
            'indd'  => 'purple-400',
            'xls'   => 'green-400',
            'xlsx'  => 'green-400',
            'csv'   => 'green-400',
            'docx'  => 'blue-400',
            'doc'   => 'blue-400',
            'rtf'   => 'blue-400'
        ];

        return $extensions[$this->model->extension()] ??
               $types[$this->model->type()] ??
               parent::imageDefaults()['icon'];
    }

    /**
     * Default settings for the file's Panel image
     *
     * @return array
     */
    protected function imageDefaults(): array
    {
        return array_merge(parent::imageDefaults(), [
            'color' => $this->imageColor(),
            'icon'  => $this->imageIcon(),
        ]);
    }

    /**
     * Returns the Panel icon type
     *
     * @return string
     */
    protected function imageIcon(): string
    {
        $types = [
            'image'    => 'file-image',
            'video'    => 'file-video',
            'document' => 'file-document',
            'audio'    => 'file-audio',
            'code'     => 'file-code',
            'archive'  => 'file-zip'
        ];

        $extensions = [
            'xls'   => 'file-spreadsheet',
            'xlsx'  => 'file-spreadsheet',
            'csv'   => 'file-spreadsheet',
            'docx'  => 'file-word',
            'doc'   => 'file-word',
            'rtf'   => 'file-word',
            'mdown' => 'file-text',
            'md'    => 'file-text'
        ];

        return $extensions[$this->model->extension()] ??
               $types[$this->model->type()] ??
               parent::imageDefaults()['color'];
    }

    /**
     * Returns the image file object based on provided query
     *
     * @internal
     * @param string|null $query
     * @return \Kirby\Cms\File|\Kirby\Filesystem\Asset|null
     */
    protected function imageSource(string $query = null)
    {
        if ($query === null && $this->model->isViewable()) {
            return $this->model;
        }

        return parent::imageSource($query);
    }

    /**
     * Returns an array of all actions
     * that can be performed in the Panel
     *
     * @param array $unlock An array of options that will be force-unlocked
     * @return array
     */
    public function options(array $unlock = []): array
    {
        $options = parent::options($unlock);

        try {
            // check if the file type is allowed at all,
            // otherwise it cannot be replaced
            $this->model->match($this->model->blueprint()->accept());
        } catch (Throwable $e) {
            $options['replace'] = false;
        }

        return $options;
    }

    /**
     * Returns the full path without leading slash
     *
     * @return string
     */
    public function path(): string
    {
        return 'files/' . $this->model->filename();
    }

    /**
     * Prepares the response data for file pickers
     * and file fields
     *
     * @param array|null $params
     * @return array
     */
    public function pickerData(array $params = []): array
    {
        $id   = $this->model->id();
        $name = $this->model->filename();

        if (empty($params['model']) === false) {
            $parent   = $this->model->parent();
            $uuid     = $parent === $params['model'] ? $name : $id;
            $absolute = $parent !== $params['model'];
        }

        $params['text'] ??= '{{ file.filename }}';

        return array_merge(parent::pickerData($params), [
            'filename' => $name,
            'dragText' => $this->dragText('auto', $absolute ?? false),
            'type'     => $this->model->type(),
            'url'      => $this->model->url(),
            'uuid'     => $uuid ?? $id,
        ]);
    }

    /**
     * Returns the data array for the
     * view's component props
     *
     * @internal
     *
     * @return array
     */
    public function props(): array
    {
        $file       = $this->model;
        $dimensions = $file->dimensions();
        $siblings   = $file->templateSiblings()->sortBy(
            'sort',
            'asc',
            'filename',
            'asc'
        );


        return array_merge(
            parent::props(),
            $this->prevNext(),
            [
                'blueprint' => $this->model->template() ?? 'default',
                'model' => [
                    'content'    => $this->content(),
                    'dimensions' => $dimensions->toArray(),
                    'extension'  => $file->extension(),
                    'filename'   => $file->filename(),
                    'link'       => $this->url(true),
                    'mime'       => $file->mime(),
                    'niceSize'   => $file->niceSize(),
                    'id'         => $id = $file->id(),
                    'parent'     => $file->parent()->panel()->path(),
                    'template'   => $file->template(),
                    'type'       => $file->type(),
                    'url'        => $file->url(),
                ],
                'preview' => [
                    'image'   => $this->image([
                        'back'  => 'transparent',
                        'ratio' => '1/1'
                    ], 'cards'),
                    'url'     => $url = $file->previewUrl(),
                    'details' => [
                        [
                            'title' => t('template'),
                            'text'  => $file->template() ?? '—'
                        ],
                        [
                            'title' => t('mime'),
                            'text'  => $file->mime()
                        ],
                        [
                            'title' => t('url'),
                            'text'  => $id,
                            'link'  => $url
                        ],
                        [
                            'title' => t('size'),
                            'text'  => $file->niceSize()
                        ],
                        [
                            'title' => t('dimensions'),
                            'text'  => $file->type() === 'image' ? $file->dimensions() . ' ' . t('pixel') : '—'
                        ],
                        [
                            'title' => t('orientation'),
                            'text'  => $file->type() === 'image' ? t('orientation.' . $dimensions->orientation()) : '—'
                        ],
                    ]
                ]
            ]
        );
    }

    /**
     * Returns navigation array with
     * previous and next file
     *
     * @internal
     *
     * @return array
     */
    public function prevNext(): array
    {
        $file     = $this->model;
        $siblings = $file->templateSiblings()->sortBy(
            'sort',
            'asc',
            'filename',
            'asc'
        );

        return [
            'next' => function () use ($file, $siblings): ?array {
                $next = $siblings->nth($siblings->indexOf($file) + 1);
                return $this->toPrevNextLink($next, 'filename');
            },
            'prev' => function () use ($file, $siblings): ?array {
                $prev = $siblings->nth($siblings->indexOf($file) - 1);
                return $this->toPrevNextLink($prev, 'filename');
            }
        ];
    }
    /**
     * Returns the url to the editing view
     * in the panel
     *
     * @param bool $relative
     * @return string
     */
    public function url(bool $relative = false): string
    {
        $parent = $this->model->parent()->panel()->url($relative);
        return $parent . '/' . $this->path();
    }

    /**
     * Returns the data array for
     * this model's Panel view
     *
     * @internal
     *
     * @return array
     */
    public function view(): array
    {
        $file = $this->model;

        return [
            'breadcrumb' => fn (): array => $file->panel()->breadcrumb(),
            'component'  => 'k-file-view',
            'props'      => $this->props(),
            'search'     => 'files',
            'title'      => $file->filename(),
        ];
    }
}
