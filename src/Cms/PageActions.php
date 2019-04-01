<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Data\Data;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Toolkit\A;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;

trait PageActions
{

    /**
     * Changes the sorting number
     * The sorting number must already be correct
     * when the method is called
     *
     * @param int $num
     * @return self
     */
    public function changeNum(int $num = null): self
    {
        if ($this->isDraft() === true) {
            throw new LogicException('Drafts cannot change their sorting number');
        }

        // don't run the action if everything stayed the same
        if ($this->num() === $num) {
            return $this;
        }

        return $this->commit('changeNum', [$this, $num], function ($oldPage, $num) {
            $newPage = $oldPage->clone([
                'num'     => $num,
                'dirname' => null,
                'root'    => null
            ]);

            // actually move the page on disk
            if ($oldPage->exists() === true) {
                Dir::move($oldPage->root(), $newPage->root());
            }

            // overwrite the child in the parent page
            $newPage
                ->parentModel()
                ->children()
                ->set($newPage->id(), $newPage);

            return $newPage;
        });
    }

    /**
     * Changes the slug/uid of the page
     *
     * @param string $slug
     * @param string $language
     * @return self
     */
    public function changeSlug(string $slug, string $languageCode = null): self
    {
        // always sanitize the slug
        $slug = Str::slug($slug);

        // in multi-language installations the slug for the non-default
        // languages is stored in the text file. The changeSlugForLanguage
        // method takes care of that.
        if ($language = $this->kirby()->language($languageCode)) {
            if ($language->isDefault() === false) {
                return $this->changeSlugForLanguage($slug, $languageCode);
            }
        }

        // if the slug stays exactly the same,
        // nothing needs to be done.
        if ($slug === $this->slug()) {
            return $this;
        }

        return $this->commit('changeSlug', [$this, $slug, $languageCode = null], function ($oldPage, $slug) {
            $newPage = $oldPage->clone([
                'slug'    => $slug,
                'dirname' => null,
                'root'    => null
            ]);

            // actually move stuff on disk
            if ($oldPage->exists() === true) {
                if (Dir::move($oldPage->root(), $newPage->root()) !== true) {
                    throw new LogicException('The page directory cannot be moved');
                }

                Dir::remove($oldPage->mediaRoot());
            }

            // overwrite the new page in the parent collection
            if ($newPage->isDraft() === true) {
                $newPage->parentModel()->drafts()->set($newPage->id(), $newPage);
            } else {
                $newPage->parentModel()->children()->set($newPage->id(), $newPage);
            }

            return $newPage;
        });
    }

    /**
     * Change the slug for a specific language
     *
     * @param string $slug
     * @param string $language
     * @return self
     */
    protected function changeSlugForLanguage(string $slug, string $languageCode = null): self
    {
        $language = $this->kirby()->language($languageCode);

        if (!$language) {
            throw new NotFoundException('The language: "' . $languageCode . '" does not exist');
        }

        if ($language->isDefault() === true) {
            throw new InvalidArgumentException('Use the changeSlug method to change the slug for the default language');
        }

        return $this->commit('changeSlug', [$this, $slug, $languageCode], function ($page, $slug, $languageCode) {
            // remove the slug if it's the same as the folder name
            if ($slug === $page->uid()) {
                $slug = null;
            }

            return $page->save(['slug' => $slug], $languageCode);
        });
    }

    /**
     * Change the status of the current page
     * to either draft, listed or unlisted
     *
     * @param string $status "draft", "listed" or "unlisted"
     * @param integer $position Optional sorting number
     * @return Page
     */
    public function changeStatus(string $status, int $position = null): self
    {
        switch ($status) {
            case 'draft':
                return $this->changeStatusToDraft();
            case 'listed':
                return $this->changeStatusToListed($position);
            case 'unlisted':
                return $this->changeStatusToUnlisted();
            default:
                throw new Exception('Invalid status: ' . $status);
        }
    }

    protected function changeStatusToDraft(): self
    {
        $page = $this->commit('changeStatus', [$this, 'draft'], function ($page) {
            return $page->unpublish();
        });

        return $page;
    }

    protected function changeStatusToListed(int $position = null): self
    {
        // create a sorting number for the page
        $num = $this->createNum($position);

        // don't sort if not necessary
        if ($this->status() === 'listed' && $num === $this->num()) {
            return $this;
        }

        $page = $this->commit('changeStatus', [$this, 'listed', $num], function ($page, $status, $position) {
            return $page->publish()->changeNum($position);
        });

        if ($this->blueprint()->num() === 'default') {
            $page->resortSiblingsAfterListing($num);
        }

        return $page;
    }

    protected function changeStatusToUnlisted(): self
    {
        if ($this->status() === 'unlisted') {
            return $this;
        }

        $page = $this->commit('changeStatus', [$this, 'unlisted'], function ($page) {
            return $page->publish()->changeNum(null);
        });

        $this->resortSiblingsAfterUnlisting();

        return $page;
    }

    /**
     * Changes the page template
     *
     * @param string $template
     * @return self
     */
    public function changeTemplate(string $template): self
    {
        if ($template === $this->template()->name()) {
            return $this;
        }

        return $this->commit('changeTemplate', [$this, $template], function ($oldPage, $template) {
            if ($this->kirby()->multilang() === true) {
                $newPage = $this->clone([
                    'template' => $template
                ]);

                foreach ($this->kirby()->languages()->codes() as $code) {
                    $content = $oldPage->content($code)->convertTo($template);

                    if (F::remove($oldPage->contentFile($code)) !== true) {
                        throw new LogicException('The old text file could not be removed');
                    }

                    // save the language file
                    $newPage->save($content, $code);
                }

                // return a fresh copy of the object
                return $newPage->clone();
            } else {
                $newPage = $this->clone([
                    'content'  => $this->content()->convertTo($template),
                    'template' => $template
                ]);

                if (F::remove($oldPage->contentFile()) !== true) {
                    throw new LogicException('The old text file could not be removed');
                }

                return $newPage->save();
            }
        });
    }

    /**
     * Change the page title
     *
     * @param string $title
     * @param string|null $languageCode
     * @return self
     */
    public function changeTitle(string $title, string $languageCode = null): self
    {
        return $this->commit('changeTitle', [$this, $title, $languageCode], function ($page, $title, $languageCode) {
            return $page->save(['title' => $title], $languageCode);
        });
    }

    /**
     * Commits a page action, by following these steps
     *
     * 1. checks the action rules
     * 2. sends the before hook
     * 3. commits the store action
     * 4. sends the after hook
     * 5. returns the result
     *
     * @param string $action
     * @param mixed ...$arguments
     * @return mixed
     */
    protected function commit(string $action, array $arguments, Closure $callback)
    {
        $old = $this->hardcopy();

        $this->rules()->$action(...$arguments);
        $this->kirby()->trigger('page.' . $action . ':before', ...$arguments);
        $result = $callback(...$arguments);
        $this->kirby()->trigger('page.' . $action . ':after', $result, $old);
        $this->kirby()->cache('pages')->flush();
        return $result;
    }

    /**
     * Creates and stores a new page
     *
     * @param array $props
     * @return self
     */
    public static function create(array $props): self
    {
        // clean up the slug
        $props['slug']     = Str::slug($props['slug'] ?? $props['content']['title'] ?? null);
        $props['template'] = $props['model'] = strtolower($props['template'] ?? 'default');
        $props['isDraft']  = ($props['draft'] ?? true);

        // create a temporary page object
        $page = Page::factory($props);

        // create a form for the page
        $form = Form::for($page, [
            'values' => $props['content'] ?? []
        ]);

        // inject the content
        $page = $page->clone(['content' => $form->strings(true)]);

        // run the hooks and creation action
        $page = $page->commit('create', [$page, $props], function ($page, $props) {

            // always create pages in the default language
            if ($page->kirby()->multilang() === true) {
                $languageCode = $page->kirby()->defaultLanguage()->code();
            } else {
                $languageCode = null;
            }

            // write the content file
            $page = $page->save($page->content()->toArray(), $languageCode);

            // flush the parent cache to get children and drafts right
            if ($page->isDraft() === true) {
                $page->parentModel()->drafts()->append($page->id(), $page);
            } else {
                $page->parentModel()->children()->append($page->id(), $page);
            }

            return $page;
        });

        // publish the new page if a number is given
        if (isset($props['num']) === true) {
            $page = $page->changeStatus('listed', $props['num']);
        }

        return $page;
    }

    /**
     * Creates a child of the current page
     *
     * @param array $props
     * @return self
     */
    public function createChild(array $props): self
    {
        $props = array_merge($props, [
            'url'    => null,
            'num'    => null,
            'parent' => $this,
            'site'   => $this->site(),
        ]);

        return static::create($props);
    }

    /**
     * Create the sorting number for the page
     * depending on the blueprint settings
     *
     * @param integer $num
     * @return integer
     */
    public function createNum(int $num = null): int
    {
        $mode = $this->blueprint()->num();

        switch ($mode) {
            case 'zero':
                return 0;
            case 'date':
            case 'datetime':
                $format = $mode === 'date' ? 'Ymd' : 'YmdHi';
                $date   = $this->content()->get('date')->value();
                $time   = empty($date) === true ? time() : strtotime($date);

                return date($format, $time);
                break;
            case 'default':

                $max = $this
                    ->parentModel()
                    ->children()
                    ->listed()
                    ->merge($this)
                    ->count();

                // default positioning at the end
                if ($num === null) {
                    $num = $max;
                }

                // avoid zeros or negative numbers
                if ($num < 1) {
                    return 1;
                }

                // avoid higher numbers than possible
                if ($num > $max) {
                    return $max;
                }

                return $num;
            default:
                $template = Str::template($mode, [
                    'kirby' => $this->kirby(),
                    'page'  => $this,
                    'site'  => $this->site(),
                ]);

                return intval($template);
        }
    }

    /**
     * Deletes the page
     *
     * @param bool $force
     * @return bool
     */
    public function delete(bool $force = false): bool
    {
        return $this->commit('delete', [$this, $force], function ($page, $force) {

            // delete all files individually
            foreach ($page->files() as $file) {
                $file->delete();
            }

            // delete all children individually
            foreach ($page->children() as $child) {
                $child->delete(true);
            }

            // actually remove the page from disc
            if ($page->exists() === true) {

                // delete all public media files
                Dir::remove($page->mediaRoot());

                // delete the content folder for this page
                Dir::remove($page->root());

                // if the page is a draft and the _drafts folder
                // is now empty. clean it up.
                if ($page->isDraft() === true) {
                    $draftsDir = dirname($page->root());

                    if (Dir::isEmpty($draftsDir) === true) {
                        Dir::remove($draftsDir);
                    }
                }
            }

            if ($page->isDraft() === true) {
                $page->parentModel()->drafts()->remove($page);
            } else {
                $page->parentModel()->children()->remove($page);
                $page->resortSiblingsAfterUnlisting();
            }

            return true;
        });
    }

    public function publish()
    {
        if ($this->isDraft() === false) {
            return $this;
        }

        $page = $this->clone([
            'isDraft' => false,
            'root'    => null
        ]);

        // actually do it on disk
        if ($this->exists() === true) {
            if (Dir::move($this->root(), $page->root()) !== true) {
                throw new LogicException('The draft folder cannot be moved');
            }

            // Get the draft folder and check if there are any other drafts
            // left. Otherwise delete it.
            $draftDir = dirname($this->root());

            if (Dir::isEmpty($draftDir) === true) {
                Dir::remove($draftDir);
            }
        }

        // remove the page from the parent drafts and add it to children
        $page->parentModel()->drafts()->remove($page);
        $page->parentModel()->children()->append($page->id(), $page);

        return $page;
    }

    /**
     * Clean internal caches
     */
    public function purge(): self
    {
        $this->children  = null;
        $this->blueprint = null;
        $this->drafts    = null;
        $this->files     = null;
        $this->content   = null;
        $this->inventory = null;

        return $this;
    }

    protected function resortSiblingsAfterListing(int $position = null): bool
    {
        // get all siblings including the current page
        $siblings = $this
            ->parentModel()
            ->children()
            ->listed()
            ->append($this)
            ->filter(function ($page) {
                return $page->blueprint()->num() === 'default';
            });

        // get a non-associative array of ids
        $keys  = $siblings->keys();
        $index = array_search($this->id(), $keys);

        // if the page is not included in the siblings something went wrong
        if ($index === false) {
            throw new LogicException('The page is not included in the sorting index');
        }

        if ($position > count($keys)) {
            $position = count($keys);
        }

        // move the current page number in the array of keys
        // subtract 1 from the num and the position, because of the
        // zero-based array keys
        $sorted = A::move($keys, $index, $position - 1);

        foreach ($sorted as $key => $id) {
            if ($id === $this->id()) {
                continue;
            } else {
                if ($sibling = $siblings->get($id)) {
                    $sibling->changeNum($key + 1);
                }
            }
        }

        $parent = $this->parentModel();
        $parent->children = $parent->children()->sortBy('num', 'asc');

        return true;
    }

    public function resortSiblingsAfterUnlisting(): bool
    {
        $index    = 0;
        $parent   = $this->parentModel();
        $siblings = $parent
            ->children()
            ->listed()
            ->not($this)
            ->filter(function ($page) {
                return $page->blueprint()->num() === 'default';
            });

        if ($siblings->count() > 0) {
            foreach ($siblings as $sibling) {
                $index++;
                $sibling->changeNum($index);
            }

            $parent->children = $siblings->sortBy('num', 'desc');
        }

        return true;
    }

    public function sort($position = null)
    {
        return $this->changeStatus('listed', $position);
    }

    /**
     * Convert a page from listed or
     * unlisted to draft.
     *
     * @return self
     */
    public function unpublish()
    {
        if ($this->isDraft() === true) {
            return $this;
        }

        $page = $this->clone([
            'isDraft' => true,
            'num'     => null,
            'dirname' => null,
            'root'    => null
        ]);

        // actually do it on disk
        if ($this->exists() === true) {
            if (Dir::move($this->root(), $page->root()) !== true) {
                throw new LogicException('The page folder cannot be moved to drafts');
            }
        }

        // remove the page from the parent children and add it to drafts
        $page->parentModel()->children()->remove($page);
        $page->parentModel()->drafts()->append($page->id(), $page);

        $page->resortSiblingsAfterUnlisting();

        return $page;
    }

    /**
     * Updates the page data
     *
     * @param array $input
     * @param string $language
     * @param boolean $validate
     * @return self
     */
    public function update(array $input = null, string $language = null, bool $validate = false)
    {
        if ($this->isDraft() === true) {
            $validate = false;
        }

        $page = parent::update($input, $language, $validate);

        // if num is created from page content, update num on content update
        if ($page->isListed() === true && in_array($page->blueprint()->num(), ['zero', 'default']) === false) {
            $page = $page->changeNum($page->createNum());
        }

        return $page;
    }
}
