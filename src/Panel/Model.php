<?php

namespace Kirby\Panel;

use Kirby\Form\Form;

/**
 * Provides information about the model for the Panel
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
abstract class Model
{
    /**
     * @var \Kirby\Cms\ModelWithContent
     */
    protected $model;

    /**
     * @param \Kirby\Cms\ModelWithContent $model
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * Get the content values for the model
     *
     * @return array
     */
    public function content(): array
    {
        return Form::for($this->model)->values();
    }

    /**
     * Returns the drag text from a custom callback
     * if the callback is defined in the config
     * @internal
     *
     * @param string $type markdown or kirbytext
     * @param mixed ...$args
     * @return string|null
     */
    public function dragTextFromCallback(string $type, ...$args): ?string
    {
        $option   = 'panel.' . $type . '.' . $this->model::CLASS_ALIAS . 'DragText';
        $callback = option($option);

        if (
            empty($callback) === false &&
            is_a($callback, 'Closure') === true &&
            ($dragText = $callback($this->model, ...$args)) !== null
        ) {
            return $dragText;
        }

        return null;
    }

    /**
     * Returns the correct drag text type
     * depending on the given type or the
     * configuration
     *
     * @internal
     *
     * @param string|null $type (`auto`|`kirbytext`|`markdown`)
     * @return string
     */
    public function dragTextType(string $type = null): string
    {
        $type = $type ?? 'auto';

        if ($type === 'auto') {
            $type = option('panel.kirbytext', true) ? 'kirbytext' : 'markdown';
        }

        return $type === 'markdown' ? 'markdown' : 'kirbytext';
    }

    /**
     * Returns the Panel icon definition
     *
     * @internal
     *
     * @param array|null $params
     * @return array
     */
    public function icon(array $params = null): array
    {
        $defaults = [
            'type'  => 'page',
            'ratio' => null,
            'back'  => 'pattern',
            'color' => '#c5c9c6',
        ];

        return array_merge($defaults, $params ?? []);
    }

    /**
     * Returns the Panel image definition
     *
     * @internal
     *
     * @param string|array|false|null $settings
     * @return array|null
     */
    public function image($settings = null): ?array
    {
        $defaults = [
            'ratio' => '3/2',
            'back'  => 'pattern',
            'cover' => false
        ];

        // switch the image off
        if ($settings === false) {
            return null;
        }

        if (is_string($settings) === true) {
            // use defined icon in blueprint
            if ($settings === 'icon') {
                return [];
            }

            $settings = [
                'query' => $settings
            ];
        }

        if ($image = $this->imageSource($settings['query'] ?? null)) {

            // main url
            $settings['url'] = $image->url();

            // only create srcsets for actual File objects
            if (is_a($image, 'Kirby\Cms\File') === true) {

                // pixelated transparent image placeholder
                $placeholder = 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw';

                // for cards
                $settings['cards'] = [
                    'url' => $placeholder,
                    'srcset' => $image->srcset([
                        352,
                        864,
                        1408,
                    ])
                ];

                // for lists
                if (($settings['cover'] ?? false) === false) {
                    $settings['list'] = [
                        'url' => $placeholder,
                        'srcset' => $image->srcset([
                            38,
                            76
                        ])
                    ];
                } else {
                    $settings['list'] = [
                        'url' => $placeholder,
                        'srcset' => $image->srcset([
                            '1x' => [
                                'width' => 38,
                                'height' => 38,
                                'crop' => 'center'
                            ],
                            '2x' => [
                                'width' => 76,
                                'height' => 76,
                                'crop' => 'center'
                            ],
                        ])
                    ];
                }
            }
        }
        unset($settings['query']);

        return array_merge($defaults, (array)$settings);
    }

    /**
     * Returns the image file object based on provided query
     *
     * @internal
     *
     * @param string|null $query
     * @return \Kirby\Cms\File|\Kirby\Filesystem\Asset|null
     */
    protected function imageSource(?string $query = null)
    {
        $image = $this->model->query($query ?? null);

        // validate the query result
        if (
            is_a($image, 'Kirby\Cms\File') === true ||
            is_a($image, 'Kirby\Filesystem\Asset') === true
        ) {
            return $image;
        }

        return null;
    }

    /**
     * Returns an array of all actions
     * that can be performed in the Panel
     * This also checks for the lock status
     *
     * @param array $unlock An array of options that will be force-unlocked
     * @return array
     */
    public function options(array $unlock = []): array
    {
        $options = $this->model->permissions()->toArray();

        if ($this->model->isLocked()) {
            foreach ($options as $key => $value) {
                if (in_array($key, $unlock)) {
                    continue;
                }

                $options[$key] = false;
            }
        }

        return $options;
    }

    /**
     * Returns the full path without leading slash
     *
     * @return string
     */
    abstract public function path(): string;

    /**
     * Returns link and tooltip
     * used for prev/next navigation
     *
     * @internal
     *
     * @param string $tooltip
     * @return array|null
     */
    public function prevnext($tooltip = 'title'): ?array
    {
        return [
            'link'    => $this->url(true),
            'tooltip' => (string)$this->model->$tooltip()
        ];
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
        $blueprint = $this->model->blueprint();
        $tabs      = $blueprint->tabs();

        if (!$tab = $blueprint->tab(param('tab'))) {
            $tab = $tabs[0] ?? [];
        }

        return [
            'blueprint'   => $blueprint->name(),
            'permissions' => $this->model->permissions()->toArray(),
            'tab'         => $tab,
            'tabs'        => $tabs,
        ];
    }

    /**
     * Returns the data array for
     * this model's Panel routes
     *
     * @internal
     *
     * @return array
     */
    abstract public function route(): array;

    /**
     * Returns the url to the editing view
     * in the Panel
     *
     * @internal
     *
     * @param bool $relative
     * @return string
     */
    public function url(bool $relative = false): string
    {
        if ($relative === true) {
            return '/' . $this->path();
        }

        return $this->model->kirby()->url('panel') . '/' . $this->path();
    }
}
