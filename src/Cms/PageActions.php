<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Util\A;
use Kirby\Util\Str;

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
        if ($num === $this->num()) {
            return $this;
        }

        return $this->commit('changeNum', $num);
    }

    /**
     * Changes the slug/uid of the page
     *
     * @param string $slug
     * @return self
     */
    public function changeSlug(string $slug): self
    {
        if ($slug === $this->slug()) {
            return $this;
        }

        return $this->commit('changeSlug', $slug);
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
                throw new Exception('Invalid status');
        }
    }

    protected function changeStatusToDraft(): self
    {
        return $this->commit('changeStatus', 'draft');
    }

    protected function resortSiblingsAfterListing(int $position): bool
    {
        // get all siblings including the current page
        $siblings = $this->siblings()->listed();

        // get a non-associative array of ids
        $keys  = $siblings->keys();
        $index = array_search($this->id(), $keys);

        // if the page is not included in the siblings
        // push the page at the end.
        if ($index === false) {
            $keys[] = $this->id();
            $index  = count($keys) - 1;
        }

        // move the current page number in the array of keys
        // subtract 1 from the num and the position, because of the
        // zero-based array keys
        $sorted = A::move($keys, $index, $position - 1);

        foreach ($sorted as $key => $id) {
            if ($id === $this->id()) {
                $position = $key + 1;
            } else {
                $siblings->findBy('id', $id)->commit('changeNum', $key + 1);
            }
        }

        return true;
    }

    protected function resortSiblingsAfterUnlisting(): bool
    {
        $siblings = $this->siblings()->listed()->not($this);
        $index    = 0;

        foreach ($siblings as $sibling) {
            $index++;
            $sibling->commit('changeNum', $index);
        }

        return true;
    }

    protected function changeStatusToListed(int $position = null): self
    {
        if ($this->status() === 'listed') {
            return $this;
        }

        // create a sorting number for the page
        $num  = $this->createNum($position);
        $page = $this->commit('changeStatus', 'listed', $num);

        if ($this->blueprint()->num() === 'default') {
            $this->resortSiblingsAfterListing($num);
        }

        return $page;
    }

    protected function changeStatusToUnlisted(): self
    {
        if ($this->status() === 'unlisted') {
            return $this;
        }

        $page = $this->commit('changeStatus', 'unlisted');

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

        return $this->commit('changeTemplate', $template);
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

        return $this->commit('changeTitle', $title);
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
    protected function commit(string $action, ...$arguments)
    {
        $this->rules()->$action($this, ...$arguments);
        $this->kirby()->trigger('page.' . $action . ':before', $this, ...$arguments);
        $result = $this->store()->$action(...$arguments);
        $this->kirby()->trigger('page.' . $action . ':after', $result, $this);

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
        $props['slug'] = Str::slug($props['slug'] ?? $props['content']['title'] ?? null);

        // create a temporary page object
        $page = PageDraft::factory($props);

        return $page->commit('create', $props);
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
            'store'  => $this->store()::PAGE_STORE_CLASS,
        ]);

        return static::create($props);
    }

    public function createFile(array $props)
    {
        $props = array_merge($props, [
            'parent' => $this,
            'store'  => $this->store()::FILE_STORE_CLASS,
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
                return $num;
            default:
                $template = new Tempura($mode, [
                    'kirby' => $this->kirby(),
                    'page'  => $this,
                    'site'  => $this->site(),
                ]);

                return intval($template->render());
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
        $this->rules()->delete($this, $force);
        $this->kirby()->trigger('page.delete:before', $this, $force);

        // delete all files individually
        foreach ($this->files() as $file) {
            $file->delete();
        }

        // delete all children individually
        foreach ($this->children() as $child) {
            $child->delete(true);
        }

        $result = $this->store()->delete();

        $this->resortSiblingsAfterUnlisting();

        $this->kirby()->trigger('page.delete:after', $result, $this);
        $this->kirby()->cache('pages')->flush();

        return $result;
    }

}
