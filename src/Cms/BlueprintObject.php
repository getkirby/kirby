<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Util\I18n;

class BlueprintObject extends Component
{

    use I18n;
    use HasModel;

    protected static $mixins = [];

    /**
     * The parent collection
     *
     * @var Collection
     */
    protected $collection;

    public function __construct(array $props = [])
    {
        $props = $this->extend($props);
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

    protected function extend($props)
    {
        if (isset($props['extends']) === false) {
            return $props;
        }

        if ($mixin = static::mixin($props['extends'])) {
            $props = array_replace_recursive($mixin, $props);
        }

        unset($props['extends']);

        return $props;
    }

    public static function mixin(string $path, array $mixin = null): array
    {
        if ($mixin === null) {
            return static::$mixins[$path] ?? null;
        }

        return static::$mixins[$path] = $mixin;
    }

    public static function mixins(array $mixins = null): array
    {
        if ($mixins === null) {
            return static::$mixins;
        }

        return static::$mixins = $mixins;
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
