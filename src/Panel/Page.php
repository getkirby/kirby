<?php

namespace Kirby\Panel;

/**
 * Provides information about the page model for the Panel
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Page extends Model
{
    /**
     * Breadcrumb array
     *
     * @return array
     */
    public function breadcrumb(): array
    {
        $parents = $this->model->parents()->flip()->merge($this->model);
        return $parents->values(function ($parent) {
            return [
                'label' => $parent->title()->toString(),
                'link'  => $parent->panel()->url(true),
            ];
        });
    }

    /**
     * Provides a kirbytag or markdown
     * tag for the page, which will be
     * used in the panel, when the page
     * gets dragged onto a textarea
     *
     * @internal
     * @param string|null $type (`auto`|`kirbytext`|`markdown`)
     * @return string
     */
    public function dragText(string $type = null): string
    {
        $type = $this->dragTextType($type);

        if ($callback = $this->dragTextFromCallback($type)) {
            return $callback;
        }

        if ($type === 'markdown') {
            return '[' . $this->model->title() . '](' . $this->model->url() . ')';
        }

        return '(link: ' . $this->model->id() . ' text: ' . $this->model->title() . ')';
    }


    /**
     * Returns the Panel icon definition
     * according to the blueprint settings
     *
     * @param array|null $params
     * @return array
     */
    public function icon(array $params = null): array
    {
        if ($icon = $this->model->blueprint()->icon()) {
            $params['type'] = $icon;
        }

        return parent::icon($params);
    }

    /**
     * Returns the escaped Id, which is
     * used in the panel to make routing work properly
     *
     * @return string
     */
    public function id(): string
    {
        return str_replace('/', '+', $this->model->id());
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
            $query = 'page.image';
        }

        return parent::imageSource($query);
    }

    /**
     * Returns the full path without leading slash
     *
     * @internal
     * @return string
     */
    public function path(): string
    {
        return 'pages/' . $this->id();
    }

    /**
     * Prepares the response data for page pickers
     * and page fields
     *
     * @param array|null $params
     * @return array
     */
    public function pickerData(array $params = []): array
    {
        $params['text'] = $params['text'] ?? '{{ page.title }}';

        return array_merge(parent::pickerData($params), [
            'dragText'    => $this->dragText(),
            'hasChildren' => $this->model->hasChildren(),
            'url'         => $this->model->url()
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
        $page = $this->model;

        return array_merge(parent::props(), [
            'model' => [
                'content'    => $this->content(),
                'id'         => $page->id(),
                'parent'     => $page->parentModel()->panel()->url(true),
                'previewUrl' => $page->previewUrl(),
                'status'     => $page->status(),
                'title'      => $page->title()->toString(),
            ],
            'next' => function () use ($page) {
                $next = $page
                    ->nextAll()
                    ->filterBy('intendedTemplate', $page->intendedTemplate())
                    ->filterBy('status', $page->status())
                    ->filterBy('isReadable', true)
                    ->first();
                return $next ? $next->panel()->prevnext('title') : null;
            },
            'prev'   => function () use ($page) {
                $prev = $page
                    ->prevAll()
                    ->filterBy('intendedTemplate', $page->intendedTemplate())
                    ->filterBy('status', $page->status())
                    ->filterBy('isReadable', true)
                    ->last();
                return $prev ? $prev->panel()->prevnext('title') : null;
            },
            'status' => function () use ($page) {
                if ($status = $page->status()) {
                    return $page->blueprint()->status()[$status] ?? null;
                }
            },
        ]);
    }

    /**
     * Returns the data array for
     * this model's Panel routes
     *
     * @internal
     *
     * @return array
     */
    public function route(): array
    {
        $page = $this->model;

        return [
            'breadcrumb' => $page->panel()->breadcrumb(),
            'component'  => 'k-page-view',
            'props'      => $this->props(),
            'title'      => $page->title()->toString(),
        ];
    }
}
