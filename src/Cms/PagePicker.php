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
class PagePicker
{
    /**
     * @var \Kirby\Cms\App
     */
    protected $kirby;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var \Kirby\Cms\Pages
     */
    protected $pages;

    /**
     * @var \Kirby\Cms\Pages
     */
    protected $pagesForQuery;

    /**
     * @var \Kirby\Cms\Page|\Kirby\Cms\Site
     */
    protected $parent;

    /**
     * @var \Kirby\Cms\Site
     */
    protected $site;

    /**
     * Creates a new PagePicker instance
     *
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        // default params
        $defaults = [
            // image settings (ratio, cover, etc.)
            'image' => [],
            // query template for the page info field
            'info' => false,
            // number of pages displayed per pagination page
            'limit' => 20,
            // optional mapping function for the pages array
            'map' => null,
            // the reference model (site or page)
            'model' => site(),
            // current page when paginating
            'page' => 1,
            // Page ID of the selected parent. Used to navigate
            'parent' => null,
            // a query string to fetch specific pages
            'query' => null,
            // enable/disable subpage navigation
            'subpages' => true,
            // query template for the page text field
            'text' => null
        ];

        $this->options = array_merge($defaults, $params);
        $this->kirby   = $this->options['model']->kirby();
        $this->site    = $this->kirby->site();
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

        if ($pages = $this->pages()) {
            return $pages->parent();
        }

        return null;
    }

    /**
     * Returns basic information about the
     * parent model that is currently selected
     * in the page picker.
     *
     * @param \Kirby\Cms\Site|\Kirby\Cms\Page
     * @return array|null
     */
    public function modelToArray($model): ?array
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
    public function pages()
    {
        // cache
        if ($this->pages !== null) {
            return $this->pages;
        }

        // no query? simple parent-based search for pages
        if (empty($this->options['query']) === true) {
            $pages = $this->pagesForParent();

        // when subpage navigation is enabled, a parent
        // might be passed in addition to the query.
        // The parent then takes priority.
        } elseif ($this->options['subpages'] === true && empty($this->options['parent']) === false) {
            $pages = $this->pagesForParent();

        // search by query
        } else {
            $pages = $this->pagesForQuery();
        }

        // filter protected pages
        $pages = $pages->filterBy('isReadable', true);

        // paginate the result
        $pages = $pages->paginate([
            'limit' => $this->options['limit'],
            'page'  => $this->options['page']
        ]);

        // cache and return the result
        return $this->pages = $pages;
    }

    /**
     * Search for pages by parent
     *
     * @return \Kirby\Cms\Pages
     */
    public function pagesForParent()
    {
        return $this->parent()->children();
    }

    /**
     * Search for pages by query string
     *
     * @return \Kirby\Cms\Pages
     */
    public function pagesForQuery()
    {
        // cache
        if ($this->pagesForQuery !== null) {
            return $this->pagesForQuery;
        }

        $model = $this->options['model'];
        $pages = $model->query($this->options['query']);

        // help mitigate some typical query usage issues
        // by converting site and page objects to proper
        // pages by returning their children

        if (is_a($pages, 'Kirby\Cms\Site') === true) {
            $pages = $pages->children();
        } elseif (is_a($pages, 'Kirby\Cms\Page') === true) {
            $pages = $pages->children();
        } elseif (is_a($pages, 'Kirby\Cms\Pages') === false) {
            throw new InvalidArgumentException('Your query must return a set of pages');
        }

        return $this->pagesForQuery = $pages;
    }

    /**
     * Converts all given pages to an associative
     * array that is already optimized for the
     * panel picker component.
     *
     * @param \Kirby\Cms\Pages|null $pages
     * @return array
     */
    public function pagesToArray($pages): array
    {
        if ($pages === null) {
            return [];
        }

        $result = [];

        // create the array result for each individual page
        foreach ($pages as $index => $page) {
            if (empty($this->options['map']) === false) {
                $result[] = $this->options['map']($page);
            } else {
                $result[] = $page->panelPickerData([
                    'image' => $this->options['image'],
                    'info'  => $this->options['info'],
                    'model' => $this->options['model'],
                    'text'  => $this->options['text'],
                ]);
            }
        }

        return $result;
    }

    /**
     * Return the most relevant pagination
     * info as array
     *
     * @param \Kirby\Cms\Pagination $pagination
     * @return array
     */
    public function paginationToArray(Pagination $pagination): array
    {
        return [
            'limit' => $pagination->limit(),
            'page'  => $pagination->page(),
            'total' => $pagination->total()
        ];
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
            if ($pages = $this->pagesForQuery()) {
                return $pages->parent();
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
        $pages = $this->pages();

        return [
            'data'       => $this->pagesToArray($pages),
            'model'      => $this->modelToArray($this->model()),
            'pagination' => $this->paginationToArray($pages->pagination())
        ];
    }
}
