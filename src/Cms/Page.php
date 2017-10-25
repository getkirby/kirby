<?php

namespace Kirby\Cms;

class Page extends Object
{

    use HasChildren;
    use HasContent;
    use HasFiles;
    use HasSiblings;

    protected static $storePrefix = 'page';

    public function __construct(array $props = [])
    {

        parent::__construct($props, [
            'children' => [
                'type'    => Children::class,
                'default' => function (): Children {
                    return $this->store()->commit('page.children', $this);
                }
            ],
            'collection' => [
                'type'    => Pages::class,
                'default' => function (): Pages {
                    return $this->parent() ? $this->parent()->children() : null;
                }
            ],
            'content' => [
                'type'    => Content::class,
                'default' => function (): Content {
                    return $this->store()->commit('page.content', $this);
                }
            ],
            'files' => [
                'type' => Files::class,
                'default' => function (): Files {
                    return $this->store()->commit('page.files', $this);
                }
            ],
            'id' => [
                'required' => true,
                'type'     => 'string',
            ],
            'num' => [
                'type' => 'integer'
            ],
            'root' => [
                'type' => 'string',
            ],
            'template' => [
                'type'    => 'string',
                'default' => function () {
                    return $this->store()->commit('page.template', $this);
                }
            ],
            'url' => [
                'type' => 'string',
            ],
        ]);

    }

    public function changeSlug(string $slug): self
    {
        return $this->store()->commit('page.change.slug', $this, $slug);
    }

    public function changeTemplate(string $template): self
    {
        return $this->store()->commit('page.change.template', $this, $template);
    }

    public function changeStatus(string $status, int $position = null): self
    {
        return $this->store()->commit('page.change.status', $this, $status, $position);
    }

    public function clone(array $props = []): self
    {
        return new static(array_merge([
            'id'     => $this->id(),
            'root'   => $this->root(),
            'url'    => $this->url(),
            'parent' => $this->parent()
        ], $props));
    }

    public static function create($parent, string $slug, string $template, array $data = []): self
    {
        return static::store()->commit('page.create', $parent, $slug, $template, $data);
    }

    public function delete(): bool
    {
        return $this->store()->commit('page.delete', $this);
    }

    public function exists(): bool
    {
        return $this->store()->commit('page.exists', $this);
    }

    public function isActive(): bool
    {
        return $this->site()->page()->is($this);
    }

    public function isErrorPage(): bool
    {
        return $this->site()->errorPage()->is($this);
    }

    public function isHomePage(): bool
    {
        return $this->site()->homePage()->is($this);
    }

    public function isInvisible(): bool
    {
        return $this->isVisible() === false;
    }

    public function isOpen(): bool
    {
        return $this->isActive() || $this->site()->page()->parents()->has($this->id());
    }

    public function isVisible(): bool
    {
        return $this->num() !== null;
    }

    public function parent()
    {
        return $this->prop('parent');
    }

    public function parents(): Pages
    {
        $parents = new Pages;
        $page    = $this->parent();

        while ($page !== null) {
            $parents->append($page->id(), $page);
            $page = $page->parent();
        }

        return $parents;
    }

    public function slug(): string
    {
        return basename($this->id());
    }

    public function sort(int $position): self
    {
        return $this->changeStatus('listed', $position);
    }

    public function update(array $content = []): self
    {
        return $this->store()->commit('page.update', $this, $content);
    }

}
