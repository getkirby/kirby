<?php

namespace Kirby\Cms;

use Kirby\Exception\LogicException;
use Kirby\Toolkit\A;
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
        $num = $this->createNum($num);

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
        // if the slug stays exactly the same,
        // nothing needs to be done.
        if ($slug === $this->slug()) {
            return $this;
        }

        // always sanitize the slug
        $slug = Str::slug($slug);

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
                throw new Exception('Invalid status: ' . $status);
        }
    }

    protected function changeStatusToDraft(): self
    {
        $page = $this->commit('changeStatus', 'draft');
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

        $page = $this->commit('changeStatus', 'listed', $num);
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

        $page = $this->commit('changeStatus', 'unlisted');
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

        return $this->commit('changeTemplate', $template, $transfer['data']);
    }

    /**
     * Transfers data from old to new blueprint and tracks changes
     *
     * @param Content $content
     * @param string $old       Old blueprint
     * @param string $new       New blueprint
     * @return array
     */
    public function transferData(Content $content, string $old, string $new): array
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
        $old = $this->hardcopy();

        $this->rules()->$action($this, ...$arguments);
        $this->kirby()->trigger('page.' . $action . ':before', $this, ...$arguments);
        $result = $this->store()->$action(...$arguments);
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
        $this->parentModel()->purge();

        $this->resortSiblingsAfterUnlisting();

        $this->kirby()->trigger('page.delete:after', $result, $this);
        $this->kirby()->cache('pages')->flush();

        return $result;
    }

    /**
     * Clean internal caches
     */
    public function purge(): self
    {
        $this->children  = null;
        $this->blueprint = null;

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
}
