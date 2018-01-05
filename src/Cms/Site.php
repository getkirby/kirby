<?php

namespace Kirby\Cms;

use Exception;

/**
 * The Site class is the root element
 * for any site with pages. It represents
 * the main content folder with its site.txt
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class Site extends Object
{

    use HasChildren;
    use HasContent;
    use HasFiles;

    /**
     * Property schema
     *
     * @return array
     */
    protected function schema()
    {
        return [
            'blueprint' => [
                'type'    => SiteBlueprint::class,
                'default' => function (): SiteBlueprint {
                    return $this->store()->commit('site.blueprint', $this);
                }
            ],
            'children' => [
                'type'    => Pages::class,
                'default' => function (): Pages {
                    return $this->store()->commit('site.children', $this);
                }
            ],
            'content' => [
                'type'    => Content::class,
                'default' => function () {
                    return $this->store()->commit('site.content', $this);
                }
            ],
            'errorPage' => [
                'type'    => Page::class,
                'default' => function () {
                    return $this->find('error');
                }
            ],
            'files' => [
                'type'    => Files::class,
                'default' => function () {
                    return $this->store()->commit('site.files', $this);
                }
            ],
            'homePage' => [
                'type'    => Page::class,
                'default' => function () {
                    return $this->find('home');
                }
            ],
            'page' => [
                'type'    => Page::class,
                'default' => function () {
                    return $this->homePage();
                }
            ],
            'root' => [
                'type' => 'string',
            ],
            'store' => [
                'type'    => Store::class,
                'default' => function () {
                    return $this->plugin('store');
                }
            ],
            'url' => [
                'type'    => 'string',
                'default' => '/'
            ],
        ];
    }

    /**
     * Returns the current page if `$path`
     * is not specified. Otherwise it will try
     * to find a page by the given path.
     *
     * If no current page is set with the page
     * prop, the home page will be returned if
     * it can be found. (see `Site::homePage()`)
     *
     * @param  string $path
     * @return Page|null
     */
    public function page(string $path = null)
    {
        if ($path === null) {
            return $this->props->get('page');
        }

        return $this->find($path);
    }

    /**
     * Alias for `Site::children()`
     *
     * @return Pages
     */
    public function pages(): Pages
    {
        return $this->children();
    }

    /**
     * Sets the current page by
     * id or page object and
     * returns the current page
     *
     * @param  string|Page $page
     * @return Page
     */
    public function visit($page): Page
    {
        // convert ids to a Page object
        if (is_string($page)) {
            $page = $this->find($page);
        }

        // handle invalid pages
        if (is_a($page, Page::class) === false) {
            throw new Exception('Invalid page object');
        }

        // set the current active page
        $this->set('page', $page);

        // return the page
        return $page;
    }

    /**
     * Updates the content of the site
     * in the site.txt
     *
     * @param array $content
     * @return self
     */
    public function update(array $content = []): self
    {
        return $this->store()->commit('site.update', $this, $content);
    }

}
