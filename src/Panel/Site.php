<?php

namespace Kirby\Panel;

/**
 * Provides information about the site model for the Panel
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Site extends Model
{
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
            'icon' => 'home',
            'text' => $this->model->title()->value(),
        ] + parent::dropdownOption();
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
        if ($query === null) {
            $query = 'site.image';
        }

        return parent::imageSource($query);
    }

    /**
     * Returns the full path without leading slash
     *
     * @return string
     */
    public function path(): string
    {
        return 'site';
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
        return array_merge(parent::props(), [
            'blueprint' => 'site',
            'model' => [
                'content'    => $this->content(),
                'link'       => $this->url(true),
                'previewUrl' => $this->model->previewUrl(),
                'title'      => $this->model->title()->toString(),
            ]
        ]);
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
        return [
            'component' => 'k-site-view',
            'props'     => $this->props()
        ];
    }
}
