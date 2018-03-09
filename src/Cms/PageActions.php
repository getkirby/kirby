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

        if ($num !== null) {

            $mode = $this->blueprint()->num();

            switch ($mode) {
                case 'zero':
                    $num = 0;
                    break;
                case 'default':
                    $num = $num;
                    break;
                default:
                    $template = new Tempura($mode, [
                        'kirby' => $this->kirby(),
                        'page'  => $this,
                        'site'  => $this->site(),
                    ]);

                    $num = intval($template->render());
                    break;
            }

            if ($num === $this->num()) {
                return $this;
            }

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
        $page = Page::factory($props);

        // validate the page dummy
        $page->rules()->create($page);

        $page->kirby()->trigger('page.create:before', $page->parent(), $props);

        $result = $page->store()->create($page);

        $page->kirby()->trigger('page.create:after', $result);

        return $result;
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

        $this->kirby()->trigger('page.delete:after', $result, $this);

        return $result;
    }

    /**
     * Changes the status to unlisted
     *
     * @return self
     */
    public function hide(): self
    {
        if ($this->isInvisible() === true) {
            return $this;
        }

        // TODO: move this to rules
        if ($this->blueprint()->options()->changeStatus() === false) {
            throw new Exception('The status for this page cannot be changed');
        }

        $siblings = $this->siblings()->not($this);
        $index    = 0;

        foreach ($siblings as $sibling) {
            $index++;
            $sibling->commit('changeNum', $index);
        }

        $this->kirby()->trigger('page.hide:before', $this);
        $result = $this->commit('changeNum', null);
        $this->kirby()->trigger('page.hide:after', $result, $this);

        return $result;
    }

    /**
     * Changes the page number
     *
     * @param int $position
     * @return self
     */
    public function sort(int $position): self
    {
        // TODO: move this to rules
        if ($this->isInvisible() === true && empty($this->errors()) === false) {
            throw new Exception('The page has errors and cannot be published');
        }

        // TODO: move this to rules
        if ($this->blueprint()->options()->changeStatus() !== true) {
            throw new Exception('The status for this page cannot be changed');
        }

        if ($this->blueprint()->num() === 'default') {

            // get all siblings including the current page
            $siblings = $this->siblings()->visible();

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

        }

        $this->kirby()->trigger('page.sort:before', $this, $position);
        $result = $this->commit('changeNum', $position);
        $this->kirby()->trigger('page.sort:after', $result, $this);

        return $result;

    }

}
