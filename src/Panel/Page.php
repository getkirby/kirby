<?php

namespace Kirby\Panel;

use Kirby\Cms\Collection;

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
     * Returns the Panel icon type
     * according to the blueprint settings
     *
     * @return string
     */
    protected function imageIcon(): string
    {
        if ($icon = $this->model->blueprint()->icon()) {
            return $icon;
        }

        return parent::imageIcon();
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
     * Returns navigation array with
     * previous and next page
     * based on blueprint definition
     *
     * @internal
     *
     * @return array
     */
    public function prevNext(): array
    {
        $page = $this->model;

        // create siblings collection based on
        // blueprint navigation
        $siblings = function (string $direction) use ($page) {
            $navigation = $page->blueprint()->navigation();
            $sortBy     = $navigation['sortBy'] ?? null;
            $status     = $navigation['status'] ?? null;
            $template   = $navigation['template'] ?? null;
            $direction  = $direction === 'prev' ? 'prev' : 'next';

            // if status is defined in navigation,
            // all items in the collection are used
            // (drafts, listed and unlisted) otherwise
            // it depends on the status of the page
            $siblings = $status !== null ? $page->parentModel()->childrenAndDrafts() : $page->siblings();

            // sort the collection if custom sortBy
            // defined in navigation otherwise
            // default sorting will apply
            if ($sortBy !== null) {
                $siblings = $siblings->sort(...$siblings::sortArgs($sortBy));
            }

            $siblings = $page->{$direction . 'All'}($siblings);

            if (empty($navigation) === false) {
                $statuses  = (array)($status ?? $page->status());
                $templates = (array)($template ?? $page->intendedTemplate());

                // do not filter if template navigation is all
                if (in_array('all', $templates) === false) {
                    $siblings = $siblings->filter('intendedTemplate', 'in', $templates);
                }

                // do not filter if status navigation is all
                if (in_array('all', $statuses) === false) {
                    $siblings = $siblings->filter('status', 'in', $statuses);
                }
            } else {
                $siblings = $siblings
                    ->filter('intendedTemplate', $page->intendedTemplate())
                    ->filter('status', $page->status());
            }

            return $siblings->filter('isReadable', true);
        };

        return [
            'next' => function () use ($siblings) {
                $next = $siblings('next')->first();
                return $next ? $next->panel()->toLink('title') : null;
            },
            'prev'   => function () use ($siblings) {
                $prev = $siblings('prev')->last();
                return $prev ? $prev->panel()->toLink('title') : null;
            }
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
        $page = $this->model;

        return array_merge(
            parent::props(),
            $this->prevNext(),
            [
                'model' => [
                    'content'    => $this->content(),
                    'id'         => $page->id(),
                    'parent'     => $page->parentModel()->panel()->url(true),
                    'previewUrl' => $page->previewUrl(),
                    'status'     => $page->status(),
                    'title'      => $page->title()->toString(),
                ],
                'status' => function () use ($page) {
                    if ($status = $page->status()) {
                        return $page->blueprint()->status()[$status] ?? null;
                    }
                },
            ]
        );
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
