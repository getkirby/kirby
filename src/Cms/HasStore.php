<?php

namespace Kirby\Cms;

trait HasStore
{

    /**
     * The Store instance
     *
     * @var Store
     */
    protected $store;

    /**
     * Setter for the Store instance
     *
     * @param Store $store
     * @return self
     */
    public function setStore(string $store)
    {
        $this->store = $store;
        return $this;
    }

    /**
     * @return Store
     */
    protected function store()
    {
        if (is_a($this->store, Store::class) === true) {
            return $this->store;
        }

        $className = $this->store;
        return new $className($this);
    }
}
