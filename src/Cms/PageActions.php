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
     *
     * @param int $num
     * @return self
     */
    protected function changeNum(int $num = null): self
    {
        // always make sure to have the right sorting number
        $num = $num !== null ? $this->createNum($num) : null;

        if ($num === $this->num()) {
            return $this;
        }

        return $this->commit('changeNum', [$this, $num], function ($oldPage, $num) {
            $newPage = $oldPage->clone(['num' => $num]);

            if ($oldPage->exists() === false) {
                return $newPage;
            }

            if (Dir::move($oldPage->root(), $newPage->root()) !== true) {
                throw new LogicException('The page directory cannot be moved');
            }

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
        // method takse care of that.
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
            $newPage = $oldPage->clone(['slug' => $slug]);

            if ($oldPage->exists() === false) {
                return $newPage;
            }

            if (Dir::move($oldPage->root(), $newPage->root()) !== true) {
                throw new LogicException('The page directory cannot be moved');
            }

            Dir::remove($oldPage->mediaRoot());

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

        return $this->commit('changeSlug', [$this, $slug, $languageCode], function ($oldPage, $slug, $languageCode) {
            $content = $oldPage->content($languageCode)->toArray();
            $content['slug'] = $slug;

            return $oldPage->clone(['content' => $content])->save($languageCode);
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
            $draft = $page->clone(['num' => null, 'isDraft' => true]);

            if ($page->exists() === false) {
                return $draft;
            }

            if (Dir::move($page->root(), $draft->root()) !== true) {
                throw new LogicException('The page directory cannot be moved');
            }

            return $draft;
        });

        $page->parentModel()->purge();
        $this->resortSiblingsAfterUnlisting();

        return $page;
    }

    protected function changeStatusToListed(int $position = null): self
    {
        // create a sorting number for the page
        $num = $this->createNum($position);

        if ($this->status() === 'listed' && $num === $this->num()) {
            return $this;
        }

        $page = $this->commit('changeStatus', [$this, 'listed', $num], function ($page, $status, $position) {
            return $page->publish()->changeNum($position);
        });

        $page->parentModel()->purge();

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

        $page->parentModel()->purge();
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
        if ($template === $this->template()) {
            return $this;
        }

        // prepare data to transfer between blueprints
        $old      = 'pages/' . $this->template();
        $new      = 'pages/' . $template;
        $transfer = $this->transferData($this->content(), $old, $new);

        return $this->commit('changeTemplate', [$this, $template, $transfer['data']], function ($oldPage, $template, $content) {
            $newPage = $oldPage->clone([
                'content'  => $content,
                'template' => $template
            ]);

            if ($oldPage->exists() === false) {
                return $newPage;
            }

            $newPage->save();

            if (F::remove($oldPage->contentFile()) !== true) {
                throw new LogicException('The old text file could not be removed');
            }

            return $newPage;
        });
    }

    /**
     * Change the page title
     *
     * @param string $title
     * @return self
     */
    public function changeTitle(string $title): self
    {
        if ($title === $this->title()->value()) {
            return $this;
        }

        return $this->commit('changeTitle', [$this, $title], function ($page, $title) {
            $content = $page
                ->content()
                ->update(['title' => $title])
                ->toArray();

            return $page->clone(['content' => $content])->save();
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

        // flush the pages cache, except the changeNum action is run
        // flushing it there, would be triggered way too often.
        // triggering it on sort and hide is absolutely enough
        if ($action !== 'changeNum') {
            $this->kirby()->cache('pages')->flush();
        }

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
        $props['template'] = strtolower($props['template'] ?? 'default');
        $props['isDraft']  = true;

        // create a temporary page object
        $page = Page::factory($props);

        return $page->commit('create', [$page, $props], function ($page, $props) {

            // create the new page directory
            if (Dir::make($page->root()) !== true) {
                throw new LogicException('The page directory for "' . $page->slug() . '" cannot be created');
            }

            // always create pages in the default language
            if ($page->kirby()->multilang() === true) {
                $languageCode = $page->kirby()->languages()->default()->code();
            } else {
                $languageCode = null;
            }

            // write the content file
            return $page->clone()->save($languageCode);
        });
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

    public function createFile(array $props)
    {
        $props = array_merge($props, [
            'parent' => $this,
            'url'    => null
        ]);

        return File::create($props);
    }

    /**
     * Create the sorting number for the page
     * depending on the blueprint settings
     *
     * @param integer $num
     * @return integer
     */
    protected function createNum(int $num = null): int
    {
        $mode = $this->blueprint()->num();

        switch ($mode) {
            case 'zero':
                return 0;
            case 'default':
                // avoid zeros or negative numbers
                if ($num < 1) {
                    return 1;
                }

                $max = $this->parentModel()->purge()->children()->listed()->merge($this)->count();

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
        $result = $this->commit('delete', [$this, $force], function ($page, $force) {
            if ($page->exists() === false) {
                return true;
            }

            // delete all files individually
            foreach ($page->files() as $file) {
                $file->delete();
            }

            // delete all children individually
            foreach ($page->children() as $child) {
                $child->delete(true);
            }

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

            return true;
        });

        $this->parentModel()->purge();
        $this->resortSiblingsAfterUnlisting();

        return $result;
    }

    public function publish()
    {
        if ($this->isDraft() === false) {
            return $this;
        }

        $page = $this->clone(['isDraft' => false]);

        if ($this->exists() === false) {
            return $page;
        }

        if (Dir::move($this->root(), $page->root()) !== true) {
            throw new LogicException('The draft folder cannot be moved');
        }

        // Get the draft folder and check if there are any other drafts
        // left. Otherwise delete it.
        $draftDir = dirname($this->root());

        if (Dir::isEmpty($draftDir) === true) {
            Dir::remove($draftDir);
        }

        return $page;
    }

    /**
     * Clean internal caches
     */
    public function purge(): self
    {
        $this->children  = null;
        $this->blueprint = null;
        $this->files     = null;
        $this->content   = null;
        $this->inventory = null;

        return $this;
    }

    protected function resortSiblingsAfterListing(int $position): bool
    {
        // get all siblings including the current page
        $siblings = $this->parentModel()->purge()->children()->listed()->merge($this);

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
                $siblings->findBy('id', $id)->changeNum($key + 1);
            }
        }

        return true;
    }

    protected function resortSiblingsAfterUnlisting(): bool
    {
        $siblings = $this->parentModel()->purge()->children()->listed()->not($this);
        $index    = 0;

        foreach ($siblings as $sibling) {
            $index++;
            $sibling->changeNum($index);
        }

        return true;
    }

    /**
     * Delete the text file without language code
     * before storing the actual file
     *
     * @param string|null $languageCode
     * @return self
     */
    public function save(string $languageCode = null)
    {
        if ($this->kirby()->multilang() === true) {
            F::remove($this->contentFile());
        }

        return parent::save($languageCode);
    }

    /**
     * Transfers data from old to new blueprint and tracks changes
     *
     * @param Content $content
     * @param string $old       Old blueprint
     * @param string $new       New blueprint
     * @return array
     */
    protected function transferData(Content $content, string $old, string $new): array
    {
        // Prepare data
        $data      = [];
        $old       = Blueprint::factory($old, 'pages/default', $this);
        $new       = Blueprint::factory($new, 'pages/default', $this);
        $oldFields = $old->fields();
        $newFields = $new->fields();

        // Tracking changes
        $added    = [];
        $replaced = [];
        $removed  = [];

        // Ensure to keep title
        $data['title'] = $content->get('title')->value();

        // Go through all fields of new template
        foreach ($newFields as $newField) {
            $name     = $newField->name();
            $oldField = $oldFields->get($name);

            // Field name matches with old template
            if ($oldField !== null) {

                // Same field type, add and keep value
                if ($oldField->type() === $newField->type()) {
                    $data[$name] = $content->get($name)->value();

                // Different field type, add with empty value
                } else {
                    $data[$name]     = null;
                    $replaced[$name] = $oldFields->get($name)->label();
                }

                // Field does not exist in old template,
            // add with empty or preserved value
            } else {
                $preserved    = $content->get($name);
                $data[$name]  = $preserved ? $preserved->value(): null;
                $added[$name] = $newField->label();
            }
        }

        // Go through all values to preserve them
        foreach ($content->fields() as $field) {
            $name     = $field->key();
            $newField = $newFields->get($name);

            if ($newField === null) {
                $data[$name]    = $field->value();
                $removed[$name] = $field->name();
            }
        }

        return [
            'data'     => $data,
            'added'    => $added,
            'replaced' => $replaced,
            'removed'  => $removed
        ];
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
        $page = parent::update($input, $language, $validate);

        // if num is created from page content, update num on content update
        if ($page->isListed() === true && in_array($page->blueprint()->num(), ['zero', 'default']) === false) {
            $page = $page->changeNum();
        }

        return $page;
    }
}
