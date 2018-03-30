<?php

namespace Kirby\Cms;

use Kirby\Util\Translate;

class BlueprintObject extends Component
{

    use Translate;
    use HasModel;

    /**
     * The parent collection
     *
     * @var Collection
     */
    protected $collection;

    public function __construct(array $props = [])
    {
        $props = Blueprint::extend($props);
        $this->setProperties($props);
    }

    /**
     * Returns the default parent collection
     *
     * @return Collection
     */
    protected function collection()
    {
        return $this->collection;
    }

    /**
     * Sets the parent Collection object
     * This is used to handle traversal methods
     * like next, prev, etc.
     *
     * @param Collection|null $collection
     * @return self
     */
    public function setCollection(Collection $collection = null)
    {
        $this->collection = $collection;
        return $this;
    }

    public function toArray(): array
    {
        return $this->propertiesToArray();
    }

}
