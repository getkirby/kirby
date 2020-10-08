<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;

/**
 * The PagePicker class helps to
 * fetch the right pages and the parent
 * model for the API calls for the
 * page picker component in the panel.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class PagePicker extends Picker
{
    /**
     * @var \Kirby\Cms\Pages
     */
    protected $items;

    /**
     * @var \Kirby\Cms\Pages
     */
    protected $itemsForQuery;

    /**
     * @var \Kirby\Cms\Page|\Kirby\Cms\Site|null
     */
    protected $parent;

    /**
     * Extends the basic defaults
     *
     * @return array
     */
    public function defaults(): array
    {
        return array_merge(parent::defaults(), [
            // Page ID of the selected parent. Used to navigate
            'parent' => null,
            // enable/disable subpage navigation
            'subpages' => true,
        ]);
    }

    /**
     * Returns the parent model object that
     * is currently selected in the page picker.
     * It normally starts at the site, but can
     * also be any subpage. When a query is given
     * and subpage navigation is deactivated,
     * there will be no model available at all.
     *
     * @return \Kirby\Cms\Page|\Kirby\Cms\Site|null
     */
    public function model()
    {
        // no subpages navigation = no model
        if ($this->options['subpages'] === false) {
            return null;
        }

        // the model for queries is a bit more tricky to find
        if (empty($this->options['query']) === false) {
            return $this->modelForQuery();
        }

        return $this->parent();
    }

    /**
     * Returns a model object for the given
     * query, depending on the parent and subpages
     * options.
     *
     * @return \Kirby\Cms\Page|\Kirby\Cms\Site|null
     */
    public function modelForQuery()
    {
        if ($this->options['subpages'] === true && empty($this->options['parent']) === false) {
            return $this->parent();
        }

        if ($items = $this->items()) {
            return $items->parent();
        }

        return null;
    }

    /**
     * Returns basic information about the
     * parent model that is currently selected
     * in the page picker.
     *
     * @param \Kirby\Cms\Site|\Kirby\Cms\Page|null
     * @return array|null
     */
    public function modelToArray($model = null): ?array
    {
        if ($model === null) {
            return null;
        }

        // the selected model is the site. there's nothing above
        if (is_a($model, 'Kirby\Cms\Site') === true) {
            return [
                'id'     => null,
                'parent' => null,
                'title'  => $model->title()->value()
            ];
        }

        // the top-most page has been reached
        // the missing id indicates that there's nothing above
        if ($model->id() === $this->start()->id()) {
            return [
                'id'     => null,
                'parent' => null,
                'title'  => $model->title()->value()
            ];
        }

        // the model is a regular page
        return [
            'id'     => $model->id(),
            'parent' => $model->parentModel()->id(),
            'title'  => $model->title()->value()
        ];
    }

    /**
     * Search all pages for the picker
     *
     * @return \Kirby\Cms\Pages|null
     */
    public function items()
    {
        // cache
        if ($this->items !== null) {
            return $this->items;
        }

        // no query? simple parent-based search for pages
        if (empty($this->options['query']) === true) {
            $items = $this->itemsForParent();

        // when subpage navigation is enabled, a parent
        // might be passed in addition to the query.
        // The parent then takes priority.
        } elseif ($this->options['subpages'] === true && empty($this->options['parent']) === false) {
            $items = $this->itemsForParent();

        // search by query
        } else {
            $items = $this->itemsForQuery();
        }

        // filter protected pages
        $items = $items->filter('isReadable', true);

        // search
        $items = $this->search($items);

        // paginate the result
        return $this->items = $this->paginate($items);
    }

    /**
     * Search for pages by parent
     *
     * @return \Kirby\Cms\Pages
     */
    public function itemsForParent()
    {
        return $this->parent()->children();
    }

    /**
     * Search for pages by query string
     *
     * @return \Kirby\Cms\Pages
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    public function itemsForQuery()
    {
        // cache
        if ($this->itemsForQuery !== null) {
            return $this->itemsForQuery;
        }

        $model = $this->options['model'];
        $items = $model->query($this->options['query']);

        // help mitigate some typical query usage issues
        // by converting site and page objects to proper
        // pages by returning their children
        if (is_a($items, 'Kirby\Cms\Site') === true) {
            $items = $items->children();
        } elseif (is_a($items, 'Kirby\Cms\Page') === true) {
            $items = $items->children();
        } elseif (is_a($items, 'Kirby\Cms\Pages') === false) {
            throw new InvalidArgumentException('Your query must return a set of pages');
        }

        return $this->itemsForQuery = $items;
    }

    /**
     * Returns the parent model.
     * The model will be used to fetch
     * subpages unless there's a specific
     * query to find pages instead.
     *
     * @return \Kirby\Cms\Page|\Kirby\Cms\Site
     */
    public function parent()
    {
        if ($this->parent !== null) {
            return $this->parent;
        }

        return $this->parent = $this->kirby->page($this->options['parent']) ?? $this->site;
    }

    /**
     * Calculates the top-most model (page or site)
     * that can be accessed when navigating
     * through pages.
     *
     * @return \Kirby\Cms\Page|\Kirby\Cms\Site
     */
    public function start()
    {
        if (empty($this->options['query']) === false) {
            if ($items = $this->itemsForQuery()) {
                return $items->parent();
            }

            return $this->site;
        }

        return $this->site;
    }

    /**
     * Returns an associative array
     * with all information for the picker.
     * This will be passed directly to the API.
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        $array['model'] = $this->modelToArray($this->model());

        return $array;
    }
}
